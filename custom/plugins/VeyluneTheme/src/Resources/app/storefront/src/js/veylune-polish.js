const initVeylunePolish = () => {
    const body = document.body;
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (body.dataset.veylunePolishInitialized === 'true') {
        return;
    }

    body.dataset.veylunePolishInitialized = 'true';

    body.classList.add('veylune-polish-ready');

    const finishLoading = () => {
        body.classList.add('veylune-is-loaded');
    };

    window.requestAnimationFrame(finishLoading);

    if (!reducedMotion && window.matchMedia('(pointer: fine)').matches) {
        const imageTargets = document.querySelectorAll([
            '.veylune-home-hero__visual',
            '.veylune-collection-card',
            '.veylune-piece-card__media',
            '.veylune-product-card__media',
            '.veylune-authority-hero__visual',
            '.veylune-journal-card__media',
            '.veylune-atmosphere-engine__visual',
            '.veylune-ecosystem-hero__field',
            '.veylune-legacy-hero__ambient',
            '.veylune-future-framework__panel'
        ].join(','));

        imageTargets.forEach((element) => {
            element.classList.add('veylune-sensory-frame');

            if (!element.querySelector(':scope > .veylune-sensory-frame__light')) {
                const light = document.createElement('span');
                light.className = 'veylune-sensory-frame__light';
                light.setAttribute('aria-hidden', 'true');
                element.appendChild(light);
            }

            element.addEventListener('pointermove', (event) => {
                const rect = element.getBoundingClientRect();
                const x = ((event.clientX - rect.left) / rect.width) * 100;
                const y = ((event.clientY - rect.top) / rect.height) * 100;

                element.style.setProperty('--veylune-light-x', `${x}%`);
                element.style.setProperty('--veylune-light-y', `${y}%`);
            }, { passive: true });
        });
    }

    let ticking = false;
    let lastScrollY = window.scrollY;

    const updateAtmosphere = () => {
        const currentScrollY = window.scrollY;
        const maxScroll = Math.max(root.scrollHeight - window.innerHeight, 1);
        const progress = Math.min(currentScrollY / maxScroll, 1);
        const velocity = Math.max(-1, Math.min((currentScrollY - lastScrollY) / 40, 1));

        root.style.setProperty('--veylune-scroll-progress', progress.toFixed(4));
        root.style.setProperty('--veylune-scroll-velocity', velocity.toFixed(3));
        body.classList.toggle('veylune-scroll-deep', currentScrollY > window.innerHeight * 0.72);

        lastScrollY = currentScrollY;
        ticking = false;
    };

    const requestAtmosphere = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(updateAtmosphere);
    };

    updateAtmosphere();
    window.addEventListener('scroll', requestAtmosphere, { passive: true });
    window.addEventListener('resize', requestAtmosphere, { passive: true });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeylunePolish, { once: true });
} else {
    initVeylunePolish();
}
