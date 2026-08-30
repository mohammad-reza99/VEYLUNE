document.querySelectorAll('[data-plp-wishlist]').forEach((button) => {
    const card = button.closest('[data-plp-card]');
    const storageKey = `veylune-preview-wishlist-${card.dataset.previewRecord}`;
    const updateState = (selected) => {
        button.setAttribute('aria-pressed', String(selected));
        button.setAttribute('aria-label', `${selected ? 'Remove' : 'Save'} ${card.dataset.plpName}${selected ? ' from saved pieces' : ''}`);
    };

    try {
        updateState(localStorage.getItem(storageKey) === 'true');
    } catch (error) {
        // The control remains available for the current page session.
        updateState(false);
    }

    button.addEventListener('click', () => {
        const selected = button.getAttribute('aria-pressed') !== 'true';
        updateState(selected);

        try {
            localStorage.setItem(storageKey, String(selected));
        } catch (error) {
            // Pressed state still communicates the in-session action.
        }
    });
});
