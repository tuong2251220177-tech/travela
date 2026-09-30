(() => {
    const toggle = document.getElementById('menu_toggle');
    if (toggle) {
        const update = () => toggle.setAttribute('aria-expanded', String(document.body.classList.contains('nav-md')));
        toggle.setAttribute('aria-controls', 'sidebar-menu');
        toggle.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggle.click();
            }
        });
        new MutationObserver(update).observe(document.body, { attributes: true, attributeFilter: ['class'] });
        update();
    }
})();
