@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
<div class="well">
    <h4>Đối chiếu đơn #{{ $invoice_booking->bookingId }}</h4>
    @if ($invoice_booking->couponCode ?? null)<p>Mã giảm giá: <strong>{{ $invoice_booking->couponCode }}</strong> · Đã giảm {{ number_format($invoice_booking->discountAmount, 0, ',', '.') }} đ</p>@endif
    @include('clients.blocks.minh-chung-chuyen-khoan')
    @php
        $paidAmount = (int) ($invoice_booking->amountPaid ?? 0);
        $remainingAmount = max(0, (int) $invoice_booking->totalPrice - $paidAmount);
        $depositRemaining = max(0, (int) $invoice_booking->depositAmount - $paidAmount);
    @endphp
    <p>Đã thu: <strong>{{ number_format($paidAmount, 0, ',', '.') }} đ</strong> · Còn thiếu: <strong>{{ number_format($remainingAmount, 0, ',', '.') }} đ</strong></p>
    @if ($remainingAmount > 0 && !in_array($invoice_booking->bookingStatus, ['c', 'f']) && $invoice_booking->cancelStatus !== 'pending')
        <form id="payment-receipt-form" method="post" action="{{ route('admin.received') }}">
            @csrf
            <input type="hidden" name="bookingId" value="{{ $invoice_booking->bookingId }}">
            <input type="hidden" name="receiptToken" value="{{ old('receiptToken', (string) \Illuminate\Support\Str::uuid()) }}">
            <h4>Ghi nhận thanh toán</h4>
            <p>Cọc cần thu thêm: {{ number_format($depositRemaining, 0, ',', '.') }} đ. Nhập số tiền thực tế vừa nhận, không nhập tổng đã thu.</p>
            <label>Số tiền vừa nhận (đồng)</label>
            <input class="form-control" type="number" name="amount" min="1" max="{{ $remainingAmount }}" step="1" value="{{ old('amount', $depositRemaining ?: $remainingAmount) }}" required>
            <label>Phương thức thực tế</label>
            <select class="form-control" name="method" required>
                <option value="office-payment" @selected(old('method', $invoice_booking->paymentMethod) === 'office-payment')>Tại văn phòng</option>
                <option value="bank-transfer" @selected(old('method', $invoice_booking->paymentMethod) === 'bank-transfer')>Chuyển khoản</option>
            </select>
            <label>Mã giao dịch / số phiếu thu (nếu có)</label>
            <input class="form-control" name="reference" maxlength="255" value="{{ old('reference') }}">
            <label>Ghi chú khoản thu</label>
            <textarea class="form-control" name="note" minlength="3" maxlength="1000" required>{{ old('note') }}</textarea>
            <label><input type="checkbox" name="verified" value="1" required> Tôi đã đối chiếu và thực tế nhận khoản tiền trên.</label>
            <button class="btn btn-primary" type="submit">Lưu khoản thu</button>
        </form>
    @endif
    <h4>Lịch sử thu tiền</h4>
    <div class="table-responsive"><table class="table table-bordered">
        <thead><tr><th>Thời gian ghi nhận</th><th>Số tiền</th><th>Phương thức</th><th>Nhân viên</th><th>Mã đối chiếu</th><th>Ghi chú</th></tr></thead>
        <tbody>@forelse (($receipts ?? collect()) as $receipt)
            <tr><td>{{ $receipt->created_at }}</td><td>{{ number_format($receipt->amount, 0, ',', '.') }} đ</td><td>{{ $receipt->method === 'bank-transfer' ? 'Chuyển khoản' : 'Tại văn phòng' }}</td><td>{{ $receipt->staff }}</td><td>{{ $receipt->reference ?: '—' }}</td><td>{{ $receipt->note }}</td></tr>
        @empty <tr><td colspan="6">Chưa có phiếu thu chi tiết. Khoản đã thanh toán trước khi nâng cấp được giữ trong tổng đã thu.</td></tr> @endforelse</tbody>
    </table></div>
    <p>Thanh toán: <strong>{{ $invoice_booking->paymentStatus === 'y' ? 'Đã nhận đủ tiền' : ($paidAmount > 0 ? 'Đã thu một phần' : 'Chưa nhận tiền') }}</strong></p>
    @if ($invoice_booking->paymentMethod === 'bank-transfer')
        <p>Nội dung chuyển khoản của đơn: <strong>TRAVELA B{{ $invoice_booking->bookingId }}</strong></p>
    @endif
    @if ($invoice_booking->transferReportedAt)
        <p>Khách báo đã chuyển khoản: {{ date('d/m/Y H:i', strtotime($invoice_booking->transferReportedAt)) }}. Kiểm tra giao dịch ngân hàng trước khi xác nhận.</p>
    @endif
    @if ($invoice_booking->cancelStatus)
        <h4>Yêu cầu hủy: {{ ['pending' => 'Chờ duyệt', 'approved' => 'Đã chấp thuận', 'rejected' => 'Đã từ chối'][$invoice_booking->cancelStatus] ?? 'Đang xử lý' }}</h4>
        <p>Lý do của khách: {{ $invoice_booking->cancelReason }}</p>
        @if ($invoice_booking->cancelNote)<p>Phản hồi đã gửi: {{ $invoice_booking->cancelNote }}</p>@endif
        @if ($invoice_booking->cancelStatus === 'pending' && !in_array($invoice_booking->bookingStatus, ['c', 'f']))
            <form method="post" action="{{ route('admin.review-cancellation') }}">
                @csrf
                <input type="hidden" name="bookingId" value="{{ $invoice_booking->bookingId }}">
                <label for="cancel-note">Phản hồi cho khách (và hướng dẫn hoàn tiền nếu có)</label>
                <textarea name="note" id="cancel-note" class="form-control" required minlength="5" maxlength="1000" rows="3">{{ old('note') }}</textarea>
                <button type="submit" name="decision" value="approve" class="btn btn-danger mt-3">Chấp thuận hủy và trả chỗ</button>
                <button type="submit" name="decision" value="reject" class="btn btn-default mt-3">Từ chối yêu cầu</button>
            </form>
        @endif
    @endif
    @if ($invoice_booking->refundStatus === 'pending')
        <div class="alert alert-warning">Cần đối chiếu / xử lý hoàn tiền bên ngoài hệ thống. Việc duyệt hủy không thực hiện chuyển tiền tự động.</div>
        <form method="post" action="{{ route('admin.resolve-refund') }}">
            @csrf
            <input type="hidden" name="bookingId" value="{{ $invoice_booking->bookingId }}">
            <label for="refund-note">Kết quả đã xử lý (số tiền / mã giao dịch nếu đã hoàn tiền)</label>
            <textarea id="refund-note" name="note" class="form-control" rows="3" required minlength="5" maxlength="1000"></textarea>
            <button class="btn btn-primary mt-3" type="submit">Ghi nhận đã hoàn tất đối soát</button>
        </form>
    @elseif ($invoice_booking->refundStatus === 'resolved')
        <p>Đã hoàn tất đối soát. Xem kết quả trong phản hồi phía trên.</p>
    @endif
</div>
