@include('admin.blocks.dau-trang')
<link rel="stylesheet" href="{{ asset('admin/assets/css/phan-cong-huong-dan-vien.css?v=20260930') }}">
<div class="container body">
    <div class="main_container">
        @include('admin.blocks.thanh-ben')

        <div class="right_col guide-assignment-page" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
            <div class="page-title">
                <div class="title_left">
                    <h3>Phân công <small>Hướng dẫn viên</small></h3>
                </div>
            </div>

            <div class="clearfix"></div>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            <div class="guide-overview" aria-label="Tổng quan phân công">
                <div><span class="guide-stat-icon"><i class="fa fa-users" aria-hidden="true"></i></span><div><strong>{{ count($guides) }}</strong><span>Hướng dẫn viên</span></div></div>
                <div><span class="guide-stat-icon"><i class="fa fa-map-o" aria-hidden="true"></i></span><div><strong>{{ count($assignments) }}</strong><span>Lượt phân công</span></div></div>
                <div><span class="guide-stat-icon"><i class="fa fa-compass" aria-hidden="true"></i></span><div><strong>{{ collect($assignments)->where('tour_status', 'in_progress')->count() }}</strong><span>Đang diễn ra</span></div></div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Giao tour cho hướng dẫn viên</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <p class="guide-section-description">Chọn hướng dẫn viên và đoàn, sau đó bổ sung điểm tập trung và lịch khởi hành.</p>
                            <form action="{{ route('admin.guide-assignment.store') }}" method="POST" class="guide-assign-form">
                                @csrf
                                <div class="form-group">
                                    <label for="guide-select">Hướng dẫn viên</label>
                                    <input class="form-control mb-2" type="search" id="guide-search" placeholder="Tìm theo họ tên, tài khoản hoặc email" aria-label="Tìm hướng dẫn viên">
                                    <select class="form-control" name="guide_id" id="guide-select" required>
                                        <option value="">-- Chọn hướng dẫn viên --</option>
                                        @forelse ($guides as $guide)
                                            <option value="{{ $guide->guide_id }}" data-search="{{ $guide->full_name ?? '' }} {{ $guide->name }} {{ $guide->email }}">
                                                {{ $guide->full_name ?: $guide->name }} · {{ $guide->name }} (#{{ $guide->guide_id }})
                                            </option>
                                        @empty
                                            <option value="">Không có hướng dẫn viên</option>
                                        @endforelse
                                    </select>
                                    <small id="guide-search-status" role="status"></small>
                                    <a href="{{ route('admin.guides') }}">Quản lý tài khoản hướng dẫn viên ↗</a>
                                </div>

                                <div class="form-group">
                                    <label>Tour</label>
                                    <select class="form-control" name="tour_id" required>
                                        <option value="">-- Chọn tour --</option>
                                        @forelse ($tours as $tour)
                                            <option value="{{ $tour->id }}:{{ $tour->groupNumber }}">
                                                {{ $tour->title }} · Đoàn {{ $tour->groupNumber }} · {{ $tour->registered_guests }}/{{ $tour->maxGuests }} đăng ký · {{ $tour->confirmed_guests }}/{{ $tour->minGuests }} xác nhận {{ $tour->confirmed_guests >= $tour->minGuests ? "(Đủ Min)" : "(Chờ đủ khách)" }}
                                            </option>
                                        @empty
                                            <option value="">Không có tour</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Điểm tập trung</label>
                                    <input type="text" class="form-control" name="meeting_point" placeholder="Ví dụ: Bến xe Mỹ Đình">
                                </div>

                                <div class="form-group">
                                    <label>Ngày khởi hành</label>
                                    <input type="date" class="form-control" name="departure_date">
                                </div>

                                <div class="form-group">
                                    <label>Trạng thái tour</label>
                                    <select class="form-control" name="tour_status">
                                        <option value="assigned">Đã phân công</option>
                                        <option value="in_progress">Đang diễn ra</option>
                                        <option value="completed">Đã hoàn thành</option>
                                        <option value="cancelled">Hủy tour</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Ghi chú sự cố</label>
                                    <textarea class="form-control" name="incident_note" rows="3" placeholder="Ghi chú nếu có sự cố hoặc cảnh báo..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Phân công</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 guide-account-slot">
<details class="guide-account-details" id="them-huong-dan-vien" @if($errors->has('username') || $errors->has('password') || $errors->has('email') || $errors->has('avatar') || $errors->has('full_name')) open @endif>
<summary><span><i class="fa fa-user-plus" aria-hidden="true"></i> Thêm hướng dẫn viên mới</span><span class="guide-account-hint">Mở biểu mẫu <i class="fa fa-angle-down" aria-hidden="true"></i></span></summary>
<form action="{{ route('admin.add-guide') }}" method="POST" class="guide-account-form" enctype="multipart/form-data">
                                @csrf
                                <h4>Thêm hướng dẫn viên mới</h4>
                                <div class="form-group">
                                    <label>Tên đăng nhập</label>
                                    <input type="text" name="username" class="form-control" placeholder="Ví dụ: guide02" required>
                                </div>
                                <div class="form-group">
                                    <label>Họ và tên</label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Ví dụ: Tri Nguyen">
                                </div>
                                <div class="form-group">
                                    <label>Mật khẩu</label>
                                    <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="guide02@gmail.com">
                                </div>
                                <div class="form-group">
                                    <label>Avatar</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Ví dụ: Hướng dẫn viên du lịch chuyên về các tour miền núi..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Thêm hướng dẫn viên</button>
                            </form>
</details>
                </div>

                <div class="col-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Danh sách phân công hiện tại</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div class="guide-assignment-table-wrap"><table class="table table-bordered table-striped guide-assignment-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hướng dẫn viên</th>
                                        <th>Tour</th>
                                        <th>Điểm tập trung</th>
                                        <th>Ngày khởi hành</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($assignments as $index => $assignment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $assignment->guide_name ?? 'Chưa xác định' }}</td>
                                            <td>{{ $assignment->tour_title ?? 'Chưa có' }} · Đoàn {{ $assignment->groupNumber }}</td>
                                            <td>{{ $assignment->meeting_point ?? 'Chưa cập nhật' }}</td>
                                            <td>{{ $assignment->departure_date ? \Carbon\Carbon::parse($assignment->departure_date)->format('d/m/Y') : 'Chưa cập nhật' }}</td>
                                            <td>
                                                @switch($assignment->tour_status ?? 'assigned')
                                                    @case('completed')
                                                        <span class="badge badge-success">Đã hoàn thành</span>
                                                        @break
                                                    @case('in_progress')
                                                        <span class="badge badge-warning">Đang diễn ra</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-danger">Hủy tour</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-info">Đã phân công</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="guide-row-actions">
                                                            <form action="{{ route('admin.guide-assignment.send-manifest') }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="guide_id" value="{{ $assignment->guide_id }}">
                                                                <input type="hidden" name="tour_id" value="{{ $assignment->tour_id }}"><input type="hidden" name="groupNumber" value="{{ $assignment->groupNumber }}">
                                                                <input type="hidden" name="format" value="pdf">
                                                                <button type="submit" class="btn btn-sm btn-danger">Gửi PDF</button>
                                                            </form>
                                                            <form action="{{ route('admin.guide-assignment.send-manifest') }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="guide_id" value="{{ $assignment->guide_id }}">
                                                                <input type="hidden" name="tour_id" value="{{ $assignment->tour_id }}"><input type="hidden" name="groupNumber" value="{{ $assignment->groupNumber }}">
                                                                <input type="hidden" name="format" value="xls">
                                                                <button type="submit" class="btn btn-sm btn-success">Gửi Excel</button>
                                                            </form>
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editGuideModal{{ $index }}">Sửa</button>
                                                    <a class="btn btn-sm btn-default" href="{{ route('admin.guides', ['q' => $assignment->guide_name]) }}">Quản lý</a>
                                                </div>
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Chưa có tour nào được phân công.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table></div>
@foreach ($assignments as $index => $assignment)
<div class="modal fade" id="editGuideModal{{ $index }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.update-guide', ['id' => $assignment->guide_id]) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Sửa hướng dẫn viên</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Tên đăng nhập</label>
                                                                <input type="text" name="username" class="form-control" value="{{ $assignment->guide_name ?? '' }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Họ và tên</label>
                                                                <input type="text" name="full_name" class="form-control" value="{{ $assignment->guide_name ?? '' }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Email</label>
                                                                <input type="email" name="email" class="form-control" value="{{ optional(DB::table('tbl_admin')->where('adminId', $assignment->guide_id)->first())->email ?? '' }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Mô tả</label>
                                                                <textarea name="description" class="form-control" rows="3">{{ optional(DB::table('tbl_admin')->where('adminId', $assignment->guide_id)->first())->description ?? '' }}</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Avatar</label>
                                                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
@endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('admin/assets/js/quan-ly-huong-dan-vien.js?v=20260930') }}"></script>
@include('admin.blocks.chan-trang')
