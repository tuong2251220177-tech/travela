@include('clients.blocks.dau-trang-giao-dien', ['pageClass' => 'private-tour-page'])
<main id="main" class="shell private-tour">
    <a href="{{ route('private-tours.index') }}">← Yêu cầu tour riêng</a><h1>Hành trình của nhóm bạn</h1>
    @include('partials.thong-tin-tour-rieng')
    @if($item->status === 'accepted')
        <section class="private-card"><h2>Đơn đặt đã được tạo</h2><p>Theo dõi đặt cọc, thanh toán và yêu cầu hủy trong chi tiết đơn.</p><a class="button" href="{{ route('tour-booked', ['bookingId' => $item->bookingId]) }}">Mở đơn #{{ $item->bookingId }} →</a></section>
    @elseif($item->status === 'quoted' && \Carbon\Carbon::parse($item->expiresAt)->isFuture())
        <section class="private-card"><h2>Chấp nhận báo giá</h2><p>Nhập tên từng hành khách. Thông tin liên hệ dùng chung của người đại diện nhóm.</p>
        <form method="post" action="{{ route('private-tours.accept', $item->id) }}" class="private-form">
            @csrf <input type="hidden" name="revision" value="{{ $item->revision }}">
            <div class="private-fields">@for($i = 0; $i < $item->numAdults + $item->numChildren; $i++)<label>{{ $i < $item->numAdults ? 'Người lớn' : 'Trẻ em' }} · Khách {{ $i + 1 }}<input name="passengers[{{ $i }}][fullName]" maxlength="150" value="{{ old('passengers.'.$i.'.fullName', $i === 0 ? $item->fullName : '') }}" required></label>@endfor</div>
            <label>Phương thức thanh toán<select name="payment"><option value="bank-transfer">Chuyển khoản QR</option><option value="office-payment" @selected(old('payment') === 'office-payment')>Tại văn phòng</option></select></label>
            <label>Số tiền muốn thanh toán<select name="paymentPlan"><option value="deposit">Đặt cọc 20%</option><option value="full" @selected(old('paymentPlan') === 'full')>Thanh toán toàn bộ (100%)</option></select></label>
            <p>Đặt cọc trong 48 giờ sau khi tạo đơn hoặc trước giờ khởi hành nếu sớm hơn. Hủy trực tuyến cần gửi trước ngày đi ít nhất 7 ngày; gần ngày đi vui lòng liên hệ để xử lý. Việc hủy/hoàn tiền cần admin duyệt theo điều kiện báo giá.</p>
            <label class="private-check"><input type="checkbox" name="agree" value="1" required> Tôi đồng ý tổng giá, lịch đi, dịch vụ và điều kiện báo giá trên.</label>
            <button type="submit" class="button">Đồng ý và tạo đơn tour riêng</button>
        </form></section>
    @endif
    @if(in_array($item->status, ['pending', 'quoted']))<form method="post" action="{{ route('private-tours.cancel', $item->id) }}">@csrf<button class="private-secondary" type="submit">Rút yêu cầu này</button></form>@endif
</main>
@include('clients.blocks.cuoi-trang-giao-dien')
