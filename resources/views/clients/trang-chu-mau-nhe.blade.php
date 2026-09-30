<!DOCTYPE html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>Travela — Khám phá Việt Nam theo cách riêng của bạn</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('clients/assets/css/fontawesome-5.14.0.min.css') }}"><link rel="stylesheet" href="{{ asset('clients/assets/css/home-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/pastel-photos.css') }}"><link rel="stylesheet" href="{{ asset('clients/assets/css/home-pastel.css?v=20260917-halong') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/giao-dien-moi.css?v=20260925') }}">
</head><body class="pastel-home">
<a class="skip-link" href="#main">Đến nội dung chính</a>@include('clients.blocks.dieu-huong-giao-dien')
<main id="main">
<section class="pastel-hero pastel-hero-halong">
    <div class="shell hero-layout">
        <div class="hero-copy">
            <span class="pastel-kicker">VIỆT NAM · NHỮNG HÀNH TRÌNH ĐÁNG NHỚ</span>
            <h1>Đi để khám phá.<br><span>Về với những điều mới.</span></h1>
            <p>Một miền đất mới, một câu chuyện riêng. Cùng Travela tìm chuyến đi bạn mong đợi, từ núi rừng đến biển xanh.</p>
            <div class="hero-buttons">
                <a class="button" href="#journeys">Khám phá tour <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                <a class="hero-secondary" href="{{ route('destination') }}">Tìm cảm hứng chuyến đi ↗</a>
            </div>
        </div>
        <div class="hero-location"><small>ĐIỂM ĐẾN TRONG KHUNG HÌNH</small><strong>Vịnh Hạ Long</strong><span>Quảng Ninh, Việt Nam</span></div>
    </div>
</section>
<section class="shell search-section" aria-label="Tìm kiếm tour"><form class="search-dock" action="{{ route('search') }}" method="get" id="home-search">
<div class="search-field"><span class="field-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span><div><label for="destination">ĐIỂM ĐẾN</label><select id="destination" name="destination">
@foreach(['dn'=>'Đà Nẵng','hl'=>'Hạ Long','pq'=>'Phú Quốc','dl'=>'Đà Lạt','hn'=>'Hà Nội','hcm'=>'TP. Hồ Chí Minh','nb'=>'Ninh Bình','cd'=>'Côn Đảo','kh'=>'Khánh Hòa','ct'=>'Cần Thơ','vt'=>'Vũng Tàu','qn'=>'Quảng Ninh','la'=>'Lào Cai','bd'=>'Bình Định','qt'=>'Quảng Trị'] as $code=>$name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
</select></div></div>
<div class="search-field"><span class="field-icon"><i class="far fa-calendar-alt" aria-hidden="true"></i></span><div><label for="departure-from">KHỞI HÀNH TỪ</label><input id="departure-from" type="date"><input type="hidden" name="start_date" id="start-date-value"></div></div>
<div class="search-field"><span class="field-icon"><i class="far fa-calendar-check" aria-hidden="true"></i></span><div><label for="departure-to">ĐẾN NGÀY</label><input id="departure-to" type="date"><input type="hidden" name="end_date" id="end-date-value"></div></div><button class="button" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Tìm kiếm tour</button></form>
@if($errors->any())<p class="empty-state" role="alert">{{ $errors->first() }}</p>@endif
<div class="home-assurances" aria-label="Tiện ích đặt tour">
    <span><i class="far fa-map" aria-hidden="true"></i> Hành trình khắp ba miền</span>
    <span><i class="far fa-calendar-check" aria-hidden="true"></i> Lịch khởi hành rõ ràng</span>
    <span><i class="fas fa-headset" aria-hidden="true"></i> Tư vấn trước chuyến đi</span>
</div>
</section>
<section class="shell section" id="journeys"><div class="section-heading"><div><span class="eyebrow">CHUYẾN ĐI ĐANG CHỜ BẠN</span><h2>Tour Nổi Bật</h2><p>Những hành trình được chọn lọc để bạn dễ dàng lựa chọn.</p></div><div class="region-tabs" aria-label="Lọc tour theo khu vực">@foreach(['all'=>'Tất cả','b'=>'Miền Bắc','t'=>'Miền Trung','n'=>'Miền Nam'] as $region=>$label)<button type="button" data-region="{{ $region }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}</button>@endforeach</div></div>
<div class="tour-grid pastel-tour-grid">@foreach($tours as $tour)<div class="pastel-tour-item" data-domain="{{ $tour->domain ?? '' }}">@include('clients.partials.the-tour-trang-chu')</div>@endforeach</div>
<p id="region-empty" class="empty-state" @if(count($tours)) hidden @endif>Chưa có tour trong khu vực này. Hãy liên hệ Travela để được tư vấn.</p><div class="section-more"><a class="pastel-link" href="{{ route('tours') }}">Xem tất cả tour →</a></div></section>
<section class="pastel-destinations"><div class="shell section"><div class="center-heading"><span class="eyebrow">CẢM HỨNG LÊN ĐƯỜNG</span><h2>Điểm Đến Nổi Bật</h2><p>Khám phá những điểm đến được yêu thích trên khắp Việt Nam</p></div><div class="destination-grid">
@foreach([['dn','Đà Nẵng','Biển xanh và những cây cầu',7],['dl','Đà Lạt','Thành phố ngàn hoa giữa đồi thông',8],['hl','Hạ Long','Vịnh biển kỳ quan với muôn ngàn đảo đá',9],['la','Sa Pa','Ruộng bậc thang và những đỉnh núi trong mây',10],['kh','Nha Trang','Vịnh biển trong xanh, kỳ nghỉ đầy cảm hứng',11],['pq','Phú Quốc','Thiên đường nghỉ dưỡng giữa đảo ngọc',12]] as [$code,$name,$description,$photo])
<a class="destination-tile pastel-photo-{{ $photo }}" href="{{ route('search',['destination'=>$code]) }}"><div><small>KHÁM PHÁ</small><h3>{{ $name }}</h3><p>{{ $description }}</p><span>Khám phá ngay →</span></div></a>@endforeach</div></div></section>
<section class="pastel-regions"><div class="shell section"><div class="center-heading"><span class="eyebrow">ĐI KHẮP VIỆT NAM</span><h2>Khám Phá Theo Khu Vực</h2><p>Mỗi miền đất là một hành trình đáng nhớ</p></div><div class="region-grid">
@foreach([['b','Miền Bắc','Khám phá núi rừng hùng vĩ, di sản và những vùng đất giàu bản sắc văn hóa nghìn năm.',13],['t','Miền Trung','Biển xanh ngọc bích, cố đô cổ kính và những cung đường ven biển đầy nắng vàng.',14],['n','Miền Nam','Từ thành phố năng động đến miệt vườn sông nước, tận hưởng nhịp sống phương Nam.',15]] as [$domain,$name,$description,$photo])
<article class="region-card"><div class="region-photo pastel-photo-{{ $photo }}"><span>{{ $name }}</span></div><div class="region-content"><p>{{ $description }}</p><a class="pastel-link" href="{{ route('tours',['domain'=>$domain]) }}">Khám phá {{ $name }} →</a></div></article>@endforeach</div></div></section>
<section class="pastel-why"><div class="shell section"><div class="center-heading"><span class="eyebrow">ĐỒNG HÀNH CÙNG BẠN</span><h2>Tại Sao Chọn Travela?</h2><p>Đơn giản hơn trong từng bước của hành trình.</p></div><div class="why-grid">
@foreach([['map','Tour đa dạng','Nhiều hành trình hấp dẫn cho kỳ nghỉ và sở thích của bạn.'],['calendar-check','Đặt tour dễ dàng','Tìm kiếm và đặt chỗ trực tuyến trong vài bước thao tác.'],['shield-alt','Thông tin minh bạch','Giá tour, lịch trình và số chỗ hiển thị rõ ràng trước khi đặt.'],['headset','Hỗ trợ tận tâm','Travela đồng hành và giải đáp thắc mắc về chuyến đi.']] as [$icon,$name,$description])<article class="why-card"><i class="fas fa-{{ $icon }}" aria-hidden="true"></i><h3>{{ $name }}</h3><p>{{ $description }}</p></article>@endforeach</div></div></section>
@if(count($toursPopular))<section class="pastel-inspiration"><div class="shell section"><div class="center-heading"><span class="eyebrow">THÊM CẢM HỨNG MỚI</span><h2>Hành Trình Dành Cho Bạn</h2><p>Khám phá thêm những gợi ý từ Travela</p></div><div class="tour-grid pastel-tour-grid">@foreach(collect($toursPopular)->take(3) as $tour)@include('clients.partials.the-tour-trang-chu')@endforeach</div></div></section>@endif
@if(isset($homeReviews) && $homeReviews->isNotEmpty())
<section class="pastel-inspiration"><div class="shell section"><div class="center-heading"><span class="eyebrow">TRẢI NGHIỆM THỰC TẾ</span><h2>Khách Hàng Nói Gì Về Travela?</h2><p>Những đánh giá được chia sẻ sau mỗi hành trình</p></div><div class="region-grid">
@foreach($homeReviews as $review)<article class="pastel-review"><div aria-label="{{ $review->rating }} trên 5 sao" style="color:#a36700">{{ str_repeat('★', max(0,min(5,(int)$review->rating))) }}</div><a href="{{ route('tour-detail',['id'=>$review->tourId]) }}#tour-reviews"><strong>{{ $review->title }}</strong></a><p>{{ \Illuminate\Support\Str::limit($review->comment,230) }}</p><span>{{ $review->fullName }}</span></article>@endforeach
</div></div></section>@endif
<section class="shell cta-section"><div class="pastel-cta pastel-photo-16"><div><span class="eyebrow">HÀNH TRÌNH ĐANG CHỜ ĐÓN</span><h2>Sẵn Sàng Cho Chuyến Đi<br>Tiếp Theo?</h2><p>Khám phá những hành trình đáng nhớ cùng Travela.</p></div><a class="button button-white" href="{{ route('tours') }}">Khám phá tour ngay ↗</a></div></section>
</main>@include('clients.blocks.chan-trang-giao-dien')@include('clients.blocks.tro-ly-ai')
<script src="{{ asset('clients/assets/js/jquery-3.6.0.min.js') }}"></script><script>window.travelaAiChatUrl = @json(route('ai-chat')); window.travelaCsrfToken = @json(csrf_token());</script><script src="{{ asset('clients/assets/js/ai-chat.js?v=20260916-qwen') }}"></script><script src="{{ asset('clients/assets/js/home-stitch.js?v=20260917-pastel') }}"></script>
</body></html>
