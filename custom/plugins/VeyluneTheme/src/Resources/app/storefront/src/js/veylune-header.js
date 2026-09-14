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
    const megaBackdrop = mega?.querySelector('.veylune-mega__backdrop');
    const megaSurface = mega?.querySelector('.veylune-mega__panel');
    const megaTriggers = [...header.querySelectorAll('[data-veylune-mega-trigger]')];
    const megaPanels = [...header.querySelectorAll('[data-veylune-mega-panel]')];
    const megaClose = header.querySelector('[data-veylune-mega-close]');
    const mobileToggle = header.querySelector('[data-veylune-mobile-toggle]');
    const mobileNav = header.querySelector('[data-veylune-mobile-nav]');
    const mobileClose = header.querySelector('[data-veylune-mobile-close]');
    const marketplaceSearchInput = header.querySelector('[data-vli-header-search-input]');
    const marketplaceSearchPanel = header.querySelector('[data-vli-header-suggest]');
    const marketplaceSearchForm = marketplaceSearchInput?.closest('form');
    const marketplaceQueryLink = marketplaceSearchPanel?.querySelector('[data-vli-header-query-link]');
    const marketplaceQueryLabel = marketplaceSearchPanel?.querySelector('[data-vli-header-query-label]');
    const marketplaceSuggestions = [...(marketplaceSearchPanel?.querySelectorAll('[data-vli-header-suggestion]') || [])];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let ticking = false;
    let lastScrollY = window.scrollY;
    let megaOpenTimer = null;
    let megaCloseTimer = null;
    let activeMegaKey = null;
    let activeMegaTrigger = null;
    let isRestoringMegaFocus = false;
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

    const closeMarketplaceSuggestions = ({ retainFocus = false } = {}) => {
        if (!marketplaceSearchInput || !marketplaceSearchPanel) {
            return;
        }

        marketplaceSearchPanel.hidden = true;
        marketplaceSearchInput.setAttribute('aria-expanded', 'false');

        if (retainFocus) {
            marketplaceSearchInput.focus({ preventScroll: true });
        }
    };

    const updateMarketplaceSuggestions = () => {
        if (!marketplaceSearchInput || !marketplaceSearchPanel || !marketplaceSearchForm) {
            return;
        }

        const query = marketplaceSearchInput.value.trim();
        const normalizedQuery = query.toLocaleLowerCase();
        const action = new URL(marketplaceSearchForm.action, window.location.origin);

        marketplaceSearchPanel.hidden = false;
        marketplaceSearchInput.setAttribute('aria-expanded', 'true');

        if (marketplaceQueryLink && marketplaceQueryLabel) {
            action.searchParams.set('q', query);
            marketplaceQueryLink.href = `${action.pathname}${action.search}`;
            marketplaceQueryLink.hidden = query.length === 0;
            marketplaceQueryLabel.textContent = query;
        }

        marketplaceSuggestions.forEach((suggestion) => {
            const terms = String(suggestion.dataset.searchTerms || suggestion.textContent).toLocaleLowerCase();
            suggestion.hidden = normalizedQuery.length > 0 && !terms.includes(normalizedQuery);
        });
    };

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

    const syncMegaOffset = () => {
        if (!mega) return;

        const headerBottom = Math.max(0, Math.ceil(header.getBoundingClientRect().bottom));
        mega.style.setProperty('--veylune-mega-offset', `${headerBottom}px`);
    };

    function openMega(key) {
        if (!mega || window.matchMedia('(max-width: 991px)').matches) {
            return;
        }

        const requestedPanel = megaPanels.find((panel) => panel.dataset.veyluneMegaPanel === key);
        if (!requestedPanel) {
            return;
        }

        window.clearTimeout(megaCloseTimer);
        closeSearch();
        syncMegaOffset();

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

        window.clearTimeout(megaOpenTimer);
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

        const returnFocus = activeMegaTrigger;
        activeMegaTrigger = null;

        if (restoreFocus && returnFocus instanceof HTMLElement) {
            isRestoringMegaFocus = true;
            returnFocus.focus({ preventScroll: true });
            window.setTimeout(() => {
                isRestoringMegaFocus = false;
            }, 0);
        }
    }

    const scheduleMegaClose = () => {
        window.clearTimeout(megaOpenTimer);
        window.clearTimeout(megaCloseTimer);
        megaCloseTimer = window.setTimeout(closeMega, 180);
    };

    const scheduleMegaOpen = (key) => {
        window.clearTimeout(megaOpenTimer);
        window.clearTimeout(megaCloseTimer);
        megaOpenTimer = window.setTimeout(() => openMega(key), 120);
    };

    const focusMegaTrigger = (currentTrigger, direction) => {
        const currentIndex = megaTriggers.indexOf(currentTrigger);
        if (currentIndex < 0 || megaTriggers.length === 0) {
            return;
        }

        let nextIndex = currentIndex;
        if (direction === 'first') nextIndex = 0;
        if (direction === 'last') nextIndex = megaTriggers.length - 1;
        if (direction === 'next') nextIndex = (currentIndex + 1) % megaTriggers.length;
        if (direction === 'previous') nextIndex = (currentIndex - 1 + megaTriggers.length) % megaTriggers.length;
        megaTriggers[nextIndex]?.focus({ preventScroll: true });
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

    marketplaceSearchInput?.addEventListener('focus', updateMarketplaceSuggestions);
    marketplaceSearchInput?.addEventListener('input', updateMarketplaceSuggestions);
    marketplaceSearchInput?.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        event.preventDefault();
        closeMarketplaceSuggestions({ retainFocus: true });
    });

    megaTriggers.forEach((trigger) => {
        const key = trigger.dataset.veyluneMegaTrigger;

        // A short intent delay keeps a normal pointer click on the navigation
        // link from being intercepted by the full-width mega overlay.
        trigger.addEventListener('mouseenter', () => scheduleMegaOpen(key));
        trigger.addEventListener('focus', () => {
            if (!isRestoringMegaFocus) openMega(key);
        });
        trigger.addEventListener('mouseleave', scheduleMegaClose);
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                openMega(key);
                const activePanel = megaPanels.find((panel) => panel.dataset.veyluneMegaPanel === key);
                window.requestAnimationFrame(() => getFocusableElements(activePanel)[0]?.focus({ preventScroll: true }));
                return;
            }

            const directions = {
                ArrowRight: 'next',
                ArrowLeft: 'previous',
                Home: 'first',
                End: 'last',
            };
            const direction = directions[event.key];
            if (direction) {
                event.preventDefault();
                focusMegaTrigger(trigger, direction);
            }
        });
    });

    megaSurface?.addEventListener('mouseenter', () => window.clearTimeout(megaCloseTimer));
    megaSurface?.addEventListener('mouseleave', scheduleMegaClose);
    megaBackdrop?.addEventListener('click', () => closeMega({ restoreFocus: true }));
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
    megaClose?.addEventListener('click', () => closeMega({ restoreFocus: true }));

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

    document.addEventListener('pointerdown', (event) => {
        const target = event.target;

        if (
            target instanceof Node &&
            header.classList.contains('is-mega-open') &&
            !megaSurface?.contains(target) &&
            !megaTriggers.some((trigger) => trigger.contains(target))
        ) {
            closeMega({ restoreFocus: true });
        }

        if (
            target instanceof Node &&
            marketplaceSearchPanel &&
            marketplaceSearchForm &&
            !marketplaceSearchPanel.contains(target) &&
            !marketplaceSearchForm.contains(target)
        ) {
            closeMarketplaceSuggestions();
        }
    });

    window.addEventListener('pageshow', () => closeMarketplaceSuggestions());

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
