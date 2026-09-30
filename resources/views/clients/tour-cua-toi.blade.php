@include('clients.blocks.dau-trang')
@include('clients.blocks.bieu-ngu')



<section class="booking-history py-100 rel z-1">
    <div class="container">
        @if (session('error'))<div class="alert alert-warning" role="alert">{{ session('error') }}</div>@endif
        <div class="booking-history__heading">
            <div>
                <span class="subtitle">Tài khoản của tôi</span>
                <h2>Lịch sử đặt tour</h2><a href="{{ route('private-tours.index') }}">Yêu cầu và báo giá tour riêng →</a>
                <p class="mb-0">Theo dõi các chuyến đi và trạng thái booking của bạn.</p>
            </div>
            <div class="booking-history__filters">
                <a class="{{ $period === 'all' ? 'active' : '' }}" href="{{ route('my-tours', ['status' => $status]) }}">Tất cả ({{ $tourCounts['all'] }})</a>
                <a class="{{ $period === 'upcoming' ? 'active' : '' }}" href="{{ route('my-tours', ['status' => $status, 'period' => 'upcoming']) }}">Chưa tham gia ({{ $tourCounts['upcoming'] }})</a>
                <a class="{{ $period === 'completed' ? 'active' : '' }}" href="{{ route('my-tours', ['status' => $status, 'period' => 'completed']) }}">Đã kết thúc ({{ $tourCounts['completed'] }})</a>
            </div>
        </div>

        <div class="booking-history__filters mb-25">
            @foreach (['all' => 'Mọi trạng thái', 'p' => 'Chờ thanh toán', 'b' => 'Chờ xác nhận', 'y' => 'Đã xác nhận', 'f' => 'Đã hoàn thành', 'c' => 'Đã hủy'] as $key => $label)
                <a class="{{ $status === $key ? 'active' : '' }}" href="{{ route('my-tours', ['status' => $key, 'period' => $period]) }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="booking-history__table-wrap">
            @if ($myTours->isEmpty())
                <div class="booking-history__empty"><i class="fal fa-suitcase-rolling fa-2x mb-15"></i><p class="mb-0">Chưa có booking phù hợp với bộ lọc.</p></div>
            @else
                <table class="booking-history__table table mb-0">
                    <thead><tr><th>Mã booking</th><th>Tour</th><th>Thời gian đặt</th><th>Thời gian đi</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($myTours as $tour)
                            @php
                                $statusMap = ['p' => ['Chờ thanh toán', 'pending'], 'b' => ['Chờ xác nhận', 'pending'], 'y' => ['Đã xác nhận', 'upcoming'], 'f' => ['Đã hoàn thành', 'done'], 'c' => ['Đã hủy', 'cancelled']];
                                $statusInfo = $statusMap[$tour->bookingStatus] ?? ['Đang xử lý', 'pending'];
                                $image = $tour->images->first() ?? 'default.jpg';
                            @endphp
                            <tr>
                                <td><strong>#{{ $tour->bookingId }}</strong></td>
                                <td><div class="booking-history__tour"><img src="{{ asset('admin/assets/images/gallery-tours/' . $image) }}" alt="{{ $tour->title }}"><div><strong>{{ $tour->title }}</strong><small>{{ $tour->destination }}</small></div></div></td>
                                <td>{{ $tour->bookingDate ? date('d/m/Y H:i', strtotime($tour->bookingDate)) : '—' }}</td>
                                <td>{{ $tour->startDate ? date('d/m/Y', strtotime($tour->startDate)) : 'Chưa cập nhật' }}<br><small>{{ $tour->endDate ? 'đến ' . date('d/m/Y', strtotime($tour->endDate)) : '' }}</small></td>
                                <td><span class="booking-status booking-status--{{ $statusInfo[1] }}">{{ $statusInfo[0] }}</span>
                                    <br><small>{{ $tour->paymentStatus === 'y' ? 'Đã thanh toán' : ($tour->transferReportedAt ? 'Chờ đối chiếu chuyển khoản' : 'Chưa thanh toán') }}</small>
                                    @if ($tour->cancelStatus === 'pending')<br><small>Đang chờ duyệt hủy</small>@endif
                                </td>
                                <td><a class="booking-history__detail" href="{{ route('tour-booked', ['bookingId' => $tour->bookingId, 'checkoutId' => $tour->checkoutId]) }}">Chi tiết <i class="fal fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</section>

@include('clients.blocks.chan-trang')
