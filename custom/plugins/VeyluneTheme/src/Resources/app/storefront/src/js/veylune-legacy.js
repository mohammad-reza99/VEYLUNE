const initVeyluneLegacy = () => {
    const body = document.body;
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const legacySurfaces = document.querySelectorAll('[data-veylune-legacy], [data-veylune-home], [data-veylune-authority], [data-veylune-ecosystem], .veylune-product-detail, .veylune-collection-page');

    if (!legacySurfaces.length) {
        return;
    }

    if (body.dataset.veyluneLegacyInitialized === 'true') {
        return;
    }

    body.dataset.veyluneLegacyInitialized = 'true';

    body.classList.add('veylune-legacy-ready');

    if (!document.querySelector('.veylune-legacy-atmosphere')) {
        const atmosphere = document.createElement('div');
        atmosphere.className = 'veylune-legacy-atmosphere';
        atmosphere.setAttribute('aria-hidden', 'true');
        atmosphere.innerHTML = '<span></span><span></span><span></span>';
        body.prepend(atmosphere);
    }

    const localHour = new Date().getHours();
    const tone = localHour < 11 ? 'morning' : localHour < 18 ? 'gallery' : 'evening';
    body.setAttribute('data-veylune-time-tone', tone);

    const identityTargets = document.querySelectorAll([
        '.veylune-home-hero__title',
        '.veylune-ecosystem-hero h1',
        '.veylune-authority-hero h1',
        '.veylune-legacy-hero h1',
        '.veylune-product-hero h1',
        '.veylune-collection-intro h1'
    ].join(','));

    identityTargets.forEach((target, index) => {
        target.classList.add('veylune-signature-title');
        target.style.setProperty('--veylune-title-order', index);
    });

    const signatureTargets = document.querySelectorAll([
        '.veylune-world-card',
        '.veylune-home-ecosystem__grid a',
        '.veylune-private-access article',
        '.veylune-room-universe__timeline a',
        '.veylune-legacy-card',
        '.veylune-legacy-index__item',
        '.veylune-cultural-story',
        '.veylune-product-card',
        '.veylune-collection-card'
    ].join(','));

    signatureTargets.forEach((target, index) => {
        target.classList.add('veylune-signature-surface');
        target.style.setProperty('--veylune-surface-order', index % 8);
    });

    const updateContinuity = () => {
        const scrollMax = Math.max(root.scrollHeight - window.innerHeight, 1);
        const progress = Math.min(window.scrollY / scrollMax, 1);
        const legacyDepth = Math.round(progress * 1000) / 1000;

        root.style.setProperty('--veylune-legacy-depth', legacyDepth);
        body.toggleAttribute('data-veylune-deep-memory', progress > 0.55);
    };

    updateContinuity();
    window.addEventListener('scroll', () => window.requestAnimationFrame(updateContinuity), { passive: true });
    window.addEventListener('resize', updateContinuity, { passive: true });

    if (!reducedMotion && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-legacy-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.22, rootMargin: '0px 0px -8% 0px' });

        signatureTargets.forEach((target) => observer.observe(target));
    } else {
        signatureTargets.forEach((target) => target.classList.add('is-legacy-visible'));
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneLegacy, { once: true });
} else {
    initVeyluneLegacy();
}
