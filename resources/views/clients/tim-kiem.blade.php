@include('clients.blocks.dau-trang')
@include('clients.blocks.bieu-ngu')
<section class="shell section"><div class="section-heading"><div><span class="eyebrow">HÀNH TRÌNH PHÙ HỢP VỚI BẠN</span><h2>Kết Quả Tìm Kiếm</h2><p>{{ count($tours) }} tour phù hợp với tìm kiếm của bạn.</p></div><a class="text-link" href="{{ route('tours') }}">Xem tất cả tour ↗</a></div><div class="tour-grid recommended-grid">
@forelse($tours as $tour)
@include('clients.partials.the-tour-trang-chu')
@empty
<div class="catalog-empty" style="grid-column:1/-1"><span>⌕</span><h3>Chưa tìm thấy hành trình phù hợp</h3><p>Thử một điểm đến khác hoặc khám phá toàn bộ tour của Travela.</p><a href="{{ route('tours') }}" class="button">Khám phá các tour</a></div>
@endforelse
</div></section>
@include('clients.blocks.dang-ky-nhan-tin')
@include('clients.blocks.chan-trang')
