(() => {
    document.querySelectorAll('.region-tabs button').forEach(button => {
        button.addEventListener('click', () => {
            const region = button.dataset.region;
            document.querySelectorAll('.region-tabs button').forEach(tab => tab.setAttribute('aria-pressed', String(tab === button)));
            let visible = 0;
            document.querySelectorAll('.pastel-tour-item').forEach(card => {
                card.hidden = region !== 'all' && card.dataset.domain !== region;
                if (!card.hidden) visible++;
            });
            document.getElementById('region-empty').hidden = visible > 0;
        });
    });
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.getElementById('mobile-nav');
    toggle.addEventListener('click', () => {
        menu.hidden = !menu.hidden;
        toggle.setAttribute('aria-expanded', String(!menu.hidden));
    });
    menu.addEventListener('click', event => {
        if (event.target.closest('a')) {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !menu.hidden) {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });
    const from = document.getElementById('departure-from');
    const to = document.getElementById('departure-to');
    from.addEventListener('change', () => {
        to.min = from.value;
        if (to.value && to.value < from.value) to.value = from.value;
    });
    document.getElementById('home-search').addEventListener('submit', () => {
        const format = value => value ? value.split('-').reverse().join('/') : '';
        document.getElementById('start-date-value').value = format(from.value);
        document.getElementById('end-date-value').value = format(to.value);
    });
})();
