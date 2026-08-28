const initVeyluneMarketplaceMotion = () => {
    const home = document.querySelector('[data-veylune-home]');
    const body = document.body;

    if (!home || body.dataset.veyluneMarketplaceMotionInitialized === 'true') {
        return;
    }

    body.dataset.veyluneMarketplaceMotionInitialized = 'true';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const media = [...home.querySelectorAll('img')];

    media.forEach((image) => {
        const markReady = () => image.classList.add('is-media-ready');

        if (image.complete) {
            markReady();
            return;
        }

        image.addEventListener('load', markReady, { once: true });
        image.addEventListener('error', markReady, { once: true });
    });

    home.querySelectorAll('[data-veylune-product-slider]').forEach((slider) => {
        let interactionTimer;

        const markInteraction = () => {
            slider.classList.add('is-motion-active');
            window.clearTimeout(interactionTimer);
            interactionTimer = window.setTimeout(() => {
                slider.classList.remove('is-motion-active');
            }, reducedMotion ? 0 : 420);
        };

        slider.querySelectorAll('[data-veylune-product-slider-previous], [data-veylune-product-slider-next]')
            .forEach((control) => control.addEventListener('click', markInteraction));

        slider.querySelector('[data-veylune-product-slider-track]')
            ?.addEventListener('scroll', markInteraction, { passive: true });
    });

    const newsletter = home.querySelector('.veylune-closing-capture__form');

    if (newsletter) {
        const button = newsletter.querySelector('button[type="submit"]');

        newsletter.addEventListener('submit', () => {
            if (!newsletter.checkValidity() || !button) {
                return;
            }

            newsletter.setAttribute('aria-busy', 'true');
            button.disabled = true;
            button.dataset.originalLabel = button.innerHTML;
            button.textContent = 'Joining...';
        });

        window.addEventListener('pageshow', () => {
            newsletter.removeAttribute('aria-busy');

            if (button) {
                button.disabled = false;

                if (button.dataset.originalLabel) {
                    button.innerHTML = button.dataset.originalLabel;
                }
            }
        });
    }

    window.requestAnimationFrame(() => body.classList.add('veylune-marketplace-motion-ready'));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneMarketplaceMotion, { once: true });
} else {
    initVeyluneMarketplaceMotion();
}
