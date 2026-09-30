@include('admin.blocks.dau-trang')
<div class="container body"><div class="main_container">
@include('admin.blocks.thanh-ben')
<div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
<div class="x_panel"><div class="x_title"><h2>Tài khoản hướng dẫn viên <small>{{ $guides->total() }} kết quả</small></h2><div class="clearfix"></div></div>
<div class="x_content">
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<p>Khóa tài khoản để ngừng đăng nhập và phân công mới. Chỉ tài khoản chưa có lịch sử phân công mới được xóa.</p>
<form method="get" class="row" action="{{ route('admin.guides') }}">
<div class="col-md-5 form-group"><label for="guide-query">Tìm tài khoản</label><input id="guide-query" class="form-control" name="q" value="{{ $q }}" maxlength="100" placeholder="Họ tên, tên đăng nhập hoặc email"></div>
<div class="col-md-3 form-group"><label for="guide-status">Trạng thái</label><select id="guide-status" class="form-control" name="status"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hoạt động</option><option value="locked" @selected(request('status') === 'locked')>Đã khóa</option></select></div>
<div class="col-md-3 form-group"><label for="guide-history">Lịch sử</label><select id="guide-history" class="form-control" name="history"><option value="">Tất cả tài khoản</option><option value="unused" @selected(request('history') === 'unused')>Chưa được phân công</option></select></div>
<div class="col-md-1 form-group"><label>&nbsp;</label><button class="btn btn-primary" type="submit">Tìm</button></div>
</form>
<p><a class="btn btn-success" href="{{ route('admin.guide-assignment') }}#them-huong-dan-vien">Thêm hướng dẫn viên</a><a class="btn btn-default" href="{{ route('admin.guide-assignment') }}">Phân công tour</a><a href="{{ route('admin.guides') }}">Xóa bộ lọc</a></p>
<div class="table-responsive"><table class="table table-striped" style="min-width:850px"><thead><tr><th>Hướng dẫn viên</th><th>Tên đăng nhập / Email</th><th>Lượt phân công</th><th>Trạng thái</th><th>Quản lý</th></tr></thead><tbody>
@forelse($guides as $guide)
<tr>
<td><strong>{{ $guide->full_name ?: $guide->username }}</strong><br><small>#{{ $guide->adminId }}</small></td>
<td>{{ $guide->username }}<br><small>{{ $guide->email ?: 'Chưa có email' }}</small></td>
<td>{{ $guide->assignmentCount }}</td>
<td><span class="badge {{ $guide->guideEnabled ? 'badge-success' : 'badge-secondary' }}">{{ $guide->guideEnabled ? 'Hoạt động' : 'Đã khóa' }}</span></td>
<td>
<form class="d-inline" method="post" action="{{ route('admin.guides.status', $guide->adminId) }}">@csrf<input type="hidden" name="enabled" value="{{ $guide->guideEnabled ? 0 : 1 }}"><button class="btn btn-sm {{ $guide->guideEnabled ? 'btn-warning' : 'btn-success' }}" type="submit">{{ $guide->guideEnabled ? 'Khóa' : 'Mở khóa' }}</button></form>
@if(!$guide->assignmentCount && !$guide->hasGuestHistory)
<form class="d-inline" method="post" action="{{ route('admin.delete-guide', $guide->adminId) }}" onsubmit="return confirm('Xóa tài khoản này? Thao tác không thể hoàn tác.');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Xóa</button></form>
@else<small>Giữ lịch sử tour</small>@endif
<details style="margin-top:8px"><summary style="cursor:pointer">Sửa thông tin</summary>
<form method="post" action="{{ route('admin.update-guide', $guide->adminId) }}" style="padding:12px 0;min-width:220px">@csrf @method('PUT')
<label for="username-{{ $guide->adminId }}">Tên đăng nhập</label><input id="username-{{ $guide->adminId }}" class="form-control mb-2" name="username" value="{{ $guide->username }}" required>
<label for="name-{{ $guide->adminId }}">Họ và tên</label><input id="name-{{ $guide->adminId }}" class="form-control mb-2" name="full_name" value="{{ $guide->full_name }}">
<label for="email-{{ $guide->adminId }}">Email</label><input id="email-{{ $guide->adminId }}" class="form-control mb-2" type="email" name="email" value="{{ $guide->email }}">
<button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
</form></details>
</td></tr>
@empty<tr><td colspan="5" class="text-center">Không tìm thấy tài khoản phù hợp.</td></tr>@endforelse
</tbody></table></div>
{{ $guides->links('pagination::bootstrap-4') }}
</div></div></div></div></div>
@include('admin.blocks.chan-trang')
