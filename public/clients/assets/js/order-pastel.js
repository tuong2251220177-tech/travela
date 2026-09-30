document.querySelectorAll('[data-copy]').forEach(button => {
    button.addEventListener('click', async () => {
        const status = document.getElementById('copy-status');
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            status.textContent = 'Đã sao chép.';
        } catch (_) {
            status.textContent = 'Không thể sao chép tự động. Hãy chọn và sao chép thông tin phía trên.';
        }
    });
});
