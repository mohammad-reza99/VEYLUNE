import {
    clampSelectionQuantity,
    emitSelectionChange,
    readSelectionState,
    selectionQuantity,
    upsertSelectionItem,
    writeSelectionState,
} from './veylune-preview-selection-store';

document.querySelectorAll('[data-veylune-pdp-preview]').forEach((root) => {
    const saveButton = root.querySelector('.veylune-pdp-buybox__save');
    const productName = root.querySelector('.veylune-pdp-buybox h1')?.textContent.trim() || 'piece';
    const storageKey = `veylune-preview-pdp-${productName}`;
    const galleryStage = root.querySelector('[data-pdp-gallery-stage]');
    const galleryDescription = root.querySelector('[data-pdp-gallery-description]');
    const galleryThumbs = Array.from(root.querySelectorAll('[data-pdp-gallery-thumb]'));
    const lightbox = root.querySelector('[data-pdp-lightbox]');
    const lightboxStage = root.querySelector('[data-pdp-lightbox-stage]');
    const lightboxCaption = root.querySelector('[data-pdp-lightbox-caption]');
    const zoomOpen = root.querySelector('[data-pdp-zoom-open]');
    const zoomClose = root.querySelector('[data-pdp-zoom-close]');
    const variantButtons = Array.from(root.querySelectorAll('[data-pdp-variant]'));
    const variantLabel = root.querySelector('[data-pdp-variant-label]');
    const deliveryForm = root.querySelector('[data-pdp-delivery-form]');
    const deliveryStatus = root.querySelector('[data-pdp-delivery-status]');
    const deliveryButton = deliveryForm?.querySelector('button[type="submit"]');
    const serviceInputs = Array.from(root.querySelectorAll('[name="veylunePdpService"]'));
    const serviceStatus = root.querySelector('[data-pdp-service-status]');
    const bundleItems = Array.from(root.querySelectorAll('[data-pdp-bundle-item]'));
    const bundleCount = root.querySelector('[data-pdp-bundle-count]');
    const bundleTotal = root.querySelector('[data-pdp-bundle-total]');
    const quantityInput = root.querySelector('[data-pdp-quantity-input]');
    const quantityDecrease = root.querySelector('[data-pdp-quantity-decrease]');
    const quantityIncrease = root.querySelector('[data-pdp-quantity-increase]');
    const addButtons = Array.from(root.querySelectorAll('[data-pdp-add-selection], [data-pdp-add-selection-mobile]'));
    const addStatus = root.querySelector('[data-pdp-add-status]');
    const cartDrawer = root.querySelector('[data-pdp-cart-drawer]');
    const cartClose = root.querySelector('[data-pdp-cart-close]');
    const cartEmpty = root.querySelector('[data-pdp-cart-empty]');
    const cartContent = root.querySelector('[data-pdp-cart-content]');
    const cartMaterial = root.querySelector('[data-pdp-cart-material]');
    const cartQuantity = root.querySelector('[data-pdp-cart-quantity]');
    const cartSubtotal = root.querySelector('[data-pdp-cart-subtotal]');
    const cartTotal = root.querySelector('[data-pdp-cart-total]');
    const cartDecrease = root.querySelector('[data-pdp-cart-decrease]');
    const cartIncrease = root.querySelector('[data-pdp-cart-increase]');
    const cartRemove = root.querySelector('[data-pdp-cart-remove]');
    const cartCheckout = root.querySelector('[data-pdp-cart-checkout]');
    const cartStatus = root.querySelector('[data-pdp-cart-status]');
    const viewLabels = {
        front: 'Front view',
        profile: 'Side profile',
        detail: 'Material detail',
        scale: 'Room scale',
    };
    let activeView = 'front';

    const selectView = (view, focusThumb = false) => {
        if (!viewLabels[view]) return;
        activeView = view;
        galleryStage.dataset.galleryView = view;
        galleryDescription.textContent = `${viewLabels[view]} concept study for ${productName}`;
        galleryThumbs.forEach((thumb) => {
            const selected = thumb.dataset.pdpGalleryThumb === view;
            thumb.setAttribute('aria-pressed', String(selected));
            if (selected && focusThumb) thumb.focus();
        });
    };

    galleryThumbs.forEach((thumb, index) => {
        thumb.addEventListener('click', () => selectView(thumb.dataset.pdpGalleryThumb));
        thumb.addEventListener('keydown', (event) => {
            if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            let nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? galleryThumbs.length - 1 : index + (event.key === 'ArrowRight' ? 1 : -1);
            nextIndex = (nextIndex + galleryThumbs.length) % galleryThumbs.length;
            selectView(galleryThumbs[nextIndex].dataset.pdpGalleryThumb, true);
        });
    });

    zoomOpen?.addEventListener('click', () => {
        lightboxStage.dataset.galleryView = activeView;
        lightboxCaption.textContent = `${viewLabels[activeView]} of ${productName}`;
        lightbox.showModal();
    });
    zoomClose?.addEventListener('click', () => lightbox.close());
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) lightbox.close();
    });
    lightbox?.addEventListener('close', () => zoomOpen?.focus());

    const selectVariant = (variant, updateUrl = true) => {
        const button = variantButtons.find((item) => item.dataset.pdpVariant === variant);
        if (!button) return;
        variantButtons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        variantLabel.textContent = button.dataset.pdpVariantName;
        if (!updateUrl) return;
        const url = new URL(window.location.href);
        url.searchParams.set('material', button.dataset.pdpVariant);
        window.history.replaceState({}, '', url);
    };

    variantButtons.forEach((button) => button.addEventListener('click', () => selectVariant(button.dataset.pdpVariant)));
    selectVariant(new URL(window.location.href).searchParams.get('material'), false);
    window.addEventListener('popstate', () => {
        selectVariant(new URL(window.location.href).searchParams.get('material'), false);
    });

    deliveryForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = deliveryForm.elements.postalCode;
        const postalCode = input.value.trim();
        deliveryButton?.setAttribute('aria-busy', 'true');
        if (deliveryButton) deliveryButton.disabled = true;
        deliveryStatus.textContent = 'Checking destination format...';

        window.requestAnimationFrame(() => {
            const valid = /^[a-z0-9][a-z0-9 -]{2,8}[a-z0-9]$/i.test(postalCode);
            if (!valid) {
                input.setAttribute('aria-invalid', 'true');
                deliveryStatus.textContent = 'Enter a valid postal code.';
                input.focus();
            } else {
                input.removeAttribute('aria-invalid');
                deliveryStatus.textContent = `Destination ${postalCode.toUpperCase()} saved. Exact delivery scope remains subject to consultation.`;
                if (selectionState.items.length) {
                    selectionState.postalCode = postalCode.toUpperCase();
                    persistSelection();
                }
            }
            deliveryButton?.removeAttribute('aria-busy');
            if (deliveryButton) deliveryButton.disabled = false;
        });
    });

    serviceInputs.forEach((input) => input.addEventListener('change', () => {
        const label = input.closest('label')?.querySelector('strong')?.textContent.trim() || 'Service';
        serviceStatus.textContent = `${label} selected.`;
        if (selectionState.items.length) {
            selectionState.service = input.value;
            persistSelection();
        }
    }));

    const updateBundle = (updateUrl = true) => {
        const selected = bundleItems.filter((item) => item.checked);
        const basePrice = Number((root.querySelector('.veylune-pdp-buybox__price strong')?.textContent || '').replace(/[^0-9.]/g, '')) || 0;
        const total = selected.reduce((sum, item) => sum + Number(item.dataset.price || 0), basePrice);
        bundleCount.textContent = String(selected.length);
        bundleTotal.textContent = new Intl.NumberFormat('en-US', {
            style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
        }).format(total);
        if (!updateUrl) return;
        const url = new URL(window.location.href);
        const indexes = bundleItems.flatMap((item, index) => item.checked ? [index + 1] : []);
        if (indexes.length) url.searchParams.set('bundle', indexes.join(','));
        else url.searchParams.delete('bundle');
        window.history.replaceState({}, '', url);
    };

    bundleItems.forEach((input) => input.addEventListener('change', () => updateBundle()));
    const selectedBundleIndexes = new Set(
        (new URL(window.location.href).searchParams.get('bundle') || '')
            .split(',')
            .map(Number)
            .filter((index) => Number.isInteger(index) && index > 0)
    );
    bundleItems.forEach((item, index) => {
        item.checked = selectedBundleIndexes.has(index + 1);
    });
    updateBundle(false);

    const formatPrice = (value) => new Intl.NumberFormat('en-US', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(value);
    const unitPrice = Number(root.dataset.productPrice) || 0;
    let plannedQuantity = 1;
    let selectionState = readSelectionState();
    let selection = selectionState.items.find((item) => (
        item.lineId === selectionState.activeLineId && item.productId === root.dataset.productId
    )) || selectionState.items.find((item) => item.productId === root.dataset.productId) || null;

    if (selection) plannedQuantity = selection.quantity;
    if (selectionState.postalCode && deliveryForm?.elements.postalCode) {
        deliveryForm.elements.postalCode.value = selectionState.postalCode;
    }
    if (selectionState.items.length) {
        const storedService = serviceInputs.find((input) => input.value === selectionState.service);
        if (storedService) {
            serviceInputs.forEach((input) => { input.checked = input === storedService; });
            const label = storedService.closest('label')?.querySelector('strong')?.textContent.trim() || 'Service';
            serviceStatus.textContent = `${label} selected.`;
        }
    }

    const persistSelection = () => {
        try {
            selectionState = writeSelectionState(selectionState);
            selection = selection
                ? selectionState.items.find((item) => item.lineId === selection.lineId) || null
                : null;
            emitSelectionChange(selectionState);
        } catch (error) {
            cartStatus.textContent = 'Cart updated for this session. Browser storage is unavailable.';
        }
    };

    const renderPlannedQuantity = () => {
        if (quantityInput) quantityInput.value = String(plannedQuantity);
        if (quantityDecrease) quantityDecrease.disabled = plannedQuantity <= 1;
        if (quantityIncrease) quantityIncrease.disabled = plannedQuantity >= 10;
    };

    const renderSelection = () => {
        const hasSelection = Boolean(selection);
        if (cartEmpty) cartEmpty.hidden = hasSelection;
        if (cartContent) cartContent.hidden = !hasSelection;
        if (!selection) return;
        const subtotal = selection.unitPrice * selection.quantity;
        cartMaterial.textContent = selection.material;
        cartQuantity.textContent = String(selection.quantity);
        cartSubtotal.textContent = formatPrice(subtotal);
        cartTotal.textContent = formatPrice(subtotal);
        cartDecrease.disabled = selection.quantity <= 1;
        cartIncrease.disabled = selection.quantity >= 10;
    };

    const openSelection = (trigger) => {
        cartDrawer.dataset.returnFocus = trigger === addButtons[1] ? 'mobile' : 'desktop';
        renderSelection();
        cartDrawer.showModal();
    };

    const updatePlannedQuantity = (value) => {
        plannedQuantity = clampSelectionQuantity(value);
        renderPlannedQuantity();
    };

    quantityDecrease?.addEventListener('click', () => updatePlannedQuantity(plannedQuantity - 1));
    quantityIncrease?.addEventListener('click', () => updatePlannedQuantity(plannedQuantity + 1));
    quantityInput?.addEventListener('change', () => updatePlannedQuantity(quantityInput.value));
    quantityInput?.addEventListener('blur', () => updatePlannedQuantity(quantityInput.value));

    const addToSelection = (button) => {
        selectionState.postalCode = /^[a-z0-9][a-z0-9 -]{2,8}[a-z0-9]$/i.test(deliveryForm?.elements.postalCode?.value.trim() || '')
            ? deliveryForm.elements.postalCode.value.trim().toUpperCase()
            : selectionState.postalCode;
        selectionState.service = serviceInputs.find((input) => input.checked)?.value || selectionState.service || 'studio';
        const result = upsertSelectionItem(selectionState, {
            productId: root.dataset.productId,
            productName: root.dataset.productName,
            material: variantLabel?.textContent || '',
            quantity: plannedQuantity,
            unitPrice,
        });
        selectionState = result.state;
        selection = result.item;
        persistSelection();
        renderSelection();
        const totalQuantity = selectionQuantity(selectionState);
        if (addStatus) addStatus.textContent = `${plannedQuantity} x ${root.dataset.productName} added to your cart.`;
        cartStatus.textContent = `${totalQuantity} item${totalQuantity === 1 ? '' : 's'} across ${selectionState.items.length} cart line${selectionState.items.length === 1 ? '' : 's'}.`;
        openSelection(button);
    };

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-pdp-add-selection], [data-pdp-add-selection-mobile]');
        if (!button || !root.contains(button)) return;
        addToSelection(button);
    }, true);

    cartClose?.addEventListener('click', () => cartDrawer.close());
    cartDrawer?.addEventListener('click', (event) => {
        if (event.target === cartDrawer) cartDrawer.close();
    });
    cartDrawer?.addEventListener('close', () => {
        const index = cartDrawer.dataset.returnFocus === 'mobile' ? 1 : 0;
        addButtons[index]?.focus();
    });
    cartDecrease?.addEventListener('click', () => {
        if (!selection) return;
        selection.quantity = clampSelectionQuantity(selection.quantity - 1);
        persistSelection();
        renderSelection();
        cartStatus.textContent = `Cart quantity changed to ${selection.quantity}.`;
    });
    cartIncrease?.addEventListener('click', () => {
        if (!selection) return;
        selection.quantity = clampSelectionQuantity(selection.quantity + 1);
        persistSelection();
        renderSelection();
        cartStatus.textContent = `Cart quantity changed to ${selection.quantity}.`;
    });
    cartRemove?.addEventListener('click', () => {
        const removedLineId = selection?.lineId;
        selectionState.items = selectionState.items.filter((item) => item.lineId !== removedLineId);
        selectionState.activeLineId = selectionState.items[0]?.lineId || '';
        selection = null;
        persistSelection();
        renderSelection();
        const remaining = selectionQuantity(selectionState);
        cartStatus.textContent = remaining
            ? `Item removed. ${remaining} item${remaining === 1 ? '' : 's'} remain in your cart.`
            : 'Item removed. Your cart is empty.';
    });
    cartCheckout?.addEventListener('click', () => {
        cartStatus.textContent = 'Checkout activation is pending supplier, delivery and pricing approval.';
    });

    renderPlannedQuantity();
    renderSelection();

    if (!saveButton) return;

    try {
        const selected = window.localStorage.getItem(storageKey) === 'true';
        saveButton.setAttribute('aria-pressed', String(selected));
        saveButton.textContent = selected ? 'Saved for later' : 'Save for later';
    } catch (error) {
        // The in-session control remains usable when storage is unavailable.
    }

    saveButton.addEventListener('click', () => {
        const selected = saveButton.getAttribute('aria-pressed') !== 'true';
        saveButton.setAttribute('aria-pressed', String(selected));
        saveButton.textContent = selected ? 'Saved for later' : 'Save for later';

        try {
            window.localStorage.setItem(storageKey, String(selected));
        } catch (error) {
            // Pressed state still communicates the current-session action.
        }
    });
});
