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

// Motion enhances the page; content stays visible when it is unavailable.
document.addEventListener("DOMContentLoaded", () => {
    const motionPreference = window.matchMedia("(prefers-reduced-motion: reduce)");
    const stage = document.querySelector(".brand-stage");
    let revealObserver;
    let stageObserver;

    const clearMotion = () => {
        revealObserver?.disconnect();
        stageObserver?.disconnect();
        document.querySelectorAll(".reveal-pending").forEach(element => {
            element.classList.remove("reveal-pending");
        });
        stage?.classList.remove("motion-in-view");
    };

    const startMotion = () => {
        clearMotion();
        if (motionPreference.matches || !("IntersectionObserver" in window)) return;
        revealObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.remove("reveal-pending");
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.08 });

        const targets = document.querySelectorAll(
            ".section-heading, .solution-card, .led-photo, .led-copy, .service-row, " +
            ".service-note, .process-grid article, .faq-layout > div, .contact-layout > div"
        );
        targets.forEach(element => {
            element.classList.add("reveal-item");
            if (element.getBoundingClientRect().top >= window.innerHeight) {
                element.classList.add("reveal-pending");
                revealObserver.observe(element);
            }
        });
        document.querySelectorAll(".solutions-grid, .process-grid").forEach(group => {
            Array.from(group.children).forEach((element, index) => {
                element.style.setProperty("--reveal-delay", `${index * 75}ms`);
            });
        });
        if (stage) {
            stageObserver = new IntersectionObserver(entries => {
                stage.classList.toggle("motion-in-view", entries[0].isIntersecting);
            });
            stageObserver.observe(stage);
        }
    };

    // Keyboard navigation never has to wait for an entrance animation.
    document.addEventListener("focusin", event => {
        const target = event.target.closest(".reveal-pending");
        if (target) {
            target.classList.remove("reveal-pending");
            revealObserver?.unobserve(target);
        }
    });
    motionPreference.addEventListener("change", startMotion);
    startMotion();
});
