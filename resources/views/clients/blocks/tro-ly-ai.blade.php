<div class="travela-ai-chat" id="travela-ai-chat">
    <button class="travela-ai-chat__toggle" type="button" aria-label="Mở trợ lý Travela" aria-expanded="false">
        <i class="fas fa-comment-dots"></i>
    </button>
    <section class="travela-ai-chat__panel" aria-label="Trợ lý AI Travela" hidden>
        <header class="travela-ai-chat__header">
            <div>
                <strong>Trợ lý Travela</strong>
                <small>Hỗ trợ chọn tour và đặt chuyến</small>
            </div>
            <button class="travela-ai-chat__close" type="button" aria-label="Đóng chat">&times;</button>
        </header>
        <div class="travela-ai-chat__messages" aria-live="polite">
            <div class="travela-ai-chat__message travela-ai-chat__message--assistant">
                Xin chào! Tôi có thể hỗ trợ bạn tìm tour và giải đáp thông tin đặt chuyến.
            </div>
        </div>
        <form class="travela-ai-chat__form">
            <input type="text" maxlength="1000" placeholder="Bạn muốn hỏi gì?" aria-label="Tin nhắn" required>
            <button type="submit" aria-label="Gửi tin nhắn"><i class="fas fa-paper-plane"></i></button>
        </form>
    </section>
</div>
