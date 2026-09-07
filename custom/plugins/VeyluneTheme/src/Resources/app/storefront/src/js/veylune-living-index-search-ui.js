const initLivingIndexSearchUi = () => {
    const input = document.querySelector('[data-vli-header-search-input]');
    const panel = document.querySelector('[data-vli-header-suggest]');
    const form = input?.closest('form');

    if (!input || !panel || !form || panel.dataset.vliSearchUiInitialized === 'true') return;

    panel.dataset.vliSearchUiInitialized = 'true';

    const queryLink = panel.querySelector('[data-vli-header-query-link]');
    const queryLabel = panel.querySelector('[data-vli-header-query-label]');
    const suggestions = [...panel.querySelectorAll('[data-vli-header-suggestion]')];

    const close = () => {
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    };

    const update = () => {
        const query = input.value.trim();
        const normalizedQuery = query.toLocaleLowerCase();
        const action = new URL(form.action, window.location.origin);

        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');

        if (queryLink && queryLabel) {
            action.searchParams.set('q', query);
            queryLink.href = `${action.pathname}${action.search}`;
            queryLink.hidden = query.length === 0;
            queryLabel.textContent = query;
        }

        suggestions.forEach((suggestion) => {
            const terms = String(suggestion.dataset.searchTerms || suggestion.textContent).toLocaleLowerCase();
            suggestion.hidden = normalizedQuery.length > 0 && !terms.includes(normalizedQuery);
        });
    };

    input.addEventListener('focus', update);
    input.addEventListener('input', update);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            input.blur();
        }
    });

    document.addEventListener('pointerdown', (event) => {
        if (!panel.contains(event.target) && !form.contains(event.target)) close();
    });

    window.addEventListener('pageshow', close);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLivingIndexSearchUi, { once: true });
} else {
    initLivingIndexSearchUi();
}
