@include('clients.blocks.dau-trang-giao-dien', ['pageClass' => 'private-tour-page'])
<link rel="stylesheet" href="{{ asset('clients/assets/css/tour-rieng.css?v=20260929') }}">
<main id="main" class="shell private-tour">
    <span class="eyebrow">CHUYẾN ĐI DÀNH RIÊNG CHO BẠN</span><h1>Đi cùng nhau, theo cách riêng.</h1>
    <p>Chọn lịch trình tham khảo và gửi nhu cầu. Travela kiểm tra xe, hướng dẫn viên và gửi báo giá trước khi bạn đặt cọc.</p>
    @if($errors->any())<div class="private-notice private-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="private-columns">
    <section class="private-card"><h2>Gửi yêu cầu tour riêng</h2>
    <form method="post" action="{{ route('private-tours.store') }}" class="private-form">
        @csrf <input type="hidden" name="token" value="{{ old('token', (string) \Illuminate\Support\Str::uuid()) }}">
        <label>Lịch trình tham khảo<select name="tourId" required><option value="">Chọn tour</option>@foreach($tours as $tour)<option value="{{ $tour->tourId }}" @selected(old('tourId', request('tourId')) == $tour->tourId)>{{ $tour->title }}</option>@endforeach</select></label>
        <div class="private-fields"><label>Người lớn<input type="number" name="numAdults" min="1" max="100" value="{{ old('numAdults', 10) }}" required></label><label>Trẻ em<input type="number" name="numChildren" min="0" max="100" value="{{ old('numChildren', 0) }}" required></label></div>
        <label>Ngày đi mong muốn<input type="date" name="requestedDate" min="{{ now()->addDay()->toDateString() }}" value="{{ old('requestedDate') }}" required></label>
        <label>Họ tên người liên hệ<input name="fullName" maxlength="150" autocomplete="name" value="{{ old('fullName') }}" required></label>
        <div class="private-fields"><label>Email<input type="email" name="email" maxlength="150" autocomplete="email" value="{{ old('email') }}" required></label><label>Số điện thoại<input type="tel" name="phoneNumber" maxlength="20" autocomplete="tel" value="{{ old('phoneNumber') }}" required></label></div>
        <label>Địa chỉ liên hệ<input name="address" maxlength="255" autocomplete="street-address" value="{{ old('address') }}" required></label>
        <label>Nhu cầu của nhóm<textarea name="notes" rows="4" maxlength="3000" placeholder="Nơi đón, loại xe, khách sạn, người cao tuổi, trẻ nhỏ…">{{ old('notes') }}</textarea></label>
        <p>Gửi yêu cầu chưa tạo đơn và chưa thu tiền. Nhóm lớn có thể cần nhiều xe; admin sẽ xác nhận phương án trong báo giá.</p>
        <button class="button" type="submit">Gửi yêu cầu tư vấn ↗</button>
    </form></section>
    <section class="private-card"><h2>Yêu cầu của bạn</h2>
        @forelse($requests as $item)<a class="private-list-item" href="{{ route('private-tours.show', $item->id) }}"><span class="private-status">{{ \App\Services\TourRieng::LABELS[$item->status] }}</span><h3>#{{ $item->id }} · {{ $item->tourTitle }}</h3><p>{{ $item->numAdults + $item->numChildren }} khách · {{ date('d/m/Y', strtotime($item->requestedDate)) }}</p><span>Xem yêu cầu và báo giá →</span></a>@empty<p>Bạn chưa có yêu cầu nào.</p>@endforelse
        {{ $requests->links('pagination::bootstrap-4') }}
    </section></div>
</main>
@include('clients.blocks.cuoi-trang-giao-dien')
