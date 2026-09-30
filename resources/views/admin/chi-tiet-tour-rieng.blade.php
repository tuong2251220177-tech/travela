@include('admin.blocks.dau-trang')
<div class="container body"><div class="main_container">
@include('admin.blocks.thanh-ben')
<div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
<div class="private-tour"><a href="{{ route('admin.private-tours.index') }}">← Danh sách yêu cầu</a>
@include('partials.thong-tin-tour-rieng')
@if($item->status === 'accepted')
    <section class="private-card"><h2>Đã tạo đơn riêng #{{ $item->bookingId }}</h2><p>Thu tiền và xác nhận trong đơn; phân công hướng dẫn viên cho lịch trình có nhãn “Riêng #{{ $item->id }}”.</p><a class="btn btn-primary" href="{{ route('admin.booking-detail', ['id' => $item->bookingId]) }}">Mở đơn đặt</a><a class="btn btn-default" href="{{ route('admin.guide-assignment') }}">Phân công hướng dẫn viên</a></section>
@elseif(in_array($item->status, ['pending', 'quoted']))
    <section class="private-card"><h2>{{ $item->status === 'quoted' ? 'Cập nhật báo giá' : 'Lập báo giá' }}</h2>
    <form method="post" action="{{ route('admin.private-tours.quote', $item->id) }}" class="private-form">
        @csrf <input type="hidden" name="revision" value="{{ $item->revision }}">
        <label>Tổng giá cả nhóm (VNĐ)<input name="quoteTotal" type="number" min="1000" max="1000000000" step="1" value="{{ old('quoteTotal', $item->quoteTotal) }}" required></label>
        <div class="private-fields"><label>Khởi hành<input type="datetime-local" name="startDate" value="{{ old('startDate', $item->startDate ? date('Y-m-d\TH:i', strtotime($item->startDate)) : $item->requestedDate.'T07:00') }}" required></label><label>Kết thúc<input type="datetime-local" name="endDate" value="{{ old('endDate', $item->endDate ? date('Y-m-d\TH:i', strtotime($item->endDate)) : '') }}" required></label></div>
        <label>Hạn chấp nhận báo giá<input type="datetime-local" name="expiresAt" value="{{ old('expiresAt', $item->expiresAt ? date('Y-m-d\TH:i', strtotime($item->expiresAt)) : '') }}" required></label>
        <label>Dịch vụ và điều kiện<textarea name="quoteNotes" rows="7" minlength="10" maxlength="5000" required placeholder="Ghi rõ xe/số xe, điểm đón, khách sạn, dịch vụ bao gồm/chưa bao gồm, lịch trình điều chỉnh và chính sách hủy/hoàn tiền.">{{ old('quoteNotes', $item->quoteNotes) }}</textarea></label>
        <label class="private-check"><input type="checkbox" name="confirmedResources" value="1" required> Đã kiểm tra khả năng bố trí xe và hướng dẫn viên cho số khách, lịch đi này.</label>
        <p>Đặt cọc 20%, thời hạn 48 giờ hoặc trước giờ khởi hành. Báo giá hiển thị trong tài khoản khách; chưa gửi email tự động.</p>
        <button class="btn btn-primary" type="submit">Gửi báo giá cho khách</button>
    </form></section>
    <section class="private-card"><h2>Không thể tổ chức?</h2><form method="post" action="{{ route('admin.private-tours.decline', $item->id) }}" class="private-form">@csrf<label>Lý do phản hồi khách<textarea name="reason" minlength="5" maxlength="1000" required></textarea></label><button class="btn btn-danger" type="submit">Từ chối yêu cầu</button></form></section>
@endif
</div></div></div></div>
@include('admin.blocks.chan-trang')
