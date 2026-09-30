<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard hướng dẫn viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('clients/assets/css/workspace-stitch.css') }}">
<link rel="stylesheet" href="{{ asset('clients/assets/css/readability.css?v=20260923') }}">
</head>
<body class="bg-light guide-theme">
@include('guide.blocks.dieu-huong')


    <div class="container py-4">
        <div class="guide-welcome"><span class="staff-kicker">HÀNH TRÌNH CỦA BẠN</span><h1>Xin chào, {{ $guideName }}!</h1><p>Theo dõi tour được giao, danh sách khách và cập nhật chuyến đi tại đây.</p></div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted">Tour được phân công</div>
                        <div class="h3 mb-0">{{ $tours->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted">Đang diễn ra</div>
                        <div class="h3 mb-0">{{ $tours->where('tour_status', 'in_progress')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted">Khách đã điểm danh</div>
                        <div class="h3 mb-0">{{ $tours->where('attendance_status', 'present')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted">Báo cáo</div>
                        <div class="h3 mb-0">{{ $tours->whereNotNull('summary_report')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Danh sách tour được phân công</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Tên tour</th>
                            <th>Địa điểm</th>
                            <th>Ngày khởi hành</th>
                            <th>Điểm tập trung</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tours as $index => $tour)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $tour->title ?? 'Tour chưa cập nhật' }} · Đoàn {{ $tour->groupNumber }}</td>
                                <td>{{ $tour->destination ?? 'Chưa cập nhật' }}</td>
                                <td>{{ $tour->departure_date ? \Carbon\Carbon::parse($tour->departure_date)->format('d/m/Y') : 'Chưa cập nhật' }}</td>
                                <td>{{ $tour->meeting_point ?? 'Chưa cập nhật' }}</td>
                                <td>
                                    @php $status = $tour->tour_status ?? 'assigned'; @endphp
                                    @if ($status === 'completed')
                                        <span class="badge badge-success">Đã hoàn thành</span>
                                    @elseif ($status === 'in_progress')
                                        <span class="badge badge-warning">Đang diễn ra</span>
                                    @elseif ($status === 'cancelled')
                                        <span class="badge badge-danger">Hủy</span>
                                    @else
                                        <span class="badge badge-info">Đã phân công</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('guide.tour-detail', ['groupNumber' => $tour->groupNumber, 'tourId' => $tour->tour_id ?? $tour->tourId]) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có tour nào được phân công cho hướng dẫn viên này.</td>
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
