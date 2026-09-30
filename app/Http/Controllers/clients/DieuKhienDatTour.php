<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use App\Models\clients\DuLieuDatTour as Booking;
use App\Models\clients\DuLieuThanhToan as Checkout;
use App\Models\clients\DuLieuTour as Tours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class DieuKhienDatTour extends Controller
{
    private const DEPOSIT_RATE = 0.20;
    private const DEPOSIT_HOURS = 48;
    private $tour;
    private $booking;
    private $checkout;

    public function __construct()
    {
        parent::__construct(); // Gọi constructor của Controller để khởi tạo $user
        $this->tour = new Tours();
        $this->booking = new Booking();
        $this->checkout = new Checkout();
    }

    public function index($id)
    {

        $title = 'Đặt Tour';
        $tour = $this->tour->getTourDetail($id);
        abort_unless($tour, 404);
        if (!$tour->availability || !$tour->startDate || \Carbon\Carbon::parse($tour->startDate)->lte(now())) {
            toastr()->error('Tour đã khởi hành hoặc không còn nhận đặt chỗ.');
            return redirect()->route('tour-detail', ['id' => $id]);
        }
        $transIdMomo = null; // Initialize the variable
        // This page starts with no selected coupon, including after a reload.
        session()->forget('booking_coupon_code');
        // Bind used codes as values: imported tables can use different collations.
        $usedCouponCodes = DB::table('tbl_booking')->where('userId', $this->getUserId())
            ->whereNotNull('couponCode')->distinct()->pluck('couponCode')->all();
        $suggestedCoupons = DB::table('tbl_coupons')->where('active', true)->where('isPublic', true)
            ->whereNotIn('code', $usedCouponCodes)
            ->whereColumn('usedCount', '<', 'usageLimit')
            ->where(fn ($query) => $query->whereNull('expiresAt')->orWhere('expiresAt', '>', now()))
            ->orderBy('minTotal')->orderByDesc('id')->limit(20)->get();
        $transportOptions = DB::table('tbl_tour_transport_options')->where('tourId', $id)->where('active', true)->orderBy('surcharge')->get();
        return view('clients.dat-tour', compact('title', 'tour', 'transIdMomo', 'suggestedCoupons', 'transportOptions'));
    }

    public function downloadPassengerTemplate(Request $request)
    {
        $request->validate(['adults' => 'required|integer|min:1|max:100', 'children' => 'required|integer|min:0|max:100']);
        abort_if((int) $request->adults + (int) $request->children < 3, 422);
        return response(app(\App\Services\BangTinhHanhKhach::class)->template((int) $request->adults, (int) $request->children), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="travela-passengers.xlsx"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function previewPassengerFile(Request $request)
    {
        $request->validate([
            'adults' => 'required|integer|min:1|max:100', 'children' => 'required|integer|min:0|max:100',
            'passenger_file' => 'required|file|max:2048',
        ], \App\Services\BangTinhHanhKhach::FILE_MESSAGES);
        $passengers = app(\App\Services\BangTinhHanhKhach::class)->read($request->file('passenger_file'), (int) $request->adults, (int) $request->children);
        return response()->json(['passengers' => $passengers])->header('Cache-Control', 'no-store');
    }

    protected function validateTourAvailability(int $tourId, int $requestedCount): ?string
    {
        $tour = $this->tour->getTourDetail($tourId);

        if (!$tour) {
            return 'Tour không tồn tại.';
        }

        $availableSeats = (int) $tour->maxGuests;
        if ($requestedCount > $availableSeats) {
            return 'Mỗi đoàn tối đa ' . $availableSeats . ' khách. Vui lòng chia thành nhiều đơn hoặc liên hệ nhân viên.';
        }

        return null;
    }

    protected function ensureSingleUseCouponSession(string $code): void
    {
        $stored = strtoupper(trim((string) session('booking_coupon_code', '')));
        if ($stored === '') {
            return;
        }
        if ($stored === strtoupper(trim($code))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'couponCode' => 'Mã giảm giá này đã được áp dụng. Hủy mã trước khi chọn lại.',
            ]);
        }
        throw \Illuminate\Validation\ValidationException::withMessages([
            'couponCode' => 'Mã giảm giá đã được kích hoạt. Hủy mã trước khi sử dụng mã khác.',
        ]);
    }

    public function createBooking(Request $req)
    {
        abort_unless($this->getUserId(), 401);
        $req->merge(['payment' => $req->input('payment_hidden') ?: $req->input('payment')]);
        $req->validate([
            'tourId' => 'required|integer|min:1',
            'numAdults' => 'required|integer|min:1|max:100',
            'numChildren' => 'required|integer|min:0|max:100',
            'bookingToken' => 'required|uuid',
            'couponCode' => 'nullable|string|max:40',
            'transportOptionId' => 'nullable|integer|min:0',
            'paymentPlan' => 'nullable|in:deposit,full',
            'payment' => 'required|in:bank-transfer,office-payment',
            'agree' => 'accepted',
            'passengers' => 'nullable|array|max:199',
            'passenger_import_json' => 'nullable|string|max:200000',
            'passenger_file' => 'nullable|file|max:2048',
        ], \App\Services\BangTinhHanhKhach::FILE_MESSAGES);
        // dd($req);
        $address = $req->input('address');
        $email = $req->input('email');
        $fullName = $req->input('fullName');
        $numAdults = $req->input('numAdults');
        $numChildren = $req->input('numChildren');
        $paymentMethod = $req->input('payment_hidden') ?: $req->input('payment');
        $tel = $req->input('tel');
        $totalPrice = $req->input('totalPrice');
        $tourId = $req->input('tourId');
        $userId = $this->getUserId();
        $passengerCount = (int) $numAdults + (int) $numChildren;

        $passengers = [[
            'fullName' => trim((string) $fullName),
            'email' => trim((string) $email),
            'tel' => trim((string) $tel),
            'address' => trim((string) $address),
        ]];

        foreach ((array) $req->input('passengers', []) as $passenger) {
            $passengers[] = [
                'fullName' => trim((string) ($passenger['fullName'] ?? '')),
                'email' => trim((string) ($passenger['email'] ?? '')),
                'tel' => trim((string) ($passenger['tel'] ?? '')),
                'address' => trim((string) ($passenger['address'] ?? '')),
            ];
        }

        if ($passengerCount > 2) {
            $req->validate(['passenger_file' => 'required|file|max:2048'], \App\Services\BangTinhHanhKhach::FILE_MESSAGES);
            $passengers = app(\App\Services\BangTinhHanhKhach::class)->read($req->file('passenger_file'), (int) $numAdults, (int) $numChildren);
        }

        \Illuminate\Support\Facades\Validator::make(['passengers' => $passengers], [
            'passengers' => 'required|array|max:200',
            'passengers.*' => 'required|array',
            'passengers.*.fullName' => 'required|string|max:255',
            'passengers.*.email' => 'required|email|max:255',
            'passengers.*.tel' => ['required', 'regex:/^[0-9+ ()-]{9,20}$/'],
            'passengers.*.address' => 'required|string|max:255',
        ])->validate();
        if (!empty($passengers[0])) {
            $fullName = $passengers[0]['fullName'];
            $email = $passengers[0]['email'];
            $tel = $passengers[0]['tel'];
            $address = $passengers[0]['address'];
        }

        if ($passengerCount < 1 || count($passengers) !== $passengerCount || collect($passengers)->contains(fn ($passenger) => in_array('', $passenger, true))) {
            toastr()->error('Vui lòng nhập đủ thông tin của tất cả hành khách.');
            return redirect()->back()->withInput();
        }

        if (empty($paymentMethod)) {
            toastr()->error('Vui lòng chọn phương thức thanh toán.');
            return redirect()->back()->withInput();
        }

        $booking = app(\App\Services\QuyTrinhDatTour::class)->create((int) $userId, [
            'tourId' => (int) $tourId, 'numAdults' => (int) $numAdults, 'numChildren' => (int) $numChildren,
            'bookingToken' => $req->input('bookingToken'), 'couponCode' => $req->input('couponCode'),
            'transportOptionId' => $req->integer('transportOptionId'),
            'paymentPlan' => $req->input('paymentPlan', 'deposit'),
            'payment' => $paymentMethod,
        ], $passengers);
        session()->forget('booking_coupon_code');
        if ($booking->wasRecentlyCreated && $passengerCount > 2) {
            $this->sendPassengerExcelToAdmin($booking->bookingId);
        }
        toastr()->success('Đặt tour thành công! Vui lòng xem hướng dẫn thanh toán trong chi tiết đơn.');
        return redirect()->route('tour-booked', ['bookingId' => $booking->bookingId]);
    }

    public function clearCoupon(Request $request)
    {
        $request->validate(['code' => 'nullable|string|max:40']);
        $code = strtoupper(trim((string) $request->input('code', '')));
        $stored = strtoupper(trim((string) session('booking_coupon_code', '')));
        if ($code !== '' && $stored !== '' && $stored !== $code) {
            throw \Illuminate\Validation\ValidationException::withMessages(['couponCode' => 'Mã hiện tại đang được áp dụng.']);
        }
        session()->forget('booking_coupon_code');
        return response()->json(['success' => true]);
    }

    public function previewCoupon(Request $request)
    {
        $data = $request->validate([
            'tourId' => 'required|integer|min:1', 'adults' => 'required|integer|min:1|max:100',
            'children' => 'required|integer|min:0|max:100', 'code' => 'required|string|max:40',
            'transportOptionId' => 'nullable|integer|min:0',
        ]);
        $tour = DB::table('tbl_tours')->where('tourId', $data['tourId'])->first();
        abort_unless($tour, 404);
        $subtotal = (int) round($tour->priceAdult * $data['adults'] + $tour->priceChild * $data['children']);
        $code = strtoupper(trim($data['code']));
        $transport = app(\App\Services\PhuongTienTour::class)->price((int) $tour->tourId, (int) ($data['transportOptionId'] ?? 0), $data['adults'] + $data['children']);
        $this->ensureSingleUseCouponSession($code);
        $discount = app(\App\Services\DichVuMaGiamGia::class)->discount($code, $subtotal, false, (int) $this->getUserId());
        session()->put('booking_coupon_code', $code);
        return response()->json(['code' => $code, 'discount' => $discount, 'total' => $subtotal - $discount + $transport['transportFee']]);
    }
    private function sendPassengerExcelToAdmin($bookingId)
    {
        $adminEmail = DB::table('tbl_admin')
            ->where('username', 'admin')
            ->value('email') ?: config('mail.from.address');

        if (!$adminEmail || !Schema::hasTable('tbl_booking_passengers')) {
            Log::warning('Không thể gửi file hành khách: chưa có email admin hoặc bảng hành khách.', [
                'bookingId' => $bookingId,
            ]);
            return;
        }

        $booking = DB::table('tbl_booking as b')
            ->join('tbl_tours as t', 't.tourId', '=', 'b.tourId')
            ->where('b.bookingId', $bookingId)
            ->select('b.*', 't.title')
            ->first();
        $passengers = DB::table('tbl_booking_passengers')
            ->where('bookingId', $bookingId)
            ->orderBy('passengerNumber')
            ->get();

        if (!$booking || $passengers->count() < 3) {
            return;
        }

        try {
            $attachment = view('exports.hanh-khach-dat-tour-excel', compact('booking', 'passengers'))->render();
            Mail::raw('Booking mới có từ 3 hành khách. File Excel thông tin hành khách được đính kèm.', function ($message) use ($adminEmail, $booking, $attachment) {
                $message->to($adminEmail)
                    ->subject('Booking mới #' . $booking->bookingId . ' - ' . $booking->title)
                    ->attachData($attachment, 'booking-' . $booking->bookingId . '-passengers.xls', [
                        'mime' => 'application/vnd.ms-excel',
                    ]);
            });
        } catch (\Throwable $exception) {
            Log::error('Gửi file hành khách cho admin thất bại.', [
                'bookingId' => $bookingId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function createMomoPayment(Request $request)
    {
        $validated = $request->validate([
            'tourId' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:1000'],
        ]);

        return response()->json(['error' => 'MoMo hiện chưa hỗ trợ xác nhận giao dịch tự động. Vui lòng chọn chuyển khoản QR hoặc thanh toán tại văn phòng.'], 422);

    }

    public function handlePaymentMomoCallback(Request $request)
    {
        // Browser callbacks alone cannot prove that money was received.
        return redirect()->route('my-tours')->with('error', 'Chưa thể tự xác nhận thanh toán MoMo. Vui lòng liên hệ nhân viên để đối chiếu giao dịch.');
    }
}
