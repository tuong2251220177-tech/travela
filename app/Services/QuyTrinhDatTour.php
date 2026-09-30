<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuyTrinhDatTour
{
    private function reject(string $message): void
    {
        throw ValidationException::withMessages(['booking' => $message]);
    }

    public function create(int $userId, array $data, array $passengers): object
    {
        return $this->persist($userId, $data, $passengers);
    }

    public function createPrivate(int $userId, array $data, array $passengers, int $total, int $requestId): object
    {
        return $this->persist($userId, $data, $passengers, $total, $requestId);
    }

    private function persist(int $userId, array $data, array $passengers, ?int $quotedTotal = null, ?int $requestId = null): object
    {
        return DB::transaction(function () use ($userId, $data, $passengers, $quotedTotal, $requestId) {
            // Serialize reservations for the same tour; never trust browser prices or payment flags.
            $tour = DB::table('tbl_tours')->where('tourId', $data['tourId'])->lockForUpdate()->first();
            $key = hash('sha256', $userId . ':' . $data['bookingToken']);
            $existing = DB::table('tbl_booking')->where('requestKey', $key)->first();
            if ($existing) {
                $existing->wasRecentlyCreated = false;
                return $existing;
            }
            if (!$tour || !$tour->availability || !$tour->startDate || Carbon::parse($tour->startDate)->lte(now())) {
                $this->reject('Tour đã khởi hành hoặc không còn nhận đặt chỗ.');
            }
            if (($tour->privateRequestId ?? null) !== null) {
                $quote = DB::table('tbl_private_tour_requests')->where('id', $requestId)->where('userId', $userId)->first();
                if (!$quote || (int) $tour->privateRequestId !== $requestId || $quote->status !== 'quoted'
                    || (int) $quote->quoteTotal !== $quotedTotal || Carbon::parse($quote->expiresAt)->lte(now())
                    || (int) $quote->numAdults !== (int) $data['numAdults'] || (int) $quote->numChildren !== (int) $data['numChildren']) {
                    $this->reject('Lịch trình riêng chỉ dành cho khách đã nhận báo giá.');
                }
            } elseif ($requestId !== null || $quotedTotal !== null) {
                $this->reject('Không thể áp dụng báo giá riêng cho tour ghép.');
            }
            $count = (int) $data['numAdults'] + (int) $data['numChildren'];
            if ($count < 1 || count($passengers) !== $count) {
                $this->reject('Số hành khách không hợp lệ. Vui lòng kiểm tra danh sách.');
            }
            $transport = $requestId !== null
                ? ['transportOptionId' => null, 'transportName' => 'Theo báo giá tour riêng', 'transportFee' => 0]
                : app(PhuongTienTour::class)->price((int) $tour->tourId, (int) ($data['transportOptionId'] ?? 0), $count, true);
            $groupNumber = app(NhomTour::class)->allocate($tour, $count, $transport['transportOptionId']);
            $subtotal = (int) $data['numAdults'] * (float) $tour->priceAdult + (int) $data['numChildren'] * (float) $tour->priceChild;
            $coupon = $requestId !== null ? '' : strtoupper(trim($data['couponCode'] ?? ''));
            $discount = app(DichVuMaGiamGia::class)->discount($coupon, (int) round($subtotal), true, $userId);
            $total = $quotedTotal ?? round($subtotal - $discount + $transport['transportFee']);
            if ($total <= 0) $this->reject('Giá tour chưa hợp lệ. Vui lòng liên hệ nhân viên.');
            $primary = $passengers[0];
            $paymentPlan = $data['paymentPlan'] ?? 'deposit';
            if (!in_array($paymentPlan, ['deposit', 'full'], true)) $this->reject('Lựa chọn thanh toán không hợp lệ.');
            $id = DB::table('tbl_booking')->insertGetId($transport + [
                'requestKey' => $key, 'tourId' => $tour->tourId, 'userId' => $userId, 'groupNumber' => $groupNumber,
                'fullName' => $primary['fullName'], 'email' => $primary['email'],
                'phoneNumber' => $primary['tel'], 'address' => $primary['address'],
                'numAdults' => $data['numAdults'], 'numChildren' => $data['numChildren'],
                'totalPrice' => $total, 'depositAmount' => round($total * 0.2),
                'paymentPlan' => $paymentPlan,
                'couponCode' => $coupon ?: null, 'discountAmount' => $discount,
                'depositDueAt' => min(now()->addHours(48), Carbon::parse($tour->startDate)),
                'depositStatus' => 'pending', 'bookingDate' => now(), 'bookingStatus' => 'p',
            ]);
            foreach ($passengers as $index => $passenger) {
                DB::table('tbl_booking_passengers')->insert([
                    'bookingId' => $id, 'passengerNumber' => $index + 1,
                    'passengerType' => $index < $data['numAdults'] ? 'adult' : 'child',
                    'fullName' => $passenger['fullName'], 'email' => $passenger['email'],
                    'phoneNumber' => $passenger['tel'], 'address' => $passenger['address'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('tbl_checkout')->insert([
                'bookingId' => $id, 'paymentMethod' => $data['payment'], 'amount' => $total,
                'paymentStatus' => 'n', 'transactionId' => null, 'paymentDate' => null,
            ]);
            $booking = DB::table('tbl_booking')->where('bookingId', $id)->first();
            $booking->wasRecentlyCreated = true;
            return $booking;
        }, 3);
    }

    private function lockedBooking(int $id): object
    {
        $booking = DB::table('tbl_booking')->where('bookingId', $id)->lockForUpdate()->first();
        abort_unless($booking, 404);
        return $booking;
    }

    public function reportTransfer(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId) {
            $booking = $this->lockedBooking($id);
            abort_unless((int) $booking->userId === $userId, 404);
            $checkout = DB::table('tbl_checkout')->where('bookingId', $id)->first();
            if (!$checkout || $checkout->paymentMethod !== 'bank-transfer' || in_array($booking->bookingStatus, ['c', 'f'])) $this->reject('Đơn này không thể báo chuyển khoản.');
            if ($checkout->paymentStatus === 'y' || $booking->transferReportedAt) return;
            DB::table('tbl_booking')->where('bookingId', $id)->update(['transferReportedAt' => now(), 'bookingStatus' => $booking->bookingStatus === 'y' ? 'y' : 'b']);
        }, 3);
    }

    public function requestCancellation(int $id, int $userId, string $reason): void
    {
        DB::transaction(function () use ($id, $userId, $reason) {
            $booking = $this->lockedBooking($id);
            abort_unless((int) $booking->userId === $userId, 404);
            if ($booking->cancelStatus === 'pending') return;
            if (in_array($booking->bookingStatus, ['c', 'f'])) $this->reject('Đơn đã kết thúc hoặc đã hủy.');
            $start = DB::table('tbl_tours')->where('tourId', $booking->tourId)->value('startDate');
            if (!$start || Carbon::parse($start)->lt(now()->addDays(7))) $this->reject('Yêu cầu hủy trực tuyến cần gửi trước ngày đi ít nhất 7 ngày. Vui lòng liên hệ nhân viên.');
            DB::table('tbl_booking')->where('bookingId', $id)->update([
                'cancelStatus' => 'pending', 'cancelReason' => $reason, 'cancelRequestedAt' => now(),
                'cancelNote' => null, 'cancelReviewedAt' => null,
            ]);
        }, 3);
    }

    public function reviewCancellation(int $id, bool $approve, string $note): void
    {
        DB::transaction(function () use ($id, $approve, $note) {
            $booking = $this->lockedBooking($id);
            if ($booking->cancelStatus !== 'pending') $this->reject('Yêu cầu đã được xử lý hoặc không tồn tại.');
            if (in_array($booking->bookingStatus, ['c', 'f'])) $this->reject('Đơn đã kết thúc hoặc đã hủy.');
            $changes = ['cancelStatus' => $approve ? 'approved' : 'rejected', 'cancelNote' => $note, 'cancelReviewedAt' => now()];
            if ($approve) {
                $changes['bookingStatus'] = 'c';
                $paid = DB::table('tbl_checkout')->where('bookingId', $id)->value('paymentStatus') === 'y';
                $changes['refundStatus'] = ($paid || ($booking->amountPaid ?? 0) > 0 || $booking->transferReportedAt) ? 'pending' : null;
            }
            DB::table('tbl_booking')->where('bookingId', $id)->update($changes);
        }, 3);
    }

    public function receiveMoney(int $id): void
    {
        DB::transaction(function () use ($id) {
            $booking = $this->lockedBooking($id);
            if (in_array($booking->bookingStatus, ['c', 'f'])) $this->reject('Không thể xác nhận tiền cho đơn đã hủy hoặc hoàn thành.');
            $checkout = DB::table('tbl_checkout')->where('bookingId', $id)->first();
            abort_unless($checkout, 404);
            if ($checkout->paymentStatus === 'y') return;
            $this->recordPayment($id, (int) $booking->totalPrice - (int) $booking->amountPaid,
                $checkout->paymentMethod, (string) session('admin', 'system'), '', 'Ghi nhận toàn bộ số tiền còn lại', (string) \Illuminate\Support\Str::uuid());
        }, 3);
    }

    public function recordPayment(int $id, int $amount, string $method, string $staff, string $reference, string $note, string $token): void
    {
        DB::transaction(function () use ($id, $amount, $method, $staff, $reference, $note, $token) {
            $booking = $this->lockedBooking($id);
            $existing = DB::table('tbl_payment_receipts')->where('requestToken', $token)->first();
            if ($existing) {
                if ((int) $existing->bookingId !== $id || (int) $existing->amount !== $amount) $this->reject('Phiếu thu đã được sử dụng. Vui lòng tải lại trang.');
                return;
            }
            if (in_array($booking->bookingStatus, ['c', 'f']) || $booking->cancelStatus === 'pending') $this->reject('Đơn đã kết thúc hoặc đang chờ xử lý hủy, không thể thu thêm tiền.');
            $paid = (int) $booking->amountPaid;
            if ($amount <= 0 || $amount > (int) $booking->totalPrice - $paid) $this->reject('Số tiền phải lớn hơn 0 và không vượt quá số tiền còn thiếu.');
            if (!in_array($method, ['office-payment', 'bank-transfer'], true)) $this->reject('Phương thức thu tiền không hợp lệ.');
            DB::table('tbl_payment_receipts')->insert([
                'bookingId' => $id, 'requestToken' => $token, 'amount' => $amount, 'method' => $method,
                'staff' => $staff, 'reference' => $reference ?: null, 'note' => $note, 'created_at' => now(),
            ]);
            $paid += $amount;
            $depositPaid = $paid >= (int) $booking->depositAmount;
            DB::table('tbl_booking')->where('bookingId', $id)->update([
                'amountPaid' => $paid, 'depositStatus' => $depositPaid ? 'paid' : 'pending',
                'transferReportedAt' => null,
                'depositPaidAt' => $depositPaid ? ($booking->depositPaidAt ?: now()) : null,
                'bookingStatus' => $booking->bookingStatus === 'y' ? 'y' : 'b',
            ]);
            DB::table('tbl_checkout')->where('bookingId', $id)->update([
                'paymentStatus' => $paid >= (int) $booking->totalPrice ? 'y' : 'n',
                'paymentDate' => $paid >= (int) $booking->totalPrice ? now() : null,
            ]);
        }, 3);
    }

    public function transition(int $id, string $target): void
    {
        if (!in_array($target, ['y', 'f'], true)) $this->reject('Trạng thái không hợp lệ.');
        DB::transaction(function () use ($id, $target) {
            $booking = $this->lockedBooking($id);
            if ($booking->bookingStatus === $target) return;
            if ($booking->cancelStatus === 'pending') $this->reject('Vui lòng xử lý yêu cầu hủy trước.');
            $fullyPaid = DB::table('tbl_checkout')->where('bookingId', $id)->value('paymentStatus') === 'y';
            if (!$fullyPaid && ($target === 'f' || $booking->depositStatus !== 'paid')) $this->reject('Xác nhận đơn cần nhận đủ cọc; hoàn thành tour cần nhận đủ tiền.');
            if ($target === 'y' && !in_array($booking->bookingStatus, ['p', 'b'])) $this->reject('Đơn không còn ở trạng thái chờ xác nhận.');
            if ($target === 'f') {
                $end = DB::table('tbl_tours')->where('tourId', $booking->tourId)->value('endDate');
                if ($booking->bookingStatus !== 'y' || !$end || Carbon::parse($end)->endOfDay()->gte(now())) $this->reject('Chỉ hoàn thành đơn đã xác nhận sau ngày kết thúc tour.');
            }
            DB::table('tbl_booking')->where('bookingId', $id)->update(['bookingStatus' => $target]);
        }, 3);
    }

    public function resolveRefund(int $id, string $note): void
    {
        DB::transaction(function () use ($id, $note) {
            $booking = $this->lockedBooking($id);
            if ($booking->bookingStatus !== 'c' || $booking->refundStatus !== 'pending') $this->reject('Đơn không có khoản chờ xử lý hoàn tiền.');
            DB::table('tbl_booking')->where('bookingId', $id)->update([
                'refundStatus' => 'resolved',
                'cancelNote' => trim($booking->cancelNote . "\nĐối soát " . now()->format('d/m/Y H:i') . ': ' . $note),
            ]);
        }, 3);
    }

    public function expire(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $booking = $this->lockedBooking($id);
            if (!in_array($booking->bookingStatus, ['p', 'b', 'y']) || $booking->depositStatus !== 'pending'
                || !$booking->depositDueAt || Carbon::parse($booking->depositDueAt)->gte(now())
                || ($booking->amountPaid ?? 0) > 0 || $booking->transferReportedAt || DB::table('tbl_checkout')->where('bookingId', $id)->value('paymentStatus') === 'y') return false;
            $changes = ['bookingStatus' => 'c', 'depositStatus' => 'expired'];
            if ($booking->cancelStatus === 'pending') {
                $changes += ['cancelStatus' => 'approved', 'cancelNote' => 'Hệ thống đã hủy đơn do quá hạn thanh toán.', 'cancelReviewedAt' => now()];
            }
            DB::table('tbl_booking')->where('bookingId', $id)->update($changes);
            return true;
        }, 3);
    }
}
