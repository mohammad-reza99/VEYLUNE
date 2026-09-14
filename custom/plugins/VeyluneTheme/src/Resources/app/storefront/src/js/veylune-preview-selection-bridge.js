import { readSelectionState, selectionQuantity, selectionStorageKey } from './veylune-preview-selection-store';

(() => {
    if (!window.location.pathname.startsWith('/__veylune-preview/')) return;

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
                'Cart, 0 items',
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

    const renderBadge = (providedQuantity) => {
        const quantity = Number.isFinite(Number(providedQuantity))
            ? Math.max(0, Number(providedQuantity))
            : selectionQuantity(readSelectionState());
        if (count) count.textContent = String(quantity);
        if (headerCart) {
            headerCart.setAttribute('aria-label', `Cart, ${quantity} item${quantity === 1 ? '' : 's'}`);
            headerCart.classList.toggle('has-selection', quantity > 0);
        }
    };

    window.addEventListener('veylune:selection-change', (event) => {
        renderBadge(event.detail?.quantity);
    });
    window.addEventListener('storage', (event) => {
        if (event.key === selectionStorageKey) renderBadge();
    });
    window.addEventListener('pageshow', () => renderBadge());
    renderBadge();
})();
