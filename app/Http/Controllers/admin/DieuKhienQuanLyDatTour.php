<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\DuLieuDatTour;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DieuKhienQuanLyDatTour extends Controller
{

    private $booking;

    public function __construct()
    {
        $this->booking = new DuLieuDatTour();
    }
    public function index()
    {
        $title = 'Quản lý đặt Tour';

        $this->booking->expireOverdueDeposits();
        $list_booking = $this->booking->getBooking();
        $list_booking = $this->updateHideBooking($list_booking);

        // dd($list_booking);

        return view('admin.dat-tour', compact('title', 'list_booking'));
    }

    public function confirmBooking(Request $request)
    {
        $bookingId = $request->bookingId;

        $request->validate(['bookingId' => 'required|integer|min:1']);
        app(\App\Services\QuyTrinhDatTour::class)->transition((int) $bookingId, 'y');
        $result = true;

        if ($result) {
            $list_booking = $this->booking->getBooking();
            $list_booking = $this->updateHideBooking($list_booking);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công.',
                'data' => view('admin.partials.danh-sach-dat-tour', compact('list_booking'))->render()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thất bại.'
            ], 500);
        }
    }

    public function showDetail($bookingId)
    {
        $title = 'Chi tiết đơn đặt';

        $invoice_booking = $this->booking->getInvoiceBooking($bookingId);
        abort_unless($invoice_booking, 404);
        $hide='hide';
        if ($invoice_booking->transactionId == null) {
            $invoice_booking->transactionId = 'Chưa có mã giao dịch đối chiếu';
        }
        if ($invoice_booking->paymentStatus === 'n' && !in_array($invoice_booking->bookingStatus, ['c', 'f'])) {
            $hide = '';
        }
        $passengers = $this->booking->getBookingPassengers($bookingId);
        $receipts = \Illuminate\Support\Facades\DB::table('tbl_payment_receipts')->where('bookingId', $bookingId)->orderByDesc('id')->get();
        $proofs = \Illuminate\Support\Facades\DB::table('tbl_transfer_proofs')->where('bookingId', $bookingId)->orderByDesc('id')->get();
        return view('admin.chi-tiet-dat-tour', compact('title', 'invoice_booking', 'hide', 'passengers', 'receipts', 'proofs'));
    }

    public function downloadPassengerExcel($bookingId)
    {
        $booking = $this->booking->getInvoiceBooking($bookingId);
        $passengers = $this->booking->getBookingPassengers($bookingId);

        if (!$booking || ((int) $booking->numAdults + (int) $booking->numChildren) < 2) {
            abort(404, 'Booking này chưa có đủ thông tin để xuất Excel.');
        }

        $content = view('exports.hanh-khach-dat-tour-excel', compact('booking', 'passengers'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="booking-' . $bookingId . '-passengers.xls"',
        ]);
    }


    public function sendPdf(Request $request)
    {
        $bookingId = $request->input('bookingId');
        $email = $request->input('email');
        $title = 'Hóa đơn';
        $invoice_booking = $this->booking->getInvoiceBooking($bookingId);

        if ($invoice_booking->transactionId == null) {
            $invoice_booking->transactionId = 'Chưa có mã giao dịch đối chiếu';
        }

        try {
            Mail::send('admin.emails.hoa-don', compact('invoice_booking'), function ($message) use ($invoice_booking) {
                $message->to($invoice_booking->email)
                    ->subject('Hóa đơn đặt tour của khách hàng' . $invoice_booking->fullName);
            });

            return response()->json([
                'success' => true,
                'message' => 'Hóa đơn đã được gửi qua email thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi email: ' . $e->getMessage(),
            ], 500);
        }

    }

    public function finishBooking(Request $request)
    {
        $bookingId = $request->bookingId;

        $request->validate(['bookingId' => 'required|integer|min:1']);
        app(\App\Services\QuyTrinhDatTour::class)->transition((int) $bookingId, 'f');
        $result = true;

        if ($result) {
            $list_booking = $this->booking->getBooking();
            $list_booking = $this->updateHideBooking($list_booking);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công.',
                'data' => view('admin.partials.danh-sach-dat-tour', compact('list_booking'))->render()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thất bại.'
            ], 500);
        }
    }

    public function receiviedMoney(Request $request)
    {
        $data = $request->validate([
            'bookingId' => 'required|integer|min:1', 'amount' => 'required|integer|min:1|max:999999999999999',
            'method' => 'required|in:office-payment,bank-transfer', 'reference' => 'nullable|string|max:255',
            'note' => 'required|string|min:3|max:1000', 'receiptToken' => 'required|uuid', 'verified' => 'accepted',
        ]);
        app(\App\Services\QuyTrinhDatTour::class)->recordPayment((int) $data['bookingId'], (int) $data['amount'], $data['method'],
            (string) session('admin'), $data['reference'] ?? '', $data['note'], $data['receiptToken']);
        return redirect()->route('admin.booking-detail', ['id' => $data['bookingId']])->with('success', 'Đã ghi nhận khoản thu.');
    }

    public function reviewCancellation(Request $request)
    {
        $data = $request->validate([
            'bookingId' => 'required|integer|min:1', 'decision' => 'required|in:approve,reject',
            'note' => 'required|string|min:5|max:1000',
        ]);
        app(\App\Services\QuyTrinhDatTour::class)->reviewCancellation((int) $data['bookingId'], $data['decision'] === 'approve', $data['note']);
        return redirect()->route('admin.booking-detail', ['id' => $data['bookingId']])->with('success', 'Đã xử lý yêu cầu hủy.');
    }

    public function resolveRefund(Request $request)
    {
        $data = $request->validate(['bookingId' => 'required|integer|min:1', 'note' => 'required|string|min:5|max:1000']);
        app(\App\Services\QuyTrinhDatTour::class)->resolveRefund((int) $data['bookingId'], $data['note']);
        return redirect()->route('admin.booking-detail', ['id' => $data['bookingId']])->with('success', 'Đã lưu kết quả đối soát / hoàn tiền.');
    }
    private function updateHideBooking($list_booking)
    {
        // Lấy ngày hiện tại
        $currentDate = date('Y-m-d');

        foreach ($list_booking as $booking) {
            // So sánh endDate của booking với ngày hiện tại
            if ($booking->endDate < $currentDate) {
                $hide = '';
            } else {
                $hide = 'hide';
            }

            // Gán giá trị $hide vào mỗi booking
            $booking->hide = $hide;
        }

        return $list_booking;
    }

}
