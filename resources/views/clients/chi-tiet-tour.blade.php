@include('clients.blocks.dau-trang-giao-dien', ['pageClass' => 'tour-detail-page pastel-detail'])
@php
    $seats = max(0, (int) ($tourDetail->maxGuests ?? 30));
    $gallery = collect($tourDetail->images ?? [])->filter()->map(function ($image) {
        return str_contains($image, '/') ? asset($image) : asset('admin/assets/images/gallery-tours/' . $image);
    })->values();
    if ($gallery->isEmpty()) $gallery->push(asset('clients/assets/images/widgets/tour1.jpg'));
@endphp
<main id="main" class="shell detail-main">
    <nav class="page-breadcrumb" aria-label="Đường dẫn"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><a href="{{ route('tours') }}">Khám phá tour</a><span>/</span><span>{{ $tourDetail->destination }}</span></nav>
    <div class="detail-title"><div class="detail-tags"><span>✦ HÀNH TRÌNH CÙNG TRAVELA</span><span class="{{ $seats ? 'available-tag' : 'sold-out-tag' }}">{{ $tourDetail->minGuests }}–{{ $tourDetail->maxGuests }} khách / đoàn</span></div><h1>{{ $tourDetail->title }}</h1><div class="detail-meta"><a href="#tour-reviews"><span class="star-text">★</span> <strong>{{ number_format($avgStar, 1) }}</strong> ({{ $countReview }} đánh giá)</a><span><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $tourDetail->destination }}</span><span><i class="far fa-clock" aria-hidden="true"></i> {{ $tourDetail->time }}</span><button type="button" class="share-tour"><i class="fas fa-share-alt" aria-hidden="true"></i> Chia sẻ tour</button><span id="share-status" role="status"></span></div></div>
    <section class="private-tour-callout" aria-label="Yêu cầu tour riêng">
        <div><strong>Muốn đi riêng cùng gia đình hoặc nhóm bạn?</strong><p>Chọn ngày mong muốn và nhận báo giá cho cả nhóm, không ghép khách khác.</p></div>
        <a class="button private-tour-button" href="{{ route('private-tours.index', ['tourId' => $tourDetail->tourId]) }}"><i class="fas fa-users" aria-hidden="true"></i> Yêu cầu tour riêng</a>
    </section>
    <section class="detail-gallery gallery-count-{{ $gallery->count() }}" aria-label="Hình ảnh tour">
        @foreach($gallery as $image)
            <button class="gallery-tile" type="button" data-gallery-index="{{ $loop->index }}" aria-label="Xem ảnh {{ $loop->iteration }} của {{ $tourDetail->title }}"><img src="{{ $image }}" alt="{{ $tourDetail->title }} — ảnh {{ $loop->iteration }}" {{ $loop->first ? 'fetchpriority=high' : 'loading=lazy' }}></button>
        @endforeach
        <button class="gallery-all" type="button" data-gallery-index="0"><i class="far fa-images" aria-hidden="true"></i> Xem {{ $gallery->count() }} hình ảnh</button>
    </section>
    <dialog class="gallery-dialog" aria-label="Xem ảnh tour"><div class="gallery-toolbar"><span id="gallery-position" aria-live="polite"></span><button type="button" class="gallery-close" aria-label="Đóng ảnh">×</button></div><img id="gallery-large" alt=""><div class="gallery-controls"><button type="button" class="gallery-prev" aria-label="Ảnh trước">←</button><button type="button" class="gallery-next" aria-label="Ảnh tiếp theo">→</button></div></dialog>
    <div class="detail-layout">
        <div class="detail-content">
            <div class="detail-facts"><div><i class="far fa-calendar-alt" aria-hidden="true"></i><small>THỜI GIAN</small><strong>{{ $tourDetail->time }}</strong></div><div><i class="fas fa-map-marker-alt" aria-hidden="true"></i><small>ĐIỂM ĐẾN</small><strong>{{ $tourDetail->destination }}</strong></div><div><i class="far fa-calendar-check" aria-hidden="true"></i><small>KHỞI HÀNH</small><strong>{{ date('d/m/Y', strtotime($tourDetail->startDate)) }}</strong></div><div><i class="fas fa-users" aria-hidden="true"></i><small>QUY MÔ ĐOÀN</small><strong>{{ $tourDetail->minGuests }}–{{ $tourDetail->maxGuests }} khách</strong></div></div>
            <nav class="detail-tabs" aria-label="Nội dung tour"><a href="#overview">Tổng quan</a><a href="#itinerary">Lịch trình</a><a href="#services">Dịch vụ</a><a href="#cancellation">Chính sách</a><a href="#tour-reviews">Đánh giá</a></nav>
            <section class="detail-panel" id="overview"><h2><i class="far fa-compass" aria-hidden="true"></i> Khám Phá Hành Trình</h2><div class="rich-content">{!! $tourDetail->description !!}</div></section>
            <section class="detail-panel" id="itinerary"><div class="panel-heading"><h2><i class="fas fa-route" aria-hidden="true"></i> Lịch Trình Chi Tiết</h2><button class="text-link expand-itinerary" type="button" aria-expanded="false">Mở tất cả</button></div><div class="itinerary-list">
            @forelse($tourDetail->timeline as $timeline)
                @php
                    $dayImage = null;
                    $document = new \DOMDocument();
                    $previousErrors = libxml_use_internal_errors(true);
                    $document->loadHTML('<?xml encoding="utf-8" ?>' . ($timeline->description ?: '<p></p>'));
                    foreach ($document->getElementsByTagName('img') as $node) {
                        $source = $node->getAttribute('src');
                        if (preg_match('~^https?://|^/(?!/)~i', $source)) { $dayImage = $source; break; }
                    }
                    libxml_clear_errors(); libxml_use_internal_errors($previousErrors);
                    $preview = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '</li>', '<br>', '<br/>'], ' ', $timeline->description)), ENT_QUOTES, 'UTF-8'))), 150);
                @endphp
                <article class="itinerary-stop">
                    <figure><img src="{{ $dayImage ?: $gallery[$loop->index % $gallery->count()] }}" alt="{{ $dayImage ? $timeline->title : 'Hình ảnh tour ' . $tourDetail->title }}" loading="lazy"><figcaption>{{ $dayImage ? 'Hình ảnh trong lịch trình' : 'Hình ảnh tổng quan của tour' }}</figcaption></figure>
                    <div class="itinerary-stop-content"><span class="eyebrow">NGÀY {{ $loop->iteration }}</span><h3>{{ $timeline->title }}</h3><p class="itinerary-preview">{{ $preview }}</p>
                    <a class="itinerary-map" href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($timeline->title . ' ' . $tourDetail->destination) }}" target="_blank" rel="noopener noreferrer">Tìm điểm đến trên bản đồ ↗</a>
                    <details class="itinerary-day"><summary><span>Xem lịch trình đầy đủ</span><span class="day-chevron" aria-hidden="true">⌄</span></summary><div class="rich-content">{!! $timeline->description !!}</div></details></div>
                </article>
            @empty
                <p class="muted">Lịch trình đang được cập nhật. Liên hệ Travela để được tư vấn chi tiết.</p>
            @endforelse
            </div></section>
            <section class="detail-panel" id="services"><h2><i class="far fa-check-circle" aria-hidden="true"></i> Dịch Vụ & Thông Tin Cần Biết</h2><div class="detail-service-grid"><div><h3><i class="fas fa-check" aria-hidden="true"></i> Dịch vụ theo chương trình</h3><p>Xem mô tả và lịch trình phía trên để biết các dịch vụ được nêu cho tour này.</p><p>Thông tin lưu trú, phương tiện và bữa ăn cần được xác nhận theo chương trình cụ thể.</p></div><div><h3><i class="fas fa-info-circle" aria-hidden="true"></i> Chi phí cần xác nhận thêm</h3><p>Liên hệ Travela về dịch vụ bổ sung, nhu cầu riêng và các khoản chưa được nêu trong lịch trình trước khi đặt.</p><a class="text-link" href="{{ route('contact') }}">Nhận tư vấn chi tiết ↗</a></div></div></section>
            <section class="detail-panel" id="cancellation"><h2><i class="fas fa-shield-alt" aria-hidden="true"></i> Thanh Toán & Yêu Cầu Hủy</h2><p class="muted">Theo dõi và gửi yêu cầu trong chi tiết đơn đặt tour của bạn.</p><div class="detail-policy-grid"><div><small>GIỮ CHỖ</small><h3>Cọc trước 20%</h3><p>Hạn cọc trong 48 giờ sau khi đặt, không muộn hơn thời điểm khởi hành. Nhân viên đối chiếu trước khi ghi nhận tiền.</p></div><div><small>TRƯỚC NGÀY ĐI</small><h3>Gửi yêu cầu hủy</h3><p>Yêu cầu hủy trực tuyến cần gửi trước ngày đi ít nhất 7 ngày và chờ nhân viên xét duyệt.</p></div><div><small>CẦN HỖ TRỢ</small><h3>Liên hệ Travela</h3><p>Khoản hoàn tiền, nếu có, cần nhân viên đối soát. Gửi yêu cầu hủy không tự động chuyển hoàn tiền.</p></div></div></section>
            <section class="detail-panel" id="tour-reviews"><div id="partials_reviews">@include('clients.partials.danh-gia')</div>
            @if($checkDisplay !== 'hide')
                @if(session()->has('username'))
                <form id="stitch-review-form" method="post" action="{{ route('reviews') }}" data-check-url="{{ route('checkBooking') }}" data-tour-id="{{ $tourDetail->tourId }}">
                    @csrf
                    <h3>Chia sẻ trải nghiệm của bạn</h3><p class="muted">Đánh giá dành cho khách đã trải nghiệm tour.</p>
                    <fieldset class="review-rating"><legend>Bạn đánh giá hành trình này thế nào?</legend>@for($star=1; $star<=5; $star++)<label><input type="radio" name="rating" value="{{ $star }}" required><span>{{ $star }} ★</span></label>@endfor</fieldset>
                    <label for="review-message">Nội dung đánh giá</label><textarea id="review-message" name="message" rows="4" required placeholder="Điều gì khiến chuyến đi của bạn đáng nhớ?"></textarea><p id="review-status" role="status"></p><button class="button" type="submit">Gửi đánh giá ↗</button>
                </form>
                @else
                    <p class="review-login"><a class="text-link" href="{{ route('login') }}">Đăng nhập</a> để chia sẻ trải nghiệm sau chuyến đi.</p>
                @endif
            @endif
            </section>
        </div>
        <aside class="detail-sidebar" aria-label="Đặt tour"><div class="booking-widget"><span class="eyebrow">HÀNH TRÌNH CỦA BẠN</span><div class="widget-price"><strong>{{ number_format($tourDetail->priceAdult, 0, ',', '.') }} ₫</strong><span>/ người lớn</span></div><p class="widget-caption">Chọn số lượng khách để xem chi phí dự kiến.</p>
            <form action="{{ route('booking', ['id' => $tourDetail->tourId]) }}" method="post" id="detail-booking" data-seats="{{ $seats }}" data-adult-price="{{ $tourDetail->priceAdult }}" data-child-price="{{ $tourDetail->priceChild }}">
                @csrf
                <div class="departure-box"><span><i class="far fa-calendar-alt" aria-hidden="true"></i> Ngày khởi hành</span><strong>{{ date('d/m/Y', strtotime($tourDetail->startDate)) }}</strong></div><div class="departure-box"><span>Ngày kết thúc</span><strong>{{ date('d/m/Y', strtotime($tourDetail->endDate)) }}</strong></div>
                <div class="guest-row"><div><label for="detail-adults">Người lớn</label><small>{{ number_format($tourDetail->priceAdult, 0, ',', '.') }} ₫ / người</small></div><div class="guest-stepper"><button type="button" data-step="-1" data-input="detail-adults" aria-label="Giảm số người lớn">−</button><input id="detail-adults" name="numAdults" type="number" min="1" max="{{ max(1, $seats) }}" value="1" readonly aria-label="Số người lớn"><button type="button" data-step="1" data-input="detail-adults" aria-label="Tăng số người lớn">+</button></div></div>
                <div class="guest-row"><div><label for="detail-children">Trẻ em</label><small>{{ number_format($tourDetail->priceChild, 0, ',', '.') }} ₫ / người</small></div><div class="guest-stepper"><button type="button" data-step="-1" data-input="detail-children" aria-label="Giảm số trẻ em">−</button><input id="detail-children" name="numChildren" type="number" min="0" max="{{ $seats }}" value="0" readonly aria-label="Số trẻ em"><button type="button" data-step="1" data-input="detail-children" aria-label="Tăng số trẻ em">+</button></div></div>
                <div class="widget-total" aria-live="polite"><span>TỔNG DỰ KIẾN</span><strong id="detail-total">{{ number_format($tourDetail->priceAdult, 0, ',', '.') }} ₫</strong></div><p class="detail-coupon-note"><i class="fas fa-ticket-alt" aria-hidden="true"></i> Chọn mã giảm giá ở bước đặt tour tiếp theo.</p>
                @php($canBook = $tourDetail->availability && $tourDetail->startDate && \Carbon\Carbon::parse($tourDetail->startDate)->isFuture())
                <button class="button widget-submit" type="submit" @if(!$canBook) disabled @endif>{{ $canBook ? 'Tiếp tục đặt tour ↗' : 'Tour không còn nhận đặt chỗ' }}</button><p class="widget-footnote">Bạn sẽ điền thông tin và chọn thanh toán ở bước tiếp theo.</p>
            </form><a class="button private-tour-button" href="{{ route('private-tours.index', ['tourId' => $tourDetail->tourId]) }}"><i class="fas fa-users" aria-hidden="true"></i> Yêu cầu tour riêng</a><a class="widget-contact" href="{{ route('contact') }}"><i class="far fa-comments" aria-hidden="true"></i> Tôi cần tư vấn thêm</a>
        </div><div class="expert-card"><span class="field-icon"><i class="fas fa-headset" aria-hidden="true"></i></span><div><strong>Travela luôn đồng hành</strong><p>Hỗ trợ lịch trình và thông tin đặt tour.</p><a class="text-link" href="{{ route('contact') }}">Kết nối với chúng tôi ↗</a></div></div></aside>
    </div>
    @if(count($tourRecommendations ?? []))<section class="section"><div class="section-heading"><div><span class="eyebrow">KHÁM PHÁ THÊM</span><h2>Những Hành Trình Bạn Có Thể Thích</h2></div><a class="text-link" href="{{ route('tours') }}">Tất cả tour ↗</a></div><div class="tour-grid recommended-grid">@foreach($tourRecommendations as $tour) @include('clients.partials.the-tour-trang-chu') @endforeach</div></section>@endif
</main>
@include('clients.blocks.cuoi-trang-giao-dien')
