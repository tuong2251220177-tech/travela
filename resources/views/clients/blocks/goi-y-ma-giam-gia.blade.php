<section aria-label="Mã giảm giá gợi ý" style="margin:16px 0;padding:14px;border:1px solid #e4d9fb;border-radius:14px;background:#faf8ff">
    <h3 style="font-size:14px;margin:0 0 8px">Mã giảm giá dành cho bạn</h3>
    <p style="font-size:11px">Mỗi tài khoản chỉ dùng mỗi mã 1 lần khi tạo đơn. Hủy mã hiện tại trước khi đổi mã hoặc số khách.</p>
    <div style="max-height:260px;overflow-y:auto">
    @forelse (($suggestedCoupons ?? collect()) as $coupon)
        <div class="coupon-suggestion" data-min-total="{{ $coupon->minTotal }}" style="padding:12px 0;border-top:1px dashed #ddd1f5">
            <strong style="color:#7044ed;overflow-wrap:anywhere">{{ $coupon->code }}</strong>
            <div style="font-size:12px">Giảm {{ number_format($coupon->value,0,',','.') }}{{ $coupon->type === 'percent' ? '%' : ' đ' }}@if($coupon->maxDiscount), tối đa {{ number_format($coupon->maxDiscount,0,',','.') }} đ@endif</div>
            <small style="display:block">Đơn từ {{ number_format($coupon->minTotal,0,',','.') }} đ · {{ $coupon->expiresAt ? 'Hết hạn '.\Carbon\Carbon::parse($coupon->expiresAt)->format('d/m/Y') : 'Chưa có hạn kết thúc' }}</small>
            <small class="coupon-eligibility" style="display:block" aria-live="polite"></small>
            <small style="display:block">Mỗi tài khoản chỉ dùng 1 lần.</small>
            <button type="button" class="booking-btn choose-coupon" data-code="{{ $coupon->code }}" style="margin-top:8px;padding:8px 12px;font-size:11px">Dùng mã này</button>
        </div>
    @empty
        <small>Hiện chưa có mã gợi ý. Bạn vẫn có thể nhập mã được cung cấp ở trên.</small>
    @endforelse
    </div>
</section>
