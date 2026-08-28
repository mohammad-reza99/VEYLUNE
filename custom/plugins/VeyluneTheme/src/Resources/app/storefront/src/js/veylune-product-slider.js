const initVeyluneProductSliders = () => {
    document.querySelectorAll('[data-veylune-product-slider]').forEach((slider) => {
        if (slider.dataset.veyluneProductSliderInitialized === 'true') {
            return;
        }

        const track = slider.querySelector('[data-veylune-product-slider-track]');
        const previous = slider.querySelector('[data-veylune-product-slider-previous]');
        const next = slider.querySelector('[data-veylune-product-slider-next]');

        if (!track || !previous || !next) {
            return;
        }

        slider.dataset.veyluneProductSliderInitialized = 'true';

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const getStep = () => {
            const firstCard = track.firstElementChild;

            if (!(firstCard instanceof HTMLElement)) {
                return track.clientWidth;
            }

            const styles = window.getComputedStyle(track);
            const gap = Number.parseFloat(styles.columnGap || styles.gap) || 0;

            return firstCard.getBoundingClientRect().width + gap;
        };

        const updateControls = () => {
            const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
            const atStart = track.scrollLeft <= 2;
            const atEnd = track.scrollLeft >= maxScroll - 2;
            const hasOverflow = maxScroll > 2;

            previous.disabled = !hasOverflow || atStart;
            next.disabled = !hasOverflow || atEnd;
            slider.classList.toggle('is-scrollable', hasOverflow);
            slider.classList.toggle('is-at-start', atStart);
            slider.classList.toggle('is-at-end', atEnd);
        };

        const move = (direction) => {
            track.scrollBy({
                left: getStep() * direction,
                behavior: reducedMotion ? 'auto' : 'smooth',
            });
        };

        previous.addEventListener('click', () => move(-1));
        next.addEventListener('click', () => move(1));
        track.addEventListener('scroll', updateControls, { passive: true });
        track.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                move(-1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                move(1);
            }
        });

        if ('ResizeObserver' in window) {
            new ResizeObserver(updateControls).observe(track);
        } else {
            window.addEventListener('resize', updateControls, { passive: true });
        }

        updateControls();
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneProductSliders, { once: true });
} else {
    initVeyluneProductSliders();
}
