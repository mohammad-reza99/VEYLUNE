import {
    clampSelectionQuantity,
    emitSelectionChange,
    readSelectionState,
    selectionQuantity,
    selectionSubtotal,
    writeSelectionState,
} from './veylune-preview-selection-store';

document.querySelectorAll('[data-veylune-cart-preview]').forEach((root) => {
    const empty = root.querySelector('[data-cart-page-empty]');
    const content = root.querySelector('[data-cart-page-content]');
    const count = root.querySelector('[data-cart-page-count]');
    const items = root.querySelector('[data-cart-page-items]');
    const itemTemplate = root.querySelector('[data-cart-page-item-template]');
    const subtotal = root.querySelector('[data-cart-page-subtotal]');
    const total = root.querySelector('[data-cart-page-total]');
    const deliverySummary = root.querySelector('[data-cart-page-delivery-summary]');
    const status = root.querySelector('[data-cart-page-status]');
    const checkout = root.querySelector('[data-cart-page-checkout]');
    const deliveryForm = root.querySelector('[data-cart-page-delivery]');
    const deliveryStatus = root.querySelector('[data-cart-page-delivery-status]');
    const promoForm = root.querySelector('[data-cart-page-promo]');
    const promoStatus = root.querySelector('[data-cart-page-promo-status]');
    const alertCopy = root.querySelector('[data-cart-page-alert-copy]');
    const undo = root.querySelector('[data-cart-page-undo]');
    let selectionState = readSelectionState();
    let removedEntry = null;

    const formatPrice = (value) => new Intl.NumberFormat('en-US', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(value);

    const persist = () => {
        selectionState = writeSelectionState(selectionState);
        emitSelectionChange(selectionState);
    };

    const focusLineAction = (lineId, action) => {
        window.requestAnimationFrame(() => {
            items.querySelector(`[data-line-id="${CSS.escape(lineId)}"] [data-cart-page-item-${action}]`)?.focus();
        });
    };

    const renderItem = (item) => {
        const article = itemTemplate.content.firstElementChild.cloneNode(true);
        const itemTotal = item.unitPrice * item.quantity;
        article.dataset.lineId = item.lineId;
        article.dataset.productId = item.productId;
        article.querySelector('[data-cart-page-item-name]').textContent = item.productName;
        article.querySelector('[data-cart-page-item-material]').textContent = item.material;
        article.querySelector('[data-cart-page-item-unit]').textContent = formatPrice(item.unitPrice);
        article.querySelector('[data-cart-page-item-quantity]').textContent = String(item.quantity);
        article.querySelector('[data-cart-page-item-total]').textContent = formatPrice(itemTotal);
        article.querySelector('[data-cart-page-item-quantity-control]').setAttribute('aria-label', `${item.productName} quantity`);
        const decrease = article.querySelector('[data-cart-page-item-decrease]');
        const increase = article.querySelector('[data-cart-page-item-increase]');
        const remove = article.querySelector('[data-cart-page-item-remove]');
        decrease.disabled = item.quantity <= 1;
        increase.disabled = item.quantity >= 10;
        decrease.setAttribute('aria-label', `Decrease ${item.productName} quantity`);
        increase.setAttribute('aria-label', `Increase ${item.productName} quantity`);
        remove.setAttribute('aria-label', `Remove ${item.productName}`);
        return article;
    };

    const render = () => {
        const totalQuantity = selectionQuantity(selectionState);
        const estimatedTotal = selectionSubtotal(selectionState);
        const hasSelection = selectionState.items.length > 0;
        empty.hidden = hasSelection;
        content.hidden = !hasSelection;
        count.textContent = `${totalQuantity} item${totalQuantity === 1 ? '' : 's'}`;
        items.replaceChildren(...selectionState.items.map(renderItem));
        subtotal.textContent = formatPrice(estimatedTotal);
        total.textContent = formatPrice(estimatedTotal);
        deliverySummary.textContent = selectionState.postalCode ? `Destination ${selectionState.postalCode}` : 'To be confirmed';
    };

    items.addEventListener('click', (event) => {
        const action = event.target.closest('[data-cart-page-item-decrease], [data-cart-page-item-increase], [data-cart-page-item-remove]');
        const article = action?.closest('[data-cart-page-item]');
        if (!action || !article) return;
        const index = selectionState.items.findIndex((item) => item.lineId === article.dataset.lineId);
        if (index < 0) return;
        const item = selectionState.items[index];

        if (action.matches('[data-cart-page-item-remove]')) {
            removedEntry = { item: { ...item }, index };
            selectionState.items.splice(index, 1);
            if (selectionState.activeLineId === item.lineId) {
                selectionState.activeLineId = selectionState.items[0]?.lineId || '';
            }
            persist();
            render();
            status.textContent = `${item.productName} removed. ${selectionQuantity(selectionState)} item${selectionQuantity(selectionState) === 1 ? '' : 's'} remain.`;
            alertCopy.textContent = `${item.productName} removed from your cart.`;
            undo.hidden = false;
            undo.focus();
            return;
        }

        const increasing = action.matches('[data-cart-page-item-increase]');
        item.quantity = clampSelectionQuantity(item.quantity + (increasing ? 1 : -1));
        selectionState.activeLineId = item.lineId;
        persist();
        render();
        status.textContent = `${item.productName} quantity changed to ${item.quantity}.`;
        focusLineAction(item.lineId, increasing ? 'increase' : 'decrease');
    });

    undo.addEventListener('click', () => {
        if (!removedEntry || selectionState.items.some((item) => item.lineId === removedEntry.item.lineId)) return;
        const insertionIndex = Math.min(removedEntry.index, selectionState.items.length);
        selectionState.items.splice(insertionIndex, 0, removedEntry.item);
        selectionState.activeLineId = removedEntry.item.lineId;
        const restoredLineId = removedEntry.item.lineId;
        const restoredName = removedEntry.item.productName;
        removedEntry = null;
        persist();
        render();
        alertCopy.textContent = `${restoredName} restored. This preview does not reserve stock.`;
        undo.hidden = true;
        status.textContent = `${restoredName} restored to your cart.`;
        focusLineAction(restoredLineId, 'remove');
    });

    deliveryForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = deliveryForm.elements.postalCode;
        const value = input.value.trim();
        if (!/^[a-z0-9][a-z0-9 -]{2,8}[a-z0-9]$/i.test(value)) {
            input.setAttribute('aria-invalid', 'true');
            deliveryStatus.textContent = 'Enter a valid postal code.';
            input.focus();
            return;
        }
        input.removeAttribute('aria-invalid');
        selectionState.postalCode = value.toUpperCase();
        persist();
        render();
        deliveryStatus.textContent = `Destination ${value.toUpperCase()} saved for consultation. Delivery price is not included yet.`;
    });

    promoForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = promoForm.elements.promoCode;
        const value = input.value.trim();
        if (!value) {
            input.setAttribute('aria-invalid', 'true');
            promoStatus.textContent = 'Enter a project or invitation code.';
            input.focus();
            return;
        }
        input.removeAttribute('aria-invalid');
        promoStatus.textContent = 'Code noted. Validation remains pending activation.';
    });

    checkout.addEventListener('click', () => {
        status.textContent = `Opening checkout preview with ${selectionState.items.length} cart line${selectionState.items.length === 1 ? '' : 's'}. No order has been created.`;
    });

    if (selectionState.postalCode) deliveryForm.elements.postalCode.value = selectionState.postalCode;
    render();
});
