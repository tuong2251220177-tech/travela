@include('admin.blocks.dau-trang')
<link rel="stylesheet" href="{{ asset('clients/assets/css/tour-rieng.css?v=20260929') }}">
<div class="container body"><div class="main_container">
@include('admin.blocks.thanh-ben')
<div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
<div class="private-tour"><section class="private-card">
    <form method="get" class="private-form"><label>Lọc trạng thái<select name="status"><option value="all">Tất cả yêu cầu</option>@foreach(\App\Services\TourRieng::LABELS as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></label><button class="btn btn-primary">Lọc yêu cầu</button></form>
    <div class="table-responsive"><table class="table"><thead><tr><th>Yêu cầu</th><th>Khách liên hệ</th><th>Ngày mong muốn</th><th>Số khách</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    @forelse($items as $item)<tr><td>#{{ $item->id }} · {{ $item->tourTitle }}</td><td>{{ $item->fullName }}<br>{{ $item->phoneNumber }}</td><td>{{ date('d/m/Y', strtotime($item->requestedDate)) }}</td><td>{{ $item->numAdults + $item->numChildren }}</td><td>{{ \App\Services\TourRieng::LABELS[$item->status] }}</td><td><a class="btn btn-primary" href="{{ route('admin.private-tours.show', $item->id) }}">Xem / báo giá</a></td></tr>@empty<tr><td colspan="6">Chưa có yêu cầu phù hợp.</td></tr>@endforelse
    </tbody></table></div>{{ $items->links('pagination::bootstrap-4') }}
</section></div></div></div></div>
@include('admin.blocks.chan-trang')
