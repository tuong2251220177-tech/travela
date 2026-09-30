@include('clients.blocks.dau-trang-giao-dien', ['pageClass' => 'order-page pastel-order'])
@php
    $bookingLabels = ['p' => 'Chờ thanh toán', 'b' => 'Chờ đối chiếu / xác nhận', 'y' => 'Đã xác nhận', 'f' => 'Đã hoàn thành', 'c' => 'Đã hủy'];
    $reference = 'TRAVELA B' . $bookingId;
    $remainingAmount = max(0, (int) $tour_booked->totalPrice - (int) ($tour_booked->amountPaid ?? 0));
    $paidAmount = (int) ($tour_booked->amountPaid ?? 0);
    $closed = in_array($tour_booked->bookingStatus, ['c', 'f']);
    $paymentLabel = $tour_booked->paymentStatus === 'y' ? 'Đã thanh toán đủ' : ($tour_booked->transferReportedAt ? 'Chờ nhân viên đối chiếu' : ($tour_booked->depositStatus === 'paid' ? 'Đã nhận cọc' : ($paidAmount > 0 ? 'Đã nhận một phần tiền' : 'Chờ thanh toán')));
    $depositRemaining = max(0, (int) ($tour_booked->depositAmount ?? 0) - $paidAmount);
    $payNow = ($tour_booked->paymentPlan ?? 'deposit') === 'full' || $depositRemaining === 0 ? $remainingAmount : min($remainingAmount, $depositRemaining);
    $qrUrl = 'https://img.vietqr.io/image/' . rawurlencode(config('bank_transfer.bank')) . '-' . rawurlencode(config('bank_transfer.account_number')) . '-compact2.png?' . http_build_query(['amount' => $payNow, 'addInfo' => $reference, 'accountName' => config('bank_transfer.account_name')]);
@endphp
<main id="main" class="shell order-main">
    <a href="{{ route('my-tours') }}">← Lịch sử đặt tour</a>
    <header class="order-heading"><div><span class="eyebrow">CHI TIẾT ĐƠN ĐẶT TOUR</span><h1>Đơn đặt tour #{{ $bookingId }}</h1><span class="order-badge">{{ $bookingLabels[$tour_booked->bookingStatus] ?? 'Đang xử lý' }}</span> <span class="order-badge payment-badge">{{ $paymentLabel }}</span></div><a class="button" href="{{ route('contact') }}">Liên hệ hỗ trợ ↗</a></header>
    @if(!$closed)
    <ol class="order-progress" aria-label="Tình trạng thanh toán"><li class="complete"><span>✓</span> Đã tạo đơn</li><li class="{{ $tour_booked->transferReportedAt || $paidAmount > 0 ? 'complete' : '' }}"><span>2</span> Gửi biên lai / nộp tiền</li><li class="{{ $tour_booked->depositStatus === 'paid' || $tour_booked->paymentStatus === 'y' ? 'complete' : '' }}"><span>3</span> Nhân viên ghi nhận cọc</li><li class="{{ $tour_booked->paymentStatus === 'y' ? 'complete' : '' }}"><span>4</span> Thanh toán đủ</li></ol>
    @endif
    @if (session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger" role="alert">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div> @endif
    <div class="order-layout">
        <div class="order-content">
            <section class="order-card order-tour">
                <div class="trip-card-heading"><span class="trip-group-tag">Đoàn {{ $tour_booked->groupNumber }}</span><span class="trip-reference">Mã đơn #{{ $bookingId }}</span></div>
                <h3>{{ $tour_booked->title }}</h3>
                <div class="trip-group-status">
                @if(!empty($tour_booked->privateRequestId))<p><strong>Đoàn riêng của nhóm bạn — không ghép khách khác.</strong> {{ $groupSummary->ready ? 'Đơn đã được xác nhận.' : 'Chờ hoàn tất đặt cọc và admin xác nhận.' }}</p>
                @else<p>{{ $groupSummary->ready ? "Đủ số khách tối thiểu" : "Chờ đủ khách khởi hành" }} · {{ $groupSummary->confirmed }}/{{ $groupSummary->minimum }} khách đã xác nhận (tối đa {{ $groupSummary->maximum }} khách/đoàn).</p>@endif
                </div>
                <dl class="trip-detail-grid">
                    <div><dt><i class="far fa-calendar-alt" aria-hidden="true"></i> Lịch khởi hành</dt><dd>{{ date('d/m/Y', strtotime($tour_booked->startDate)) }} — {{ date('d/m/Y', strtotime($tour_booked->endDate)) }}</dd></div>
                    <div><dt><i class="fas fa-users" aria-hidden="true"></i> Hành khách</dt><dd>{{ $tour_booked->numAdults }} người lớn · {{ $tour_booked->numChildren }} trẻ em</dd></div>
                    <div class="trip-detail-wide"><dt><i class="fas fa-bus" aria-hidden="true"></i> Phương tiện</dt><dd>{{ $tour_booked->transportName ?? 'Theo chương trình tour' }}<small>Phụ thu {{ number_format($tour_booked->transportFee ?? 0, 0, ',', '.') }} đ</small></dd></div>
                    <div><dt>Trạng thái đặt tour</dt><dd>{{ $bookingLabels[$tour_booked->bookingStatus] ?? 'Đang xử lý' }}</dd></div>
                    <div><dt>Trạng thái thanh toán</dt><dd>{{ $paymentLabel }}</dd></div>
                </dl>
                <div class="trip-payment-summary">
                <div class="trip-payment-heading"><strong>Thanh toán</strong><span>{{ ($tour_booked->paymentPlan ?? 'deposit') === 'full' ? 'Trả toàn bộ 100%' : 'Đặt cọc 20%' }}</span></div>
                @if ($tour_booked->paymentDate)<p>Ngày nhận tiền: {{ date('d/m/Y H:i', strtotime($tour_booked->paymentDate)) }}</p>@endif
                <dl class="trip-money-grid"><div><dt>Tổng giá trị đơn</dt><dd>{{ number_format($tour_booked->totalPrice, 0, ',', '.') }} <small>đ</small></dd></div><div><dt>Đã thanh toán</dt><dd>{{ number_format($paidAmount, 0, ',', '.') }} <small>đ</small></dd></div><div class="trip-money-remaining"><dt>Còn thiếu</dt><dd>{{ number_format($remainingAmount, 0, ',', '.') }} <small>đ</small></dd></div></dl>
                @if ($tour_booked->couponCode ?? null)<p>Mã {{ $tour_booked->couponCode }}: đã giảm {{ number_format($tour_booked->discountAmount, 0, ',', '.') }} đồng.</p>@endif
                @if ($tour_booked->depositDueAt && !$closed && $tour_booked->depositStatus !== 'paid' && $tour_booked->paymentStatus !== 'y')
                    <p class="trip-payment-deadline"><i class="far fa-clock" aria-hidden="true"></i> Hạn thanh toán / xác nhận cọc: <strong>{{ date('d/m/Y H:i', strtotime($tour_booked->depositDueAt)) }}</strong></p>
                @endif
                </div>
                @if ($tour_booked->depositStatus === 'expired')<div class="alert alert-warning">Đơn đã hết hạn thanh toán và được hủy. Không chuyển thêm tiền cho đơn này.</div>@endif
                <div class="trip-contact"><h4>Người liên hệ</h4>
                <dl class="trip-detail-grid"><div><dt>Họ và tên</dt><dd>{{ $tour_booked->fullName }}</dd></div><div><dt>Số điện thoại</dt><dd>{{ $tour_booked->phoneNumber }}</dd></div><div><dt>Email</dt><dd>{{ $tour_booked->email }}</dd></div><div><dt>Địa chỉ</dt><dd>{{ $tour_booked->address }}</dd></div></dl></div>
                <div class="trip-card-footer">
                @if(!empty($tour_booked->privateRequestId))<a href="{{ route('private-tours.show', $tour_booked->privateRequestId) }}">Xem báo giá và điều kiện tour riêng</a>
                @else<a href="{{ route('tour-detail', ['id' => $tour_booked->tourId]) }}">{{ $tour_booked->bookingStatus === 'f' ? 'Xem tour và viết đánh giá' : 'Xem chi tiết tour' }}</a>@endif
                <span aria-hidden="true">↗</span></div>
            </section>
            <section class="order-card"><h2>Danh sách hành khách</h2><div class="order-passengers">
                @forelse($passengers as $passenger)
                <div class="order-passenger"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><strong>{{ $passenger->fullName }}</strong><small>{{ $passenger->passengerType === 'adult' ? 'Người lớn' : 'Trẻ em' }} · {{ $passenger->phoneNumber }}</small></div></div>
                @empty<p>Đơn này chưa có danh sách hành khách chi tiết. Liên hệ Travela để được hỗ trợ.</p>@endforelse
            </div></section>
            <section class="order-card">
                <h4>Chính sách & yêu cầu hủy</h4>
                @if ($tour_booked->cancelStatus)
                    <p><strong>{{ ['pending' => 'Đang chờ duyệt', 'approved' => 'Đã chấp thuận hủy', 'rejected' => 'Chưa được chấp thuận'][$tour_booked->cancelStatus] ?? 'Đang xử lý' }}</strong></p>
                    <p>Lý do: {{ $tour_booked->cancelReason }}</p>
                    @if ($tour_booked->cancelNote)<p>Phản hồi của nhân viên: {{ $tour_booked->cancelNote }}</p>@endif
                @endif
                @if ($tour_booked->refundStatus === 'pending')<div class="alert alert-info">Nhân viên cần đối chiếu khoản đã chuyển và xử lý hoàn tiền theo chính sách. Việc hủy đơn không tự động hoàn tiền.</div>@endif
                @if ($tour_booked->refundStatus === 'resolved')<div class="alert alert-success">Đã hoàn tất đối soát. Xem kết quả trong phản hồi của nhân viên ở trên.</div>@endif
                @if ($hide === '')
                    <form action="{{ route('cancel-booking') }}" method="post">
                        @csrf
                        <input type="hidden" name="bookingId" value="{{ $bookingId }}">
                        <label for="cancel-reason">Lý do muốn hủy</label>
                        <textarea class="form-control mb-3" id="cancel-reason" name="reason" required minlength="5" maxlength="1000" rows="3">{{ old('reason') }}</textarea>
                        <p>Yêu cầu trực tuyến cần gửi trước ngày đi ít nhất 7 ngày. Chỗ chỉ được trả lại sau khi nhân viên duyệt.</p>
                        <button class="btn btn-outline-danger" type="submit">Gửi yêu cầu hủy</button>
                    </form>
                @elseif (!in_array($tour_booked->bookingStatus, ['c', 'f']) && $tour_booked->cancelStatus !== 'pending')
                    <p>Đã gần ngày khởi hành. Vui lòng <a href="{{ route('contact') }}">liên hệ nhân viên</a> nếu cần hỗ trợ hủy.</p>
                @endif
            </section>
        </div>
        <aside class="order-sidebar">
            <section class="order-card order-finance"><h2>Tổng quan thanh toán</h2>
                <dl><div><dt>Giá trước giảm</dt><dd>{{ number_format($tour_booked->totalPrice + ($tour_booked->discountAmount ?? 0), 0, ',', '.') }} ₫</dd></div>
                @if($tour_booked->couponCode ?? null)<div><dt>Mã {{ $tour_booked->couponCode }}</dt><dd>−{{ number_format($tour_booked->discountAmount, 0, ',', '.') }} ₫</dd></div>@endif
                <div class="finance-total"><dt>Tổng thanh toán</dt><dd>{{ number_format($tour_booked->totalPrice, 0, ',', '.') }} ₫</dd></div>
                <div><dt>Tiền cọc yêu cầu</dt><dd>{{ number_format($tour_booked->depositAmount ?? 0, 0, ',', '.') }} ₫</dd></div>
                <div><dt>Đã thu</dt><dd>{{ number_format($paidAmount, 0, ',', '.') }} ₫</dd></div>
                <div class="finance-remaining"><dt>Còn thiếu</dt><dd>{{ number_format($remainingAmount, 0, ',', '.') }} ₫</dd></div></dl>
                <p class="order-badge payment-badge">{{ $paymentLabel }}</p>
                @if($tour_booked->depositDueAt && !$closed && $tour_booked->depositStatus !== 'paid' && $tour_booked->paymentStatus !== 'y')<p class="order-deadline">Hạn xác nhận cọc: {{ date('d/m/Y H:i', strtotime($tour_booked->depositDueAt)) }}</p>@endif
            </section>
            <section class="order-card">
                <h4>Thanh toán</h4>
                @include('clients.blocks.minh-chung-chuyen-khoan')
                @if ($tour_booked->paymentStatus === 'y')
                    <div class="alert alert-success">Nhân viên đã xác nhận thanh toán.</div>
                @elseif (in_array($tour_booked->bookingStatus, ['c', 'f']))
                    <p>Đơn đã đóng. Không chuyển thêm tiền cho đơn này.</p>
                @elseif ($tour_booked->paymentMethod === 'bank-transfer')
                    <p>Kiểm tra người nhận, số tiền và nội dung trước khi chuyển khoản. Nếu đã chuyển, không quét thanh toán lại.</p>
                    <img src="{{ $qrUrl }}" alt="Mã VietQR cho đơn {{ $bookingId }}. Nếu ảnh không tải, dùng thông tin chuyển khoản bên dưới." width="300" height="380" style="max-width:100%;object-fit:contain;margin:auto">
                    <div class="transfer-details"><p><small>Ngân hàng thụ hưởng</small><strong>{{ config('bank_transfer.bank_name') }}</strong></p><p><small>Chủ tài khoản</small><strong>{{ config('bank_transfer.account_name') }}</strong></p>
                    <p><small>Số tài khoản</small><strong>{{ config('bank_transfer.account_number') }}</strong><button type="button" data-copy="{{ config('bank_transfer.account_number') }}" aria-label="Sao chép số tài khoản">Sao chép</button></p>
                    <p><small>Số tiền chuyển lần này</small><strong>{{ number_format($payNow, 0, ',', '.') }} ₫</strong><button type="button" data-copy="{{ $payNow }}" aria-label="Sao chép số tiền">Sao chép</button></p>
                    <p><small>Nội dung chuyển khoản</small><strong>{{ $reference }}</strong><button type="button" data-copy="{{ $reference }}" aria-label="Sao chép nội dung chuyển khoản">Sao chép</button></p></div><span id="copy-status" role="status"></span>
                        <form action="{{ route('report-transfer') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="bookingId" value="{{ $bookingId }}">
                            <label for="transfer-proof">Ảnh chụp màn hình chuyển khoản</label>
                            <input class="form-control mb-3" id="transfer-proof" type="file" name="proof" accept="image/jpeg,image/png,image/webp" required>
                            <small>JPG, PNG hoặc WebP, tối đa 5 MB.</small>
                            <button class="btn btn-primary mt-3" type="submit">Gửi ảnh chuyển khoản — báo nhân viên</button>
                        </form>
                    @if ($tour_booked->transferReportedAt)
                        <div class="alert alert-info">Đã báo chuyển khoản lúc {{ date('d/m/Y H:i', strtotime($tour_booked->transferReportedAt)) }}. Vui lòng chờ đối chiếu.</div>
                    @endif
                @else
                    <p>Số tiền thanh toán lần này: <strong>{{ number_format($payNow, 0, ',', '.') }} đồng</strong>. Vui lòng liên hệ nhân viên để hoàn tất thanh toán. Trạng thái sẽ cập nhật sau khi đối chiếu.</p>
                @endif
                <a class="mt-3" href="{{ route('tour-booked', ['bookingId' => $bookingId]) }}">Cập nhật trạng thái</a>
            </section>
        </aside>
    </div>
</main>
<script src="{{ asset('clients/assets/js/order-pastel.js?v=20260917') }}" defer></script>
@include('clients.blocks.cuoi-trang-giao-dien')
