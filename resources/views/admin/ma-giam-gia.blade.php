@include('admin.blocks.dau-trang')
<div class="container body"><div class="main_container">
@include('admin.blocks.thanh-ben')
<div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
<h2>Mã giảm giá</h2>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="x_panel">
<h3>Tạo mã ngẫu nhiên</h3>
<form method="post" action="{{ route('admin.coupons.store') }}">
@csrf
<div class="row">
<div class="col-md-3 form-group"><label>Hiển thị mã</label><select class="form-control" name="isPublic"><option value="1" @selected(old('isPublic','1') == '1')>Công khai — hiện trong gợi ý</option><option value="0" @selected(old('isPublic','1') == '0')>Mã riêng — khách tự nhập</option></select></div>
<div class="col-md-3 form-group"><label>Lượt dùng mỗi tài khoản</label><input class="form-control" type="number" name="perUserLimit" value="1" readonly><small>Mỗi tài khoản chỉ dùng mỗi mã 1 lần.</small></div>
@foreach ([['quantity','Số mã cần tạo',1,1,100], ['value','Mức giảm (% hoặc đồng)',10,1,1000000000], ['minTotal','Giá trị đơn tối thiểu (đồng)',0,0,1000000000], ['usageLimit','Lượt dùng tối đa mỗi mã',1,1,1000000]] as [$name,$label,$default,$min,$max])
<div class="col-md-3 form-group"><label>{{ $label }}</label><input class="form-control" type="number" name="{{ $name }}" value="{{ old($name,$default) }}" min="{{ $min }}" max="{{ $max }}" step="1" required></div>
@endforeach
<div class="col-md-3 form-group"><label>Loại giảm</label><select class="form-control" name="type"><option value="percent" @selected(old('type') !== 'fixed')>Phần trăm (1–99%)</option><option value="fixed" @selected(old('type') === 'fixed')>Số tiền cố định</option></select></div>
<div class="col-md-3 form-group"><label>Tiền tố mã (chữ/số, tối đa 10 ký tự)</label><input class="form-control" name="prefix" maxlength="10" pattern="[A-Za-z0-9]{1,10}" value="{{ old('prefix','TOUR') }}"></div>
<div class="col-md-3 form-group"><label>Giảm tối đa (đồng, chỉ áp dụng %)</label><input class="form-control" type="number" name="maxDiscount" min="1" max="1000000000" step="1" value="{{ old('maxDiscount') }}" placeholder="Bỏ trống: không giới hạn"></div>
<div class="col-md-3 form-group"><label>Dùng đến hết ngày</label><input class="form-control" type="date" name="expiresAt" min="{{ now()->toDateString() }}" value="{{ old('expiresAt', now()->addMonth()->toDateString()) }}" required></div>
</div>
<p>Mỗi đơn dùng một mã. Lượt dùng được tính khi đặt đơn thành công; hủy đơn không trả lại lượt. Giảm tối đa đến khi đơn còn 1 đồng.</p>
<p>Mã riêng chỉ ẩn khỏi gợi ý: bất kỳ khách nào biết mã vẫn có thể nhập. Giới hạn mỗi tài khoản tính cả các đơn đã hủy.</p>
<button class="btn btn-primary" type="submit">Tạo mã ngẫu nhiên</button>
</form></div>
<div class="x_panel table-responsive"><table class="table table-bordered">
<thead><tr><th>Mã (sao chép để gửi khách)</th><th>Mức giảm</th><th>Đơn tối thiểu</th><th>Đã dùng / giới hạn</th><th>Hạn sử dụng</th><th>Người tạo</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
<tbody>@foreach($coupons as $coupon)
<tr><td><code style="user-select:all">{{ $coupon->code }}</code></td><td>{{ number_format($coupon->value,0,',','.') }} {{ $coupon->type === 'percent' ? '%' : 'đ' }} @if($coupon->maxDiscount)<br>Tối đa {{ number_format($coupon->maxDiscount,0,',','.') }} đ @endif</td>
<td>{{ number_format($coupon->minTotal,0,',','.') }} đ</td><td>{{ $coupon->usedCount }} / {{ $coupon->usageLimit }}</td><td>{{ $coupon->expiresAt ?: 'Không giới hạn (mã cũ)' }}</td><td>{{ $coupon->createdBy }}</td>
<td>{{ !$coupon->active ? 'Đã tắt' : (($coupon->expiresAt && \Carbon\Carbon::parse($coupon->expiresAt)->isPast()) ? 'Hết hạn' : ($coupon->usedCount >= $coupon->usageLimit ? 'Hết lượt' : 'Đang mở')) }}
<form method="post" action="{{ route('admin.coupons.settings',['id'=>$coupon->id]) }}">@csrf
<label>Hiển thị</label><select name="isPublic" class="form-control"><option value="1" @selected($coupon->isPublic)>Công khai</option><option value="0" @selected(!$coupon->isPublic)>Mã riêng</option></select>
<label>Lượt / tài khoản</label><input class="form-control" name="perUserLimit" type="number" value="1" readonly>
<button class="btn btn-sm btn-primary">Lưu điều kiện</button></form></td>
<td><form method="post" action="{{ route('admin.coupons.status',['id'=>$coupon->id]) }}">@csrf<input type="hidden" name="active" value="{{ $coupon->active ? 0 : 1 }}"><button class="btn btn-sm btn-default">{{ $coupon->active ? 'Tắt mã' : 'Bật mã' }}</button></form></td></tr>
@endforeach</tbody></table>{{ $coupons->links() }}</div>
</div></div></div>
@include('admin.blocks.chan-trang')
