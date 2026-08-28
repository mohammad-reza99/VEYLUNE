document.querySelectorAll('[data-veylune-checkout-preview]').forEach((root) => {
    const storageKey = 'veylune-private-selection-v1';
    const reviewsStorageKey = 'veylune-checkout-reviews-v1';
    const settingsStorageKey = 'veylune-profile-settings-v1';
    const addressesStorageKey = 'veylune-address-book-v1';
    const form = root.querySelector('[data-checkout-form]');
    const empty = root.querySelector('[data-checkout-empty]');
    const status = root.querySelector('[data-checkout-status]');
    const dialog = root.querySelector('[data-checkout-review-dialog]');
    const dialogClose = root.querySelector('[data-checkout-review-close]');
    const submit = root.querySelector('[data-checkout-review]');
    let selection = null;

    const formatPrice = (value) => new Intl.NumberFormat('en-US', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(value);

    try {
        const stored = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
        const expired = Number(stored?.updatedAt) > 0 && Date.now() - Number(stored.updatedAt) > 30 * 24 * 60 * 60 * 1000;
        if (expired) window.localStorage.removeItem(storageKey);
        if (!expired && stored?.productId && stored?.productName && Number(stored.unitPrice) > 0) {
            selection = {
                productName: String(stored.productName),
                material: String(stored.material || 'Material pending'),
                quantity: Math.min(10, Math.max(1, Number.parseInt(stored.quantity, 10) || 1)),
                unitPrice: Number(stored.unitPrice),
            };
        }
    } catch (error) {
        status.textContent = 'The saved selection could not be restored.';
    }

    const renderSelection = () => {
        const hasSelection = Boolean(selection);
        form.hidden = !hasSelection;
        empty.hidden = hasSelection;
        if (!selection) return;
        const total = selection.unitPrice * selection.quantity;
        root.querySelector('[data-checkout-product-name]').textContent = selection.productName;
        root.querySelector('[data-checkout-product-material]').textContent = selection.material;
        root.querySelector('[data-checkout-product-quantity]').textContent = String(selection.quantity);
        root.querySelector('[data-checkout-line-total]').textContent = formatPrice(total);
        root.querySelector('[data-checkout-subtotal]').textContent = formatPrice(total);
        root.querySelector('[data-checkout-total]').textContent = formatPrice(total);
    };

    const prefillCheckoutDetails = () => {
        let restoredProfile = false;
        let restoredAddress = false;
        const setValue = (name, value) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field && !field.value && value) field.value = String(value);
        };
        try {
            const profile = JSON.parse(window.localStorage.getItem(settingsStorageKey) || 'null');
            if (profile?.firstName && profile?.lastName && profile?.email) {
                setValue('firstName', String(profile.firstName).slice(0, 40));
                setValue('lastName', String(profile.lastName).slice(0, 40));
                setValue('email', String(profile.email).slice(0, 100));
                setValue('phone', String(profile.phone || '').slice(0, 30));
                restoredProfile = true;
            }
            const savedAddresses = JSON.parse(window.localStorage.getItem(addressesStorageKey) || '[]');
            const address = Array.isArray(savedAddresses)
                ? (savedAddresses.find((item) => item?.isDefault) || savedAddresses[0])
                : null;
            if (address?.street && address?.city && address?.postalCode) {
                setValue('address', String(address.street).slice(0, 80));
                setValue('city', String(address.city).slice(0, 50));
                setValue('postalCode', String(address.postalCode).slice(0, 16));
                const countryMap = {
                    Germany: 'DE', Austria: 'AT', Switzerland: 'CH', Netherlands: 'NL', France: 'FR',
                };
                setValue('country', countryMap[address.country] || address.country);
                restoredAddress = true;
            }
        } catch (error) {
            // Checkout remains usable when private preview storage is unavailable.
        }
        if (restoredProfile || restoredAddress) {
            const restored = [restoredProfile && 'profile', restoredAddress && 'default address'].filter(Boolean).join(' and ');
            status.textContent = `Private ${restored} restored. Review the details before continuing.`;
        }
    };

    const validateField = (field) => {
        let valid = true;
        const value = field.type === 'checkbox' ? field.checked : field.value.trim();
        if (field.required && !value) valid = false;
        if (field.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) valid = false;
        if (field.name === 'postalCode' && value && !/^[a-z0-9][a-z0-9 -]{2,8}[a-z0-9]$/i.test(value)) valid = false;
        field.setAttribute('aria-invalid', String(!valid));
        if (valid) field.removeAttribute('aria-invalid');
        return valid;
    };

    form.querySelectorAll('input, select').forEach((field) => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('change', () => validateField(field));
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const requiredFields = Array.from(form.querySelectorAll('[required]'));
        const invalidFields = requiredFields.filter((field) => !validateField(field));
        const [firstInvalid] = invalidFields;
        if (firstInvalid) {
            const fieldLabels = {
                email: 'Email address', firstName: 'First name', lastName: 'Last name',
                address: 'Street address', city: 'City', postalCode: 'Postal code',
                country: 'Country', reviewConsent: 'Review consent',
            };
            const fieldLabel = fieldLabels[firstInvalid.name] || 'required field';
            status.textContent = `${invalidFields.length} required ${invalidFields.length === 1 ? 'field needs' : 'fields need'} attention. Start with ${fieldLabel}.`;
            firstInvalid.focus();
            return;
        }

        const data = new FormData(form);
        const deliveryLabel = form.querySelector('[name="deliveryMethod"]:checked')?.closest('label')?.querySelector('strong')?.textContent.trim() || 'Pending';
        const destinationLabel = `${data.get('address')}, ${data.get('postalCode')} ${data.get('city')}, ${data.get('country')}`;
        root.querySelector('[data-review-contact]').textContent = `${data.get('firstName')} ${data.get('lastName')} · ${data.get('email')}`;
        root.querySelector('[data-review-destination]').textContent = destinationLabel;
        root.querySelector('[data-review-delivery]').textContent = deliveryLabel;
        root.querySelector('[data-review-total]').textContent = root.querySelector('[data-checkout-total]').textContent;
        try {
            const existing = JSON.parse(window.localStorage.getItem(reviewsStorageKey) || '[]');
            const reviews = Array.isArray(existing) ? existing : [];
            const createdAt = Date.now();
            reviews.unshift({
                id: `VR-${new Date(createdAt).toISOString().slice(2, 10).replaceAll('-', '')}-${String(createdAt).slice(-4)}`,
                productName: selection.productName,
                material: selection.material,
                quantity: selection.quantity,
                total: selection.unitPrice * selection.quantity,
                destination: destinationLabel,
                delivery: deliveryLabel,
                createdAt,
            });
            window.localStorage.setItem(reviewsStorageKey, JSON.stringify(reviews.slice(0, 12)));
            status.textContent = 'Details complete. Preview review saved without creating an order.';
        } catch (error) {
            status.textContent = 'Details complete. Review opened, but preview history could not be saved.';
        }
        dialog.showModal();
    });

    dialogClose.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
    dialog.addEventListener('close', () => submit.focus());

    renderSelection();
    prefillCheckoutDetails();
});
