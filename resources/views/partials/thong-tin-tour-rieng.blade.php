<link rel="stylesheet" href="{{ asset('clients/assets/css/tour-rieng.css?v=20260929') }}">
@if(session('success'))<p class="private-notice" role="status">{{ session('success') }}</p>@endif
@if($errors->any())<div class="private-notice private-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="private-card">
    <span class="private-status">{{ \App\Services\TourRieng::LABELS[$item->status] }}</span>
    <h2>{{ $item->tourTitle }}</h2>
    <p>Yêu cầu #{{ $item->id }} · Đoàn riêng, không ghép khách khác.</p>
    <dl class="private-facts">
        <div><dt>Nhóm khách</dt><dd>{{ $item->numAdults }} người lớn · {{ $item->numChildren }} trẻ em</dd></div>
        <div><dt>Ngày đi mong muốn</dt><dd>{{ date('d/m/Y', strtotime($item->requestedDate)) }}</dd></div>
        <div><dt>Người liên hệ</dt><dd>{{ $item->fullName }} · {{ $item->phoneNumber }}</dd></div>
        <div><dt>Email</dt><dd>{{ $item->email }}</dd></div>
    </dl>
    @if($item->notes)<h3>Yêu cầu của nhóm</h3><p class="private-text">{{ $item->notes }}</p>@endif
    @if($item->quoteTotal)
        <div class="private-quote">
            <span>Tổng giá cả nhóm</span><strong>{{ number_format($item->quoteTotal, 0, ',', '.') }} ₫</strong>
            <p>Đặt cọc 20%: {{ number_format(round($item->quoteTotal * .2), 0, ',', '.') }} ₫. Không áp dụng mã giảm giá tour ghép.</p>
            <p>Lịch đi: <b>{{ date('d/m/Y H:i', strtotime($item->startDate)) }}</b> → <b>{{ date('d/m/Y H:i', strtotime($item->endDate)) }}</b></p>
            <p>Hạn chấp nhận: {{ date('d/m/Y H:i', strtotime($item->expiresAt)) }} (giờ Việt Nam).</p>
            @if($item->status === 'quoted' && \Carbon\Carbon::parse($item->expiresAt)->isPast())<p class="private-error">Báo giá đã hết hạn. Vui lòng liên hệ để được báo giá lại.</p>@endif
        </div>
    @endif
    @if($item->quoteNotes)<h3>{{ $item->status === 'declined' ? 'Phản hồi từ Travela' : 'Dịch vụ và điều kiện báo giá' }}</h3><p class="private-text">{{ $item->quoteNotes }}</p>@endif
</section>
