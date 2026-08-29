const initVeyluneHeader = () => {
    const header = document.querySelector('[data-veylune-header]');

    if (!header) {
        return;
    }

    if (header.dataset.veyluneHeaderInitialized === 'true') {
        return;
    }

    header.dataset.veyluneHeaderInitialized = 'true';

    const body = document.body;
    const searchToggle = header.querySelector('[data-veylune-search-toggle]');
    const searchClose = header.querySelector('[data-veylune-search-close]');
    const searchInput = header.querySelector('[data-veylune-search-input]');
    const searchPanel = header.querySelector('[data-veylune-search-panel]');
    const searchBackdrop = header.querySelector('.veylune-search-archive__backdrop');
    const searchResults = header.querySelector('[data-veylune-search-results]');
    const progress = header.querySelector('[data-veylune-header-progress]');
    const mega = header.querySelector('[data-veylune-mega]');
    const megaTriggers = [...header.querySelectorAll('[data-veylune-mega-trigger]')];
    const megaPanels = [...header.querySelectorAll('[data-veylune-mega-panel]')];
    const megaClose = header.querySelector('[data-veylune-mega-close]');
    const mobileToggle = header.querySelector('[data-veylune-mobile-toggle]');
    const mobileNav = header.querySelector('[data-veylune-mobile-nav]');
    const mobileClose = header.querySelector('[data-veylune-mobile-close]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let ticking = false;
    let lastScrollY = window.scrollY;
    let megaCloseTimer = null;
    let activeMegaKey = null;
    let activeMegaTrigger = null;
    let searchReturnFocus = null;
    let mobileReturnFocus = null;

    const focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'summary',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(',');

    const getFocusableElements = (container) => {
        if (!container) {
            return [];
        }

        return [...container.querySelectorAll(focusableSelector)].filter((element) => {
            if (!(element instanceof HTMLElement)) {
                return false;
            }

            return !element.hasAttribute('inert') && element.offsetParent !== null;
        });
    };

    const trapFocus = (event, container) => {
        if (event.key !== 'Tab') {
            return;
        }

        const focusableElements = getFocusableElements(container);

        if (!focusableElements.length) {
            event.preventDefault();
            return;
        }

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    };

    const setInertState = (element, isInert) => {
        if (!element) {
            return;
        }

        if (isInert) {
            element.setAttribute('inert', '');
            return;
        }

        element.removeAttribute('inert');
    };

    setInertState(searchPanel, true);
    setInertState(mega, true);
    setInertState(mobileNav, true);
    megaPanels.forEach((panel) => setInertState(panel, true));

    const setOverlayBodyState = () => {
        const hasOverlay = header.classList.contains('is-search-open') || header.classList.contains('is-mobile-nav-open');
        body.classList.toggle('veylune-overlay-active', hasOverlay);
    };

    const setSearchState = (isOpen, { restoreFocus = false } = {}) => {
        header.classList.toggle('is-search-open', isOpen);
        searchToggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        searchPanel?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        setInertState(searchPanel, !isOpen);

        if (!isOpen) {
            searchResults?.classList.remove('is-active');

            if (restoreFocus && searchReturnFocus instanceof HTMLElement) {
                searchReturnFocus.focus();
            }

            searchReturnFocus = null;
        }

        setOverlayBodyState();
    };

    const closeSearch = (options = {}) => setSearchState(false, options);

    const openSearch = () => {
        searchReturnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : searchToggle;
        closeMega();
        closeMobileNav();
        setSearchState(true);

        if (searchInput && !reducedMotion) {
            window.setTimeout(() => searchInput.focus(), 220);
            return;
        }

        searchInput?.focus();
    };

    function openMega(key) {
        if (!mega || window.matchMedia('(max-width: 991px)').matches) {
            return;
        }

        window.clearTimeout(megaCloseTimer);
        closeSearch();

        header.classList.add('is-mega-open');
        mega.classList.add('is-open');
        mega.setAttribute('aria-hidden', 'false');
        setInertState(mega, false);
        activeMegaKey = key;
        activeMegaTrigger = megaTriggers.find((trigger) => trigger.dataset.veyluneMegaTrigger === key) || null;

        megaTriggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', trigger.dataset.veyluneMegaTrigger === key ? 'true' : 'false');
        });

        megaPanels.forEach((panel) => {
            const isActive = panel.dataset.veyluneMegaPanel === key;
            panel.classList.toggle('is-active', isActive);
            panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            setInertState(panel, !isActive);
        });
    }

    function closeMega({ restoreFocus = false } = {}) {
        if (!mega) {
            return;
        }

        header.classList.remove('is-mega-open');
        mega.classList.remove('is-open');
        mega.setAttribute('aria-hidden', 'true');
        setInertState(mega, true);
        activeMegaKey = null;

        megaTriggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', 'false');
        });

        megaPanels.forEach((panel) => {
            panel.classList.remove('is-active');
            panel.setAttribute('aria-hidden', 'true');
            setInertState(panel, true);
        });

        if (restoreFocus && activeMegaTrigger instanceof HTMLElement) {
            activeMegaTrigger.focus();
        }

        activeMegaTrigger = null;
    }

    const scheduleMegaClose = () => {
        window.clearTimeout(megaCloseTimer);
        megaCloseTimer = window.setTimeout(closeMega, 180);
    };

    const openMobileNav = () => {
        mobileReturnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : mobileToggle;
        closeSearch();
        closeMega();
        header.classList.add('is-mobile-nav-open');
        mobileNav?.classList.add('is-open');
        mobileNav?.setAttribute('aria-hidden', 'false');
        setInertState(mobileNav, false);
        mobileToggle?.setAttribute('aria-expanded', 'true');
        setOverlayBodyState();

        const firstFocusable = getFocusableElements(mobileNav)[0];

        if (firstFocusable && !reducedMotion) {
            window.setTimeout(() => firstFocusable.focus(), 180);
            return;
        }

        firstFocusable?.focus();
    };

    function closeMobileNav({ restoreFocus = false } = {}) {
        header.classList.remove('is-mobile-nav-open');
        mobileNav?.classList.remove('is-open');
        mobileNav?.setAttribute('aria-hidden', 'true');
        setInertState(mobileNav, true);
        mobileToggle?.setAttribute('aria-expanded', 'false');
        setOverlayBodyState();

        if (restoreFocus && mobileReturnFocus instanceof HTMLElement) {
            mobileReturnFocus.focus();
        }

        mobileReturnFocus = null;
    }

    const updateProgress = () => {
        if (!progress) {
            return;
        }

        const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
        const amount = documentHeight > 0 ? Math.min(window.scrollY / documentHeight, 1) : 0;

        progress.style.transform = `scaleX(${amount})`;
    };

    const updateHeaderState = () => {
        const currentScrollY = window.scrollY;
        const isScrolled = currentScrollY > 24;
        const isScrollingDown = currentScrollY > lastScrollY + 4;
        const isScrollingUp = currentScrollY < lastScrollY - 4;

        header.classList.toggle('is-initial', !isScrolled);
        header.classList.toggle('is-sticky', isScrolled);
        header.classList.toggle('is-scrolled', isScrolled);
        header.classList.toggle('is-header-hidden-text', isScrolled);
        header.classList.toggle('is-scrolling-down', isScrollingDown && isScrolled);
        header.classList.toggle('is-scrolling-up', isScrollingUp);
        header.dataset.veyluneHeaderState = isScrolled ? 'sticky' : 'initial';
        header.dataset.veyluneScrollDirection = isScrollingDown ? 'down' : (isScrollingUp ? 'up' : 'none');

        if (isScrollingDown && header.classList.contains('is-search-open')) {
            closeSearch();
        }

        if (isScrollingDown && header.classList.contains('is-mega-open')) {
            closeMega();
        }

        updateProgress();
        lastScrollY = currentScrollY;
        ticking = false;
    };

    const requestHeaderState = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(updateHeaderState);
    };

    updateHeaderState();

    window.addEventListener('scroll', requestHeaderState, { passive: true });
    window.addEventListener('resize', requestHeaderState, { passive: true });

    searchToggle?.addEventListener('click', openSearch);
    searchClose?.addEventListener('click', () => {
        closeSearch({ restoreFocus: true });
    });
    searchBackdrop?.addEventListener('click', () => closeSearch({ restoreFocus: true }));
    searchPanel?.addEventListener('keydown', (event) => trapFocus(event, searchPanel));
    searchInput?.addEventListener('input', () => {
        searchResults?.classList.toggle('is-active', searchInput.value.trim().length > 0);
    });

    megaTriggers.forEach((trigger) => {
        const key = trigger.dataset.veyluneMegaTrigger;

        trigger.addEventListener('mouseenter', () => openMega(key));
        trigger.addEventListener('focus', () => openMega(key));
        trigger.addEventListener('mouseleave', scheduleMegaClose);
    });

    mega?.addEventListener('mouseenter', () => window.clearTimeout(megaCloseTimer));
    mega?.addEventListener('mouseleave', scheduleMegaClose);
    mega?.addEventListener('focusout', (event) => {
        const nextTarget = event.relatedTarget;

        window.setTimeout(() => {
            if (
                nextTarget instanceof Node &&
                (mega.contains(nextTarget) || megaTriggers.some((trigger) => trigger.contains(nextTarget)))
            ) {
                return;
            }

            if (!mega.contains(document.activeElement) && !megaTriggers.some((trigger) => trigger.contains(document.activeElement))) {
                closeMega();
            }
        }, 0);
    });
    header.addEventListener('focusout', (event) => {
        if (!activeMegaKey) {
            return;
        }

        const nextTarget = event.relatedTarget;

        window.setTimeout(() => {
            if (
                nextTarget instanceof Node &&
                (mega?.contains(nextTarget) || megaTriggers.some((trigger) => trigger.contains(nextTarget)))
            ) {
                return;
            }

            if (!mega?.contains(document.activeElement) && !megaTriggers.some((trigger) => trigger.contains(document.activeElement))) {
                closeMega();
            }
        }, 0);
    });
    megaClose?.addEventListener('click', closeMega);

    mobileToggle?.addEventListener('click', openMobileNav);
    mobileClose?.addEventListener('click', () => {
        closeMobileNav({ restoreFocus: true });
    });
    mobileNav?.addEventListener('keydown', (event) => trapFocus(event, mobileNav));
    mobileNav?.addEventListener('click', (event) => {
        if (event.target.closest('a[href]')) closeMobileNav();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        if (header.classList.contains('is-search-open')) {
            event.preventDefault();
            closeSearch({ restoreFocus: true });
        }

        if (header.classList.contains('is-mega-open')) {
            event.preventDefault();
            closeMega({ restoreFocus: true });
        }

        if (header.classList.contains('is-mobile-nav-open')) {
            event.preventDefault();
            closeMobileNav({ restoreFocus: true });
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Node)) {
            return;
        }

        if (header.classList.contains('is-search-open') && !searchPanel?.contains(target) && !searchToggle?.contains(target)) {
            closeSearch({ restoreFocus: true });
        }
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 992px)').matches && header.classList.contains('is-mobile-nav-open')) {
            closeMobileNav({ restoreFocus: true });
        }
    }, { passive: true });
};

const initVeyluneAtmosphere = () => {
    if (document.body.dataset.veyluneAtmosphereInitialized === 'true') {
        return;
    }

    document.body.dataset.veyluneAtmosphereInitialized = 'true';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let transition = document.querySelector('.veylune-page-transition');

    if (!transition) {
        transition = document.createElement('div');
        transition.className = 'veylune-page-transition';
        document.body.appendChild(transition);
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (
            !link ||
            !link.matches('[data-veylune-page-transition]') ||
            event.defaultPrevented ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey ||
            link.matches('[data-cart-widget], [data-ajax-modal], [data-off-canvas-cart]')
        ) {
            return;
        }

        const url = new URL(link.href, window.location.href);

        if (url.origin !== window.location.origin || link.target === '_blank' || link.hasAttribute('download') || url.hash && url.pathname === window.location.pathname) {
            return;
        }

        event.preventDefault();
        transition.classList.add('is-active');
        window.setTimeout(() => {
            window.location.href = link.href;
        }, reducedMotion ? 0 : 240);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initVeyluneHeader();
        initVeyluneAtmosphere();
    }, { once: true });
} else {
    initVeyluneHeader();
    initVeyluneAtmosphere();
}
