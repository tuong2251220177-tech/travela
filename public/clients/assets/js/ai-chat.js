(() => {
    const chat = document.getElementById('travela-ai-chat');
    if (!chat) return;
    const toggle = chat.querySelector('.travela-ai-chat__toggle');
    const close = chat.querySelector('.travela-ai-chat__close');
    const panel = chat.querySelector('.travela-ai-chat__panel');
    const messages = chat.querySelector('.travela-ai-chat__messages');
    const form = chat.querySelector('form');
    const input = form.querySelector('input');
    const submit = form.querySelector('button');
    const subtitle = chat.querySelector('.travela-ai-chat__header small');
    const history = [];
    let busy = false;
    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) input.focus(); else toggle.focus();
    }
    function addMessage(text, role) {
        const item = document.createElement('div');
        item.className = 'travela-ai-chat__message travela-ai-chat__message--' + role;
        item.textContent = text;
        messages.append(item);
        messages.scrollTop = messages.scrollHeight;
        return item;
    }
    toggle.addEventListener('click', () => setOpen(panel.hidden));
    close.addEventListener('click', () => setOpen(false));
    chat.addEventListener('keydown', event => { if (event.key === 'Escape') setOpen(false); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || busy) return;
        busy = true;
        input.disabled = submit.disabled = true;
        form.setAttribute('aria-busy', 'true');
        input.value = '';
        addMessage(message, 'user');
        const pending = addMessage('Đang tìm thông tin cho bạn…', 'assistant');
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 65000);
        try {
            const response = await fetch(window.travelaAiChatUrl || '/ai-chat', {
                method: 'POST', signal: controller.signal,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': window.travelaCsrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ message, history: history.slice(-6) }),
            });
            if (response.status === 419) throw new Error('Phiên làm việc đã hết hạn. Vui lòng tải lại trang rồi gửi lại tin nhắn.');
            if (response.status === 429) throw new Error('Bạn gửi tin nhắn quá nhanh. Vui lòng chờ một chút rồi thử lại.');
            const data = await response.json();
            if (!response.ok || !data.success || !data.message) throw new Error('Chưa nhận được câu trả lời. Vui lòng thử gửi lại.');
            pending.textContent = data.message;
            subtitle.textContent = data.mode === 'fallback' ? 'Hỗ trợ tự động • Thông tin từ Travela' : (data.mode === 'guide' ? 'Travela • Thông tin tour và thanh toán' : 'Trợ lý AI • Hỗ trợ chọn tour và đặt chuyến');
            history.push({ role: 'user', content: message }, { role: 'assistant', content: data.message.slice(0, 2000) });
            if (history.length > 6) history.splice(0, history.length - 6);
        } catch (error) {
            pending.textContent = error.name === 'AbortError' ? 'Trợ lý phản hồi quá lâu. Vui lòng thử gửi lại.' : (error instanceof TypeError ? 'Mất kết nối. Kiểm tra mạng rồi gửi lại tin nhắn.' : error.message);
            input.value = message;
        } finally {
            clearTimeout(timer);
            busy = false;
            input.disabled = submit.disabled = false;
            form.setAttribute('aria-busy', 'false');
            messages.scrollTop = messages.scrollHeight;
            if (!panel.hidden) input.focus();
        }
    });
})();
