@foreach ($list_booking as $booking)
    <tr>
        <td>{{ $booking->title }}<br><small>Đoàn {{ $booking->groupNumber }}</small></td>
        <td>{{ $booking->fullName }}</td>
        <td>{{ $booking->email }}</td>
        <td>{{ $booking->phoneNumber }}</td>
        <td>{{ $booking->address }}</td>
        <td>{{ date('d-m-Y', strtotime($booking->bookingDate)) }}</td>
        <td>{{ $booking->numAdults }}</td>
        <td>{{ $booking->numChildren }}</td>
        <td>{{ number_format($booking->totalPrice, 0, ',', '.') }}<br><small>Đã thu: {{ number_format($booking->amountPaid ?? 0, 0, ',', '.') }}<br>Còn thiếu: {{ number_format(max(0, $booking->totalPrice - ($booking->amountPaid ?? 0)), 0, ',', '.') }}</small></td>
        <td>
            {{ number_format($booking->depositAmount ?? 0, 0, ',', '.') }}<br>
            @if (($booking->depositStatus ?? '') === 'paid')
                <span class="badge badge-success">Đã cọc</span>
            @elseif (($booking->depositStatus ?? '') === 'expired')
                <span class="badge badge-danger">Hết hạn cọc</span>
            @else
                <span class="badge badge-warning">Chờ cọc</span>
            @endif
        </td>
        <td>
            @if ($booking->bookingStatus == 'c')
                <span class="badge badge-danger">Đã hủy</span>
            @elseif ($booking->bookingStatus == 'p')
                <span class="badge badge-warning">Chờ thanh toán</span>
            @elseif ($booking->bookingStatus == 'b')
                <span class="badge badge-warning">Chưa xác nhận</span>
            @elseif ($booking->bookingStatus == 'y')
                <span class="badge badge-primary">Đã xác nhận</span>
            @elseif ($booking->bookingStatus == 'f')
                <span class="badge badge-success">Đã hoàn thành</span>
            @endif
            @if ($booking->cancelStatus === 'pending')<br><span class="badge badge-danger">Có yêu cầu hủy</span>@endif
        </td>
        <td>
            @if ($booking->paymentMethod == 'momo-payment')
                <img src="{{ asset('admin/assets/images/icon/icon_momo.png') }}" class="icon_payment" alt="">
            @elseif ($booking->paymentMethod == 'paypal-payment')
                <img src="{{ asset('admin/assets/images/icon/icon_paypal.png') }}" class="icon_payment" alt="">
            @elseif ($booking->paymentMethod == 'bank-transfer')
                <span class="badge badge-warning">QR</span>
            @else
                <span>Tại văn phòng</span>
            @endif
        </td>

        <td>
            @if ($booking->paymentStatus == 'n')
                <span class="badge badge-warning">{{ ($booking->amountPaid ?? 0) > 0 ? 'Đã thu một phần' : 'Chưa thanh toán' }}</span>
                @if ($booking->transferReportedAt)<br><span class="badge badge-warning">Khách báo đã chuyển</span>@endif
            @else
                <span class="badge badge-success">Đã thanh toán</span>
            @endif
        </td>

        <td>
            <a class="btn btn-sm btn-primary" href="{{ route('admin.booking-detail', ['id' => $booking->bookingId]) }}">Chi tiết / thu tiền</a>
            <div class="btn-group">
                <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Thao tác khác với đơn đặt tour">
                </button>
                <div class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(71px, 38px, 0px);">
                    @if (in_array($booking->bookingStatus, ['p', 'b']) && ($booking->paymentStatus === 'y' || $booking->depositStatus === 'paid') && $booking->cancelStatus !== 'pending')
                    <a class="dropdown-item confirm-booking" href="javascript:void(0)" data-bookingId="{{ $booking->bookingId }}"
                        data-urlConfirm="{{ route('admin.confirm-booking') }}">Xác nhận</a>
                    @endif
                    @if ($booking->bookingStatus === 'y' && $booking->paymentStatus === 'y' && $booking->cancelStatus !== 'pending' && \Carbon\Carbon::parse($booking->endDate)->endOfDay()->lt(now()))
                    <a class="dropdown-item finish-booking" href="javascript:void(0)" data-bookingId="{{ $booking->bookingId }}"
                        data-urlfinish="{{ route('admin.finish-booking') }}">Đã hoàn thành</a>
                    @endif
                    <a class="dropdown-item" href="{{ route('admin.booking-detail',['id' => $booking->bookingId]) }}">Xem chi tiết</a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
