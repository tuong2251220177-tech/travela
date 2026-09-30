@include('clients.blocks.dau-trang')
@include('clients.blocks.bieu-ngu')
<section class="shell section"><div class="section-heading"><div><span class="eyebrow">ĐỘI NGŨ TRAVELA</span><h2>Cùng Bạn Trên Mọi Nẻo Đường</h2><p>Những người đồng hành giúp bạn hiểu thêm về điểm đến và tận hưởng chuyến đi.</p></div><a class="text-link" href="{{ route('contact') }}">Liên hệ tư vấn ↗</a></div><div class="guide-cards">
@forelse($guides as $guide)
<article class="guide-person"><img src="{{ asset($guide->avatar ?? 'clients/assets/images/team/guide1.jpg') }}" alt="{{ $guide->display_name }}" loading="lazy"><div><span class="eyebrow">{{ $guide->designation }}</span><h2>{{ $guide->display_name }}</h2><p>{{ Str::limit($guide->description ?? 'Đồng hành cùng bạn khám phá những điểm đến mới.', 150) }}</p>@if(!empty($guide->email))<a href="mailto:{{ $guide->email }}">Liên hệ hướng dẫn viên ↗</a>@endif</div></article>
@empty
<div class="catalog-empty"><h3>Đội ngũ đang được cập nhật</h3><p>Liên hệ Travela để tìm hiểu thông tin hướng dẫn viên cho chuyến đi của bạn.</p></div>
@endforelse
</div></section>
@include('clients.blocks.dang-ky-nhan-tin')
@include('clients.blocks.chan-trang')
