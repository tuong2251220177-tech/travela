<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DichVuMaGiamGia
{
    public function discount(string $code, int $subtotal, bool $consume = false, ?int $userId = null): int
    {
        if ($code === '') return 0;
        $query = DB::table('tbl_coupons')->where('code', strtoupper(trim($code)));
        if ($consume) $query->lockForUpdate();
        $coupon = $query->first();
        if (!$coupon || !$coupon->active || ($coupon->expiresAt && Carbon::parse($coupon->expiresAt)->lte(now())) || $coupon->usedCount >= $coupon->usageLimit) {
            throw ValidationException::withMessages(['couponCode' => 'Mã không hợp lệ, đã tắt, hết hạn hoặc hết lượt sử dụng.']);
        }
        if ($subtotal < $coupon->minTotal) throw ValidationException::withMessages(['couponCode' => 'Mã yêu cầu đơn từ ' . number_format($coupon->minTotal, 0, ',', '.') . ' đồng.']);
        if (!$userId) throw ValidationException::withMessages(['couponCode' => 'Vui lòng đăng nhập để sử dụng mã.']);
        $uses = DB::table('tbl_booking')->where('userId', $userId)->where('couponCode', $coupon->code);
        if ($consume) $uses->lockForUpdate();
        if ($uses->get(['bookingId'])->count() >= 1) {
            throw ValidationException::withMessages(['couponCode' => 'Bạn đã sử dụng mã này. Mỗi tài khoản chỉ được dùng mỗi mã 1 lần.']);
        }
        $discount = $coupon->type === 'percent' ? (int) round($subtotal * $coupon->value / 100) : (int) $coupon->value;
        if ($coupon->maxDiscount !== null) $discount = min($discount, (int) $coupon->maxDiscount);
        $discount = max(0, min($discount, $subtotal - 1));
        // Called inside the booking transaction; failed bookings roll this back.
        if ($consume) DB::table('tbl_coupons')->where('id', $coupon->id)->increment('usedCount');
        return $discount;
    }
}
