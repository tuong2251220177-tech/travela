@include('clients.blocks.dau-trang')
@include('clients.blocks.bieu-ngu')
<section class="popular-destinations-area py-100"><div class="container"><div class="section-heading"><div><span class="eyebrow">ĐIỂM ĐẾN TRÊN KHẮP VIỆT NAM</span><h2>Chọn Miền Đất Bạn Yêu</h2><p>Tìm cảm hứng từ những hành trình qua ba miền Bắc, Trung, Nam.</p></div></div><ul class="destinations-nav mb-4" aria-label="Lọc theo vùng miền"><li class="active" data-filter="*"><button type="button">Tất cả</button></li><li data-filter=".domain-b"><button type="button">Miền Bắc</button></li><li data-filter=".domain-t"><button type="button">Miền Trung</button></li><li data-filter=".domain-n"><button type="button">Miền Nam</button></li></ul><div class="row destinations-active">
@forelse($tours as $tour)
<div class="col-lg-4 col-md-6 item domain-{{ $tour->domain }} catalog-card-column">@include('clients.partials.the-tour-trang-chu')</div>
@empty
<div class="col-12"><div class="catalog-empty"><h3>Những điểm đến mới đang được chuẩn bị</h3><p>Liên hệ Travela để được tư vấn về chuyến đi của bạn.</p></div></div>
@endforelse
</div></div></section>
@include('clients.blocks.dang-ky-nhan-tin')
@include('clients.blocks.chan-trang')
