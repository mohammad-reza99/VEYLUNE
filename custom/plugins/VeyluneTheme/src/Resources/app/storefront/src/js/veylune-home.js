const initVeyluneHome = () => {
    const home = document.querySelector('[data-veylune-home], [data-veylune-authority], [data-veylune-ecosystem], [data-veylune-legacy]');

    if (!home) {
        return;
    }

    if (home.dataset.veyluneHomeInitialized === 'true') {
        return;
    }

    home.dataset.veyluneHomeInitialized = 'true';

    const revealItems = [...home.querySelectorAll('[data-veylune-reveal]')];
    const parallaxItems = [...home.querySelectorAll('[data-veylune-parallax]')];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let ticking = false;

    if (reducedMotion) {
        home.classList.add('no-motion');
    }

    if ('IntersectionObserver' in window && !reducedMotion) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.18 });

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    const updateParallax = () => {
        if (reducedMotion) {
            ticking = false;
            return;
        }

        parallaxItems.forEach((item) => {
            const rect = item.getBoundingClientRect();
            const progress = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
            const clamped = Math.max(0, Math.min(progress, 1));
            const y = (clamped - 0.5) * 22;

            item.style.setProperty('--veylune-parallax-y', `${y}px`);
            item.style.transform = `translate3d(0, ${y}px, 0)`;
        });

        ticking = false;
    };

    const requestParallax = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(updateParallax);
    };

    updateParallax();
    window.addEventListener('scroll', requestParallax, { passive: true });
    window.addEventListener('resize', requestParallax, { passive: true });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneHome, { once: true });
} else {
    initVeyluneHome();
}
