document.addEventListener("DOMContentLoaded", () => {
    const button = document.getElementById("mobile-menu");
    const nav = document.getElementById("nav-links");
    if (!button || !nav) return;
    const close = () => {
        nav.classList.remove("active");
        button.setAttribute("aria-expanded", "false");
        button.setAttribute("aria-label", "Abrir menú");
    };
    button.addEventListener("click", () => {
        const open = nav.classList.toggle("active");
        button.setAttribute("aria-expanded", String(open));
        button.setAttribute("aria-label", open ? "Cerrar menú" : "Abrir menú");
    });
    nav.querySelectorAll("a").forEach(link => link.addEventListener("click", close));
    document.addEventListener("click", event => {
        if (!nav.contains(event.target) && !button.contains(event.target)) close();
    });
    document.addEventListener("keydown", event => {
        if (event.key === "Escape" && nav.classList.contains("active")) {
            close();
            button.focus();
        }
    });
    window.matchMedia("(min-width: 993px)").addEventListener("change", close);
});

document.addEventListener("DOMContentLoaded", () => {
    if (!("IntersectionObserver" in window)) return;

    const whatsapp = document.querySelector(".whatsapp-float");
    const actions = document.querySelector(".campaign-form-actions");
    if (whatsapp && actions) {
        const formObserver = new IntersectionObserver(([entry]) => {
            whatsapp.hidden = entry.isIntersecting;
        }, { rootMargin: "0px 0px 24px 0px" });
        formObserver.observe(actions);
    }

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const revealObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add("section-arrive");
            revealObserver.unobserve(entry.target);
        });
    });
    const home = document.querySelector(".home-page");
    // Reveal smaller groups on the home page so they also enter in sequence on narrow screens.
    const targets = home
        ? home.querySelectorAll(".section-heading, .solution-card, .service-row, .project-card, .led-photo, .led-copy, .process-grid article, .faq-list details, .team-grid article, #contacto > .container")
        : document.querySelectorAll("#contenido > section:not(#hero) > .container");
    targets.forEach(target => {
        if (target.getBoundingClientRect().top >= window.innerHeight) {
            revealObserver.observe(target);
        }
    });
});

// Play presentation videos while visible; respect a manual pause.
document.addEventListener("DOMContentLoaded", () => {
    if (!("IntersectionObserver" in window)) return;
    document.querySelectorAll("video[data-viewport-video]").forEach(video => {
        let visible = false;
        let manuallyPaused = false;
        let automaticPause = false;
        const pauseOutside = () => {
            if (video.paused) return;
            automaticPause = true;
            video.pause();
        };
        const update = () => {
            if (!visible || document.hidden) { pauseOutside(); return; }
            if (manuallyPaused || video.ended || !video.paused) return;
            const playback = video.play();
            if (playback) playback.then(() => {
                if (!visible || document.hidden) pauseOutside();
            }).catch(() => {});
        };
        video.addEventListener("pause", () => {
            if (!automaticPause && !video.ended) manuallyPaused = true;
            automaticPause = false;
        });
        video.addEventListener("play", () => {
            manuallyPaused = false;
            if (!visible || document.hidden) pauseOutside();
        });
        const observer = new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting && entry.intersectionRatio >= 0.5;
            update();
        }, { threshold: [0, 0.5] });
        observer.observe(video);
        document.addEventListener("visibilitychange", update);
    });
});

