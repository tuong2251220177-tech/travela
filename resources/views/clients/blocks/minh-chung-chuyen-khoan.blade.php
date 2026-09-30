@if (isset($proofs) && $proofs->isNotEmpty())
    <div class="my-3">
        <h5>Ảnh chuyển khoản đã gửi</h5>
        @foreach ($proofs as $proof)
            <a href="{{ route('transfer-proof', ['id' => $proof->id]) }}" target="_blank" rel="noopener" style="display:inline-block;margin:6px">
                <img src="{{ route('transfer-proof', ['id' => $proof->id]) }}" alt="Ảnh chuyển khoản gửi lúc {{ $proof->created_at }}" loading="lazy" style="width:120px;height:150px;object-fit:contain;border:1px solid #ddd;border-radius:8px">
                <small style="display:block">{{ $proof->created_at }}</small>
            </a>
        @endforeach
        <p>Ảnh hỗ trợ đối chiếu; thanh toán được cập nhật sau khi nhân viên kiểm tra giao dịch.</p>
    </div>
@endif
