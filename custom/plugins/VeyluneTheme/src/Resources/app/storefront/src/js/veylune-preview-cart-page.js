document.querySelectorAll('[data-veylune-cart-preview]').forEach((root) => {
    const storageKey = 'veylune-private-selection-v1';
    const empty = root.querySelector('[data-cart-page-empty]');
    const content = root.querySelector('[data-cart-page-content]');
    const count = root.querySelector('[data-cart-page-count]');
    const name = root.querySelector('[data-cart-page-name]');
    const material = root.querySelector('[data-cart-page-material]');
    const unit = root.querySelector('[data-cart-page-unit]');
    const quantity = root.querySelector('[data-cart-page-quantity]');
    const lineTotal = root.querySelector('[data-cart-page-line-total]');
    const subtotal = root.querySelector('[data-cart-page-subtotal]');
    const total = root.querySelector('[data-cart-page-total]');
    const decrease = root.querySelector('[data-cart-page-decrease]');
    const increase = root.querySelector('[data-cart-page-increase]');
    const remove = root.querySelector('[data-cart-page-remove]');
    const status = root.querySelector('[data-cart-page-status]');
    const checkout = root.querySelector('[data-cart-page-checkout]');
    const deliveryForm = root.querySelector('[data-cart-page-delivery]');
    const deliveryStatus = root.querySelector('[data-cart-page-delivery-status]');
    const promoForm = root.querySelector('[data-cart-page-promo]');
    const promoStatus = root.querySelector('[data-cart-page-promo-status]');
    const alertCopy = root.querySelector('[data-cart-page-alert-copy]');
    const undo = root.querySelector('[data-cart-page-undo]');
    let selection = null;
    let removedSelection = null;

    const clampQuantity = (value) => Math.min(10, Math.max(1, Number.parseInt(value, 10) || 1));
    const formatPrice = (value) => new Intl.NumberFormat('en-US', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(value);

    try {
        const stored = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
        const expired = Number(stored?.updatedAt) > 0 && Date.now() - Number(stored.updatedAt) > 30 * 24 * 60 * 60 * 1000;
        if (expired) window.localStorage.removeItem(storageKey);
        if (!expired && stored?.productId && stored?.productName && Number(stored.unitPrice) > 0) {
            selection = {
                productId: String(stored.productId),
                productName: String(stored.productName),
                material: String(stored.material || 'Pending'),
                quantity: clampQuantity(stored.quantity),
                unitPrice: Number(stored.unitPrice),
                updatedAt: Number(stored.updatedAt) || Date.now(),
            };
        }
    } catch (error) {
        status.textContent = 'Your saved selection could not be restored. Start a new private selection.';
    }

    const persist = () => {
        try {
            if (selection) {
                selection.updatedAt = Date.now();
                window.localStorage.setItem(storageKey, JSON.stringify(selection));
            } else {
                window.localStorage.removeItem(storageKey);
            }
            window.dispatchEvent(new CustomEvent('veylune:selection-change', {
                detail: { quantity: selection?.quantity || 0 },
            }));
        } catch (error) {
            status.textContent = 'Selection updated for this session. Browser storage is unavailable.';
        }
    };

    const render = () => {
        const hasSelection = Boolean(selection);
        if (selection) {
            root.dataset.productId = selection.productId;
        } else {
            delete root.dataset.productId;
        }
        empty.hidden = hasSelection;
        content.hidden = !hasSelection;
        count.textContent = hasSelection ? `${selection.quantity} item${selection.quantity === 1 ? '' : 's'}` : '0 items';
        if (!selection) return;
        const estimatedTotal = selection.unitPrice * selection.quantity;
        name.textContent = selection.productName;
        material.textContent = selection.material;
        unit.textContent = formatPrice(selection.unitPrice);
        quantity.textContent = String(selection.quantity);
        lineTotal.textContent = formatPrice(estimatedTotal);
        subtotal.textContent = formatPrice(estimatedTotal);
        total.textContent = formatPrice(estimatedTotal);
        decrease.disabled = selection.quantity <= 1;
        increase.disabled = selection.quantity >= 10;
    };

    decrease.addEventListener('click', () => {
        if (!selection) return;
        selection.quantity = clampQuantity(selection.quantity - 1);
        persist();
        render();
        status.textContent = `Quantity changed to ${selection.quantity}.`;
    });
    increase.addEventListener('click', () => {
        if (!selection) return;
        selection.quantity = clampQuantity(selection.quantity + 1);
        persist();
        render();
        status.textContent = `Quantity changed to ${selection.quantity}.`;
    });
    remove.addEventListener('click', () => {
        removedSelection = selection ? { ...selection } : null;
        selection = null;
        persist();
        render();
        status.textContent = 'Item removed. Your private selection is empty.';
        alertCopy.textContent = 'Item removed from your private selection.';
        undo.hidden = !removedSelection;
        undo.focus();
    });

    undo.addEventListener('click', () => {
        if (!removedSelection) return;
        selection = removedSelection;
        removedSelection = null;
        persist();
        render();
        alertCopy.textContent = 'Item restored. This preview does not reserve stock.';
        undo.hidden = true;
        remove.focus();
        status.textContent = 'Item restored to your private selection.';
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
        status.textContent = 'Opening the private checkout preview. No order has been created.';
    });

    render();
});
