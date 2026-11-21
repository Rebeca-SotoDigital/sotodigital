document.addEventListener('DOMContentLoaded', function () {
    const button = document.querySelector('[data-floating-whatsapp]');
    const hero = document.querySelector('.campaign-hero');
    const form = document.querySelector('[data-lead-form]');
    if (!button || !hero || !form || !window.IntersectionObserver) return;
    const mobile = window.matchMedia('(max-width: 800px)');
    function update() {
        const heroRect = hero.getBoundingClientRect();
        const formRect = form.getBoundingClientRect();
        const formVisible = formRect.top < window.innerHeight && formRect.bottom > 0;
        const visible = mobile.matches && heroRect.bottom <= 0 && !formVisible;
        // Do not unexpectedly remove a keyboard-focused contact link.
        button.hidden = !visible;
    }
    const observer = new window.IntersectionObserver(update, {threshold: 0});
    observer.observe(hero);
    observer.observe(form);
    if (mobile.addEventListener) mobile.addEventListener('change', update);
    window.addEventListener('resize', update, {passive: true});
    update();
});
