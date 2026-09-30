(() => {
    const search = document.getElementById('guide-search');
    const select = document.getElementById('guide-select');
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g, 'd').toLowerCase();
    if (search && select) search.addEventListener('input', () => {
        const term = normalize(search.value.trim());
        let count = 0;
        for (const option of select.options) {
            if (!option.value) continue;
            const matches = normalize(option.dataset.search || option.textContent).includes(term);
            option.hidden = !matches && !option.selected;
            if (matches) count++;
        }
        document.getElementById('guide-search-status').textContent = `${count} hướng dẫn viên phù hợp. `;
    });
    if (location.hash === '#them-huong-dan-vien') {
        const form = document.getElementById('them-huong-dan-vien');
        if (form) { form.open = true; form.scrollIntoView({ block: 'start' }); }
    }
})();
