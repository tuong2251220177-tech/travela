<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết tour</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('clients/assets/css/workspace-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
</head>
<body class="bg-light guide-theme">
@include('guide.blocks.dieu-huong')
    <div class="container py-4">
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">{{ $tour->title }} · Đoàn {{ $tour->groupNumber }}</h3>
            <a href="{{ route('guide.dashboard') }}" class="btn btn-secondary">Quay lại</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Địa điểm:</strong> {{ $tour->destination }}</p>
                        <p><strong>Thời gian:</strong> {{ $tour->time }} ngày</p>
                        <p><strong>Điểm tập trung:</strong> {{ $tour->meeting_point ?? 'Chưa cập nhật' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ngày khởi hành:</strong> {{ $tour->departure_date ? \Carbon\Carbon::parse($tour->departure_date)->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                        <p><strong>Đã xác nhận:</strong> {{ $tour->registered_guests ?? 0 }} / {{ $tour->minGuests }} khách tối thiểu · Max {{ $tour->maxGuests }}</p>
                        <p>{{ $tour->registered_guests >= $tour->minGuests ? 'Đủ số khách tối thiểu' : 'Chờ đủ khách khởi hành' }}</p>
                        <p><strong>Trạng thái:</strong> {{ $tour->tour_status ?? 'assigned' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Cập nhật trạng thái tour</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('guide.update-tour-status', ['groupNumber' => $tour->groupNumber, 'tourId' => $tour->tour_id ?? $tour->tourId]) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label>Trạng thái tour</label>
                            <select name="tour_status" class="form-control">
                                <option value="assigned" {{ ($tour->tour_status ?? 'assigned') == 'assigned' ? 'selected' : '' }}>Đã phân công</option>
                                <option value="in_progress" {{ ($tour->tour_status ?? 'assigned') == 'in_progress' ? 'selected' : '' }}>Đang diễn ra</option>
                                <option value="completed" {{ ($tour->tour_status ?? 'assigned') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                <option value="cancelled" {{ ($tour->tour_status ?? 'assigned') == 'cancelled' ? 'selected' : '' }}>Hủy tour</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Điểm tập trung</label>
                            <input type="text" class="form-control" name="meeting_point" value="{{ $tour->meeting_point ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>Ngày khởi hành</label>
                            <input type="date" class="form-control" name="departure_date" value="{{ $tour->departure_date ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Lưu</button>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>Ghi chú sự cố</label>
                        <textarea class="form-control" name="incident_note" rows="3">{{ $tour->incident_note ?? '' }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Báo cáo sau chuyến đi</label>
                        <textarea class="form-control" name="summary_report" rows="4">{{ $tour->summary_report ?? '' }}</textarea>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách khách trong tour</h5>
                    <div>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('guide.manifest', ['groupNumber' => $tour->groupNumber, 'tourId' => $tour->tour_id ?? $tour->tourId, 'format' => 'pdf']) }}">Tải PDF</a>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('guide.manifest', ['groupNumber' => $tour->groupNumber, 'tourId' => $tour->tour_id ?? $tour->tourId, 'format' => 'xls']) }}">Tải Excel</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Họ tên</th>
                            <th>SĐT</th>
                            <th>Số người</th>
                            <th>Yêu cầu đặc biệt</th>
                            <th>Trạng thái</th>
                            <th>Điểm danh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guests as $guest)
                            <tr>
                                <td>{{ $guest->booking_name ?? $guest->user_name ?? 'Không rõ' }}</td>
                                <td>{{ $guest->phoneNumber ?? 'N/A' }}</td>
                                <td>{{ ($guest->numAdults ?? 0) + ($guest->numChildren ?? 0) }}</td>
                                <td>{{ $guest->guest_note ?? 'Không có' }}</td>
                                <td>
                                    @php $guestStatus = $guest->guest_status ?? 'confirmed'; @endphp
                                    @if ($guestStatus === 'present')
                                        <span class="badge badge-success">Đã có mặt</span>
                                    @elseif ($guestStatus === 'absent')
                                        <span class="badge badge-danger">Vắng mặt</span>
                                    @elseif ($guestStatus === 'completed')
                                        <span class="badge badge-primary">Đã hoàn thành</span>
                                    @else
                                        <span class="badge badge-warning">Đã xác nhận</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('guide.update-guest-status', ['groupNumber' => $tour->groupNumber, 'tourId' => $tour->tour_id ?? $tour->tourId]) }}">
                                        @csrf
                                        <input type="hidden" name="booking_id" value="{{ $guest->bookingId }}">
                                        <select name="guest_status" class="form-control form-control-sm mb-2">
                                            <option value="confirmed" {{ $guestStatus == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                            <option value="present" {{ $guestStatus == 'present' ? 'selected' : '' }}>Đã có mặt</option>
                                            <option value="absent" {{ $guestStatus == 'absent' ? 'selected' : '' }}>Vắng mặt</option>
                                            <option value="completed" {{ $guestStatus == 'completed' ? 'selected' : '' }}>Đã hoàn thành tour</option>
                                        </select>
                                        <input type="text" name="guest_note" class="form-control form-control-sm" placeholder="Yêu cầu đặc biệt" value="{{ $guest->guest_note ?? '' }}">
                                        <button type="submit" class="btn btn-sm btn-success mt-2">Cập nhật</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có khách đặt trong tour này.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.12.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
