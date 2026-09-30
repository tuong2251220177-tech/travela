@php
    $tourImage = data_get($tour->images, '0') ?: 'clients/assets/images/widgets/tour1.jpg';
    $tourImageUrl = str_contains($tourImage, '/') ? asset($tourImage) : asset('admin/assets/images/gallery-tours/' . $tourImage);
@endphp
<article class="tour-card">
    <a class="tour-image" href="{{ route('tour-detail', ['id' => $tour->tourId]) }}" aria-label="Xem tour {{ $tour->title }}">
        <img src="{{ $tourImageUrl }}" alt="{{ $tour->title }}" loading="lazy" width="480" height="320">
        <span class="tour-badge">Nhận đăng ký theo đoàn</span>
        @if(($tour->rating ?? 0) > 0)<span class="tour-rating"><span aria-hidden="true">★</span> {{ number_format($tour->rating, 1) }}</span>@endif
    </a>
    <div class="tour-body"><div class="tour-meta"><span><i class="far fa-clock" aria-hidden="true"></i> {{ $tour->time }}</span><span><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $tour->destination }}</span></div>
        <h3><a href="{{ route('tour-detail', ['id' => $tour->tourId]) }}">{{ $tour->title }}</a></h3>
        @if(request()->routeIs('home') && !empty($tour->startDate))<small class="tour-departure"><i class="far fa-calendar-alt" aria-hidden="true"></i> Khởi hành {{ \Carbon\Carbon::parse($tour->startDate)->format('d/m/Y') }}</small>@endif
        <p class="tour-seats "><i class="fas fa-user-friends" aria-hidden="true"></i> Nhận đăng ký theo đoàn</p>
        <div class="tour-bottom"><div><small>GIÁ NGƯỜI LỚN</small><strong>{{ number_format($tour->priceAdult, 0, ',', '.') }}<span> ₫</span></strong><span class="per-person">/ người</span></div><a class="card-link" href="{{ route('tour-detail', ['id' => $tour->tourId]) }}">Chi tiết <span aria-hidden="true">↗</span></a></div>
    </div>
</article>
