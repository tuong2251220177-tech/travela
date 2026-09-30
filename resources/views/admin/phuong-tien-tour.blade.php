@include('admin.blocks.dau-trang')
<div class="container body"><div class="main_container">
@include('admin.blocks.thanh-ben')
<div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
<div class="x_panel"><div class="x_title"><h2>Phương tiện · {{ $tour->title }}</h2><div class="clearfix"></div></div>
<div class="x_content">
<a href="{{ route('admin.tours') }}">← Danh sách tour</a>
@if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
@if($errors->any())<p class="alert alert-danger">{{ $errors->first() }}</p>@endif
<p>Phương tiện tiêu chuẩn đã gồm trong giá tour. Thêm lựa chọn nâng cấp và phụ thu tại đây. Phụ thu theo khách áp dụng cho cả người lớn và trẻ em; phụ thu theo đơn chỉ cộng một lần. Mã giảm giá áp dụng trên giá tour, không giảm phần phụ thu phương tiện.</p>
<p>Các khách chọn cùng loại phương tiện được xếp cùng loại đoàn. Quy mô tối thiểu/tối đa vẫn theo cấu hình tour; chỉ mở phương tiện phù hợp với quy mô này.</p>
@foreach($options as $option)
<form class="row" method="post" action="{{ route('admin.transport.update', [$tour->tourId, $option->id]) }}">
@csrf
<div class="col-md-3 form-group"><label>Phương tiện</label><p><strong>{{ $option->name }}</strong></p></div>
<div class="col-md-3 form-group"><label for="fee-{{ $option->id }}">Phụ thu (đ)</label><input id="fee-{{ $option->id }}" class="form-control" type="number" name="surcharge" min="0" max="100000000" required value="{{ $option->surcharge }}"></div>
<div class="col-md-2 form-group"><label for="unit-{{ $option->id }}">Cách tính</label><select id="unit-{{ $option->id }}" class="form-control" name="chargeUnit"><option value="person" @selected($option->chargeUnit === 'person')>Mỗi khách</option><option value="booking" @selected($option->chargeUnit === 'booking')>Cả đơn</option></select></div>
<div class="col-md-2 form-group"><label for="active-{{ $option->id }}">Trạng thái</label><select id="active-{{ $option->id }}" class="form-control" name="active"><option value="1" @selected($option->active)>Đang mở</option><option value="0" @selected(!$option->active)>Đã tắt</option></select></div>
<div class="col-md-2 form-group"><button class="btn btn-primary" type="submit">Lưu</button></div>
</form>
@endforeach
<hr><h3>Thêm phương tiện</h3>
<form method="post" action="{{ route('admin.transport.store', $tour->tourId) }}">@csrf
<div class="form-group"><label for="transport-name">Tên phương tiện</label><input id="transport-name" class="form-control" name="name" maxlength="100" required value="{{ old('name') }}" placeholder="Ví dụ: Xe limousine"></div>
<div class="form-group"><label for="transport-fee">Phụ thu (đ)</label><input id="transport-fee" class="form-control" type="number" name="surcharge" min="0" max="100000000" required value="{{ old('surcharge') }}"></div>
<div class="form-group"><label for="transport-unit">Cách tính</label><select id="transport-unit" class="form-control" name="chargeUnit"><option value="person">Mỗi khách (người lớn và trẻ em)</option><option value="booking">Cả đơn đặt tour</option></select></div>
<button type="submit" class="btn btn-success">Thêm phương tiện</button>
</form></div></div></div></div></div>
@include('admin.blocks.chan-trang')
