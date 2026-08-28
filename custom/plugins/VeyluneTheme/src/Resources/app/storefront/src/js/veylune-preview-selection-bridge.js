(() => {
    if (!window.location.pathname.startsWith('/__veylune-preview/')) return;

    const storageKey = 'veylune-private-selection-v1';
    const maxAge = 30 * 24 * 60 * 60 * 1000;
    let headerCart = document.querySelector('.veylune-header__bag');
    let headerAccount = document.querySelector('.veylune-header__actions a[href*="/account"]');
    let count = headerCart?.querySelector('.veylune-marketplace-action__count');
    const token = new URL(window.location.href).searchParams.get('token');

    const activatePendingLink = (element, href, label, dataAttribute) => {
        if (!element || !token) return element;
        const link = document.createElement('a');
        link.className = element.className.replace(/\bis-pending\b/g, '').trim();
        link.innerHTML = element.innerHTML;
        link.href = href;
        link.setAttribute('aria-label', label);
        link.setAttribute(dataAttribute, '');
        element.replaceWith(link);
        return link;
    };

    if (token) {
        document.querySelectorAll('[data-veylune-account-pending]').forEach((element, index) => {
            const link = activatePendingLink(
                element,
                `/__veylune-preview/account?token=${encodeURIComponent(token)}`,
                'Private account preview',
                'data-veylune-preview-account'
            );
            if (index === 0) headerAccount = link;
        });
        document.querySelectorAll('[data-veylune-cart-pending]').forEach((element, index) => {
            const link = activatePendingLink(
                element,
                `/__veylune-preview/cart?token=${encodeURIComponent(token)}`,
                'Private selection, 0 items',
                'data-veylune-preview-cart'
            );
            if (index === 0) headerCart = link;
        });
    }

    if (headerCart && token) {
        headerCart.href = `/__veylune-preview/cart?token=${encodeURIComponent(token)}`;
        headerCart.dataset.veylunePreviewCart = '';
    }
    count = headerCart?.querySelector('.veylune-marketplace-action__count');
    if (headerAccount && token) {
        headerAccount.href = `/__veylune-preview/account?token=${encodeURIComponent(token)}`;
        headerAccount.setAttribute('aria-label', 'Private account preview');
        headerAccount.dataset.veylunePreviewAccount = '';
    }
    if (token) {
        document.querySelectorAll('a[href$="/account"]:not([data-veylune-native-account])').forEach((link) => {
            link.href = `/__veylune-preview/account?token=${encodeURIComponent(token)}`;
            link.setAttribute('aria-label', 'Private account preview');
            link.dataset.veylunePreviewAccount = '';
        });
    }

    const readSelection = () => {
        try {
            const stored = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
            const valid = stored?.productId
                && stored?.productName
                && Number(stored.unitPrice) > 0
                && Number.isFinite(Number(stored.quantity));
            const expired = Number(stored?.updatedAt) > 0 && Date.now() - Number(stored.updatedAt) > maxAge;

            if (!valid || expired) {
                if (stored) window.localStorage.removeItem(storageKey);
                return null;
            }

            const quantity = Math.min(10, Math.max(1, Number.parseInt(stored.quantity, 10) || 1));
            if (!stored.updatedAt) {
                stored.updatedAt = Date.now();
                stored.quantity = quantity;
                window.localStorage.setItem(storageKey, JSON.stringify(stored));
            }
            return { quantity };
        } catch (error) {
            try {
                window.localStorage.removeItem(storageKey);
            } catch (storageError) {
                // The preview remains usable without persistent storage.
            }
            return null;
        }
    };

    const renderBadge = (providedQuantity) => {
        const quantity = Number.isFinite(Number(providedQuantity))
            ? Math.min(10, Math.max(0, Number(providedQuantity)))
            : (readSelection()?.quantity || 0);
        if (count) count.textContent = String(quantity);
        if (headerCart) {
            headerCart.setAttribute('aria-label', `Private selection, ${quantity} item${quantity === 1 ? '' : 's'}`);
            headerCart.classList.toggle('has-selection', quantity > 0);
        }
    };

    window.addEventListener('veylune:selection-change', (event) => {
        renderBadge(event.detail?.quantity);
    });
    window.addEventListener('storage', (event) => {
        if (event.key === storageKey) renderBadge();
    });
    window.addEventListener('pageshow', () => renderBadge());
    renderBadge();
})();
