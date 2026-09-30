(() => {
    const qr = document.getElementById('bank-payment-qr');
    if (!qr) return;
    const status = document.getElementById('bank-qr-status');
    const retry = document.getElementById('bank-qr-retry');
    const reference = document.getElementById('bank-transfer-reference');
    let lastUrl = '';
    let timer;
    function refresh(force = false) {
        if (!document.querySelector('input[name="payment"][value="bank-transfer"]:checked')) return;
        const amount = Math.round(Number(document.querySelector('input.totalPrice')?.value));
        if (!Number.isSafeInteger(amount) || amount <= 0) {
            qr.hidden = true;
            status.textContent = 'Vui lòng chọn số khách hợp lệ để tạo mã thanh toán.';
            return;
        }
        const tour = document.getElementById('tourId').value.replace(/\D/g, '');
        const phone = document.getElementById('tel').value.replace(/\D/g, '').slice(-11);
        const content = ('TRAVELA T' + tour + (phone ? ' ' + phone : '')).slice(0, 25);
        reference.textContent = content;
        const url = new URL(`https://img.vietqr.io/image/${encodeURIComponent(qr.dataset.bank)}-${encodeURIComponent(qr.dataset.account)}-compact2.png`);
        url.search = new URLSearchParams({ amount: String(amount), addInfo: content, accountName: qr.dataset.name }).toString();
        if (!force && lastUrl === url.href) return;
        lastUrl = url.href;
        qr.hidden = true;
        retry.hidden = true;
        status.textContent = 'Đang tải mã QR…';
        qr.onload = () => { qr.hidden = false; status.textContent = 'Quét mã bằng ứng dụng ngân hàng để chuyển khoản.'; };
        qr.onerror = () => { qr.hidden = true; status.textContent = 'Chưa tải được mã QR. Bạn có thể chuyển khoản bằng thông tin tài khoản bên cạnh hoặc thử lại.'; retry.hidden = false; };
        qr.src = url.href;
    }
    document.querySelectorAll('input[name="payment"]').forEach(input => input.addEventListener('change', () => refresh()));
    document.getElementById('tel').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => refresh(), 350); });
    new MutationObserver(() => refresh()).observe(document.querySelector('.transfer-amount'), { childList: true, characterData: true, subtree: true });
    retry.addEventListener('click', () => refresh(true));
    refresh();
})();
