<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use App\Services\QuyTrinhDatTour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DieuKhienTourDaDat extends Controller
{
    public function index(Request $req)
    {
        $req->validate(['bookingId' => 'required|integer|min:1']);
        $bookingId = (int) $req->input('bookingId');
        $title = 'Chi tiết đơn đặt tour';
        $tour_booked = DB::table('tbl_booking as b')
            ->join('tbl_tours as t', 't.tourId', '=', 'b.tourId')
            ->join('tbl_checkout as c', 'c.bookingId', '=', 'b.bookingId')
            ->where('b.bookingId', $bookingId)->where('b.userId', $this->getUserId())
            ->select('t.*', 'c.*', 'b.*')->first();
        abort_unless($tour_booked, 404);
        $hide = !in_array($tour_booked->bookingStatus, ['c', 'f'])
            && $tour_booked->cancelStatus !== 'pending'
            && $tour_booked->startDate && Carbon::parse($tour_booked->startDate)->gte(now()->addDays(7)) ? '' : 'hide';
        $proofs = DB::table('tbl_transfer_proofs')->where('bookingId', $bookingId)->orderByDesc('id')->get();
        $passengers = DB::table('tbl_booking_passengers')->where('bookingId', $bookingId)->orderBy('passengerNumber')->get();
        $groupSummary = app(\App\Services\NhomTour::class)->summary($tour_booked->tourId, $tour_booked->groupNumber);
        return view('clients.tour-da-dat', compact('groupSummary', 'title', 'tour_booked', 'hide', 'bookingId', 'proofs', 'passengers'));
    }

    public function cancelBooking(Request $req, QuyTrinhDatTour $workflow)
    {
        $data = $req->validate(['bookingId' => 'required|integer|min:1', 'reason' => 'required|string|min:5|max:1000']);
        $workflow->requestCancellation((int) $data['bookingId'], (int) $this->getUserId(), $data['reason']);
        return redirect()->route('tour-booked', ['bookingId' => $data['bookingId']])->with('success', 'Đã gửi yêu cầu hủy. Nhân viên sẽ kiểm tra và phản hồi tại đây.');
    }

    public function reportTransfer(Request $req, QuyTrinhDatTour $workflow)
    {
        $data = $req->validate(['bookingId' => 'required|integer|min:1', 'proof' => 'required|image|mimes:jpg,jpeg,png,webp|dimensions:min_width=1,min_height=1|max:5120'],
            ['proof.required' => 'Vui lòng chọn ảnh chuyển khoản.', 'proof.max' => 'Ảnh tối đa 5 MB.']);
        $path = null;
        try {
            DB::transaction(function () use ($req, $data, $workflow, &$path) {
                $booking = DB::table('tbl_booking')->where('bookingId', $data['bookingId'])->lockForUpdate()->first();
                abort_unless($booking && (int) $booking->userId === (int) $this->getUserId(), 404);
                $checkout = DB::table('tbl_checkout')->where('bookingId', $booking->bookingId)->first();
                abort_unless($checkout && $checkout->paymentMethod === 'bank-transfer' && $checkout->paymentStatus !== 'y' && !in_array($booking->bookingStatus, ['c', 'f']), 422, 'Đơn không còn nhận ảnh chuyển khoản.');
                $path = $req->file('proof')->store('transfer-proofs', 'local');
                if (!$path) throw new \RuntimeException('Không lưu được ảnh chuyển khoản.');
                DB::table('tbl_transfer_proofs')->insert(['bookingId' => $booking->bookingId, 'path' => $path, 'created_at' => now()]);
                $workflow->reportTransfer((int) $booking->bookingId, (int) $this->getUserId());
            });
        } catch (\Throwable $e) {
            if ($path) \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
            throw $e;
        }
        return redirect()->route('tour-booked', ['bookingId' => $data['bookingId']])->with('success', 'Đã báo chuyển khoản. Đơn chỉ được ghi nhận đã thanh toán sau khi nhân viên đối chiếu.');
    }
    public function transferProof(int $id)
    {
        $proof = DB::table('tbl_transfer_proofs as p')->join('tbl_booking as b', 'b.bookingId', '=', 'p.bookingId')->where('p.id', $id)->select('p.path', 'b.userId')->first();
        abort_unless($proof, 404);
        abort_unless(session()->has('admin') || (session()->has('username') && (int) $proof->userId === (int) $this->getUserId()), 403);
        return \Illuminate\Support\Facades\Storage::disk('local')->response($proof->path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
