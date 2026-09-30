@php
    $navLinks = [
        ['home', 'Trang chủ'], ['tours', 'Khám phá tour'], ['destination', 'Điểm đến'], ['about', 'Về Travela'], ['contact', 'Liên hệ'],
    ];
@endphp
<header class="site-header"><div class="shell header-inner">
    <a class="brand" href="{{ route('home') }}" aria-label="Travela — Trang chủ"><span class="brand-mark">✦</span>Travela<span class="brand-dot">.</span></a>
    <nav class="desktop-nav" aria-label="Điều hướng chính">
        @foreach($navLinks as [$navRoute, $navLabel])
            <a href="{{ route($navRoute) }}" @if(request()->routeIs($navRoute)) class="active" aria-current="page" @endif>{{ $navLabel }}</a>
        @endforeach
    </nav>
    <div class="header-actions">
        <a class="support-link" href="{{ route('contact') }}"><i class="fas fa-headset" aria-hidden="true"></i><span><small>ĐỒNG HÀNH CÙNG BẠN</small><strong>Tư vấn chuyến đi</strong></span></a>
        <a class="button small" href="{{ route('tours') }}">Đặt tour ngay ↗</a>
        <details class="account-menu"><summary class="account-link" aria-label="Menu tài khoản"><i class="far fa-user" aria-hidden="true"></i></summary><div class="account-menu-panel">
            @if(session()->has('username'))
                <span>Xin chào, {{ session('username') }}</span><a href="{{ route('user-profile') }}">Thông tin cá nhân</a><a href="{{ route('my-tours') }}">Lịch sử đặt tour</a><a href="{{ route('private-tours.index') }}">Yêu cầu tour riêng</a><a href="{{ route('logout') }}">Đăng xuất ↗</a>
            @else
                <span>Chào mừng đến Travela</span><a href="{{ route('login') }}">Đăng nhập / Đăng ký</a><a href="{{ route('guide.login') }}">Dành cho hướng dẫn viên</a>
            @endif
        </div></details>
        <button class="menu-toggle" aria-controls="mobile-nav" aria-expanded="false" aria-label="Mở menu"><i class="fas fa-bars" aria-hidden="true"></i></button>
    </div>
</div><nav id="mobile-nav" class="mobile-nav" aria-label="Điều hướng di động" hidden>
    @foreach($navLinks as [$navRoute, $navLabel])<a href="{{ route($navRoute) }}">{{ $navLabel }}</a>@endforeach
    <a href="{{ route('team') }}">Đội ngũ hướng dẫn viên</a><a href="{{ route('my-tours') }}">Lịch sử đặt tour</a><a href="{{ route('private-tours.index') }}">Yêu cầu tour riêng</a>
    @if(session()->has('username'))<a href="{{ route('user-profile') }}">Tài khoản</a><a href="{{ route('logout') }}">Đăng xuất</a>@else<a href="{{ route('login') }}">Đăng nhập / Đăng ký</a>@endif
</nav></header>
