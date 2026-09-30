(() => {
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.getElementById('mobile-nav');
    const closeMenu = () => { menu.hidden = true; toggle.setAttribute('aria-expanded', 'false'); };
    toggle?.addEventListener('click', () => { menu.hidden = !menu.hidden; toggle.setAttribute('aria-expanded', String(!menu.hidden)); });
    menu?.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && menu && !menu.hidden) { closeMenu(); toggle.focus(); } });

    const dialog = document.querySelector('.gallery-dialog');
    const images = [...document.querySelectorAll('.gallery-tile img')];
    let galleryIndex = 0;
    const showImage = index => {
        galleryIndex = (index + images.length) % images.length;
        const enlarged = document.getElementById('gallery-large');
        enlarged.src = images[galleryIndex].src;
        enlarged.alt = images[galleryIndex].alt;
        document.getElementById('gallery-position').textContent = `${galleryIndex + 1} / ${images.length}`;
    };
    document.querySelectorAll('[data-gallery-index]').forEach(button => button.addEventListener('click', () => {
        showImage(Number(button.dataset.galleryIndex));
        dialog.showModal();
    }));
    document.querySelector('.gallery-close')?.addEventListener('click', () => dialog.close());
    document.querySelector('.gallery-prev')?.addEventListener('click', () => showImage(galleryIndex - 1));
    document.querySelector('.gallery-next')?.addEventListener('click', () => showImage(galleryIndex + 1));
    dialog?.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') showImage(galleryIndex - 1);
        if (event.key === 'ArrowRight') showImage(galleryIndex + 1);
    });
    const expand = document.querySelector('.expand-itinerary');
    expand?.addEventListener('click', () => {
        const open = expand.getAttribute('aria-expanded') !== 'true';
        document.querySelectorAll('.itinerary-day').forEach(day => { day.open = open; });
        expand.setAttribute('aria-expanded', String(open));
        expand.textContent = open ? 'Thu gọn tất cả' : 'Mở tất cả';
    });
    document.querySelector('.share-tour')?.addEventListener('click', async () => {
        const status = document.getElementById('share-status');
        try {
            if (navigator.share) await navigator.share({ title: document.title, url: location.href });
            else { await navigator.clipboard.writeText(location.href); status.textContent = 'Đã sao chép liên kết tour.'; }
        } catch (error) {
            if (error.name !== 'AbortError') status.textContent = 'Bạn có thể sao chép địa chỉ trên thanh trình duyệt để chia sẻ.';
        }
    });
    const booking = document.getElementById('detail-booking');
    if (booking) {
        const adults = document.getElementById('detail-adults');
        const children = document.getElementById('detail-children');
        const seats = Number(booking.dataset.seats);
        const refresh = () => {
            const total = Number(adults.value) * Number(booking.dataset.adultPrice) + Number(children.value) * Number(booking.dataset.childPrice);
            document.getElementById('detail-total').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
            booking.querySelectorAll('[data-step]').forEach(button => {
                const input = document.getElementById(button.dataset.input);
                button.disabled = Number(button.dataset.step) > 0 ? Number(adults.value) + Number(children.value) >= seats : Number(input.value) <= Number(input.min);
            });
        };
        booking.querySelectorAll('[data-step]').forEach(button => button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.input);
            input.value = Number(input.value) + Number(button.dataset.step);
            refresh();
        }));
        refresh();
    }
    const reviewForm = document.getElementById('stitch-review-form');
    reviewForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const status = document.getElementById('review-status');
        const submit = reviewForm.querySelector('[type="submit"]');
        const message = reviewForm.elements.message.value.trim();
        if (!message) { status.textContent = 'Vui lòng nhập nội dung đánh giá.'; return; }
        submit.disabled = true;
        status.textContent = 'Đang gửi đánh giá…';
        const post = async (url, data) => {
            const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': window.travelaCsrfToken }, body: JSON.stringify(data) });
            if (!response.ok) throw new Error(response.status === 401 || response.status === 419 ? 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.' : 'Chưa thể gửi đánh giá. Vui lòng thử lại sau.');
            return response.json();
        };
        try {
            const tourId = reviewForm.dataset.tourId;
            const eligibility = await post(reviewForm.dataset.checkUrl, { tourId });
            if (!eligibility.success) throw new Error('Bạn cần hoàn thành chuyến đi trước khi đánh giá tour.');
            const response = await post(reviewForm.action, { tourId, rating: Number(reviewForm.elements.rating.value), message });
            if (!response.success) throw new Error('Chưa thể gửi đánh giá. Vui lòng thử lại.');
            document.getElementById('partials_reviews').innerHTML = response.data;
            status.textContent = response.message;
            reviewForm.querySelectorAll('fieldset, textarea, label, button').forEach(element => { element.hidden = true; });
        } catch (error) { status.textContent = error.message; submit.disabled = false; }
    });
})();
