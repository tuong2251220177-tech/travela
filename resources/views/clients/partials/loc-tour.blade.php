@forelse ($tours as $tour)
<div class="col-xl-4 col-md-6 catalog-card-column">@include('clients.partials.the-tour-trang-chu')</div>
@empty
<div class="col-12"><div class="catalog-empty"><span>⌕</span><h3>Chưa tìm thấy hành trình phù hợp</h3><p>Thử thay đổi khoảng giá, điểm đến hoặc thời gian để xem thêm tour.</p><a href="{{ route('tours') }}" class="button">Xem tất cả tour</a></div></div>
@endforelse
<div class="col-lg-12">
    <ul class="pagination justify-content-center pt-15 flex-wrap pagination-tours" data-aos="fade-up"
        data-aos-duration="1500" data-aos-offset="50">
        <!-- Previous Page Link -->
        @if ($tours->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="far fa-chevron-left"></i></span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $tours->previousPageUrl() }}"><i class="far fa-chevron-left"></i></a>
            </li>
        @endif

        <!-- Page Numbers -->
        @for ($i = 1; $i <= $tours->lastPage(); $i++)
            <li class="page-item @if ($i == $tours->currentPage()) active @endif">
                <a class="page-link" href="{{ $tours->url($i) }}">{{ $i }}</a>
            </li>
        @endfor

        <!-- Next Page Link -->
        @if ($tours->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $tours->nextPageUrl() }}"><i class="far fa-chevron-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link"><i class="far fa-chevron-right"></i></span>
            </li>
        @endif
    </ul>
</div>
