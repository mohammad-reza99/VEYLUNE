const normalize = (value = '') => value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

document.querySelectorAll('[data-veylune-plp]').forEach((root) => {
    const pageSize = 12;
    const grid = root.querySelector('[data-plp-grid]');
    const cards = Array.from(root.querySelectorAll('[data-plp-card]'));
    const chips = Array.from(root.querySelectorAll('[data-plp-type]'));
    const materialInputs = Array.from(root.querySelectorAll('[data-plp-material]'));
    const sortOptions = Array.from(root.querySelectorAll('[data-plp-sort]'));
    const filterToggle = root.querySelector('[data-plp-filter-toggle]');
    const filterPanel = root.querySelector('[data-plp-filter-panel]');
    const sortToggle = root.querySelector('[data-plp-sort-toggle]');
    const sortPanel = root.querySelector('[data-plp-sort-panel]');
    const count = root.querySelector('[data-plp-count]');
    const showing = root.querySelector('[data-plp-showing]');
    const empty = root.querySelector('[data-plp-empty]');
    const loadMore = root.querySelector('[data-plp-load-more]');
    let activeType = 'all';
    let activeSort = 'featured';
    let visibleLimit = pageSize;
    let renderFrame = null;

    const menuPairs = [[filterToggle, filterPanel], [sortToggle, sortPanel]];

    const matchesType = (card) => {
        if (activeType === 'all') return true;
        const type = normalize(card.dataset.plpType);
        if (activeType === 'chairs') return type.includes('chair');
        if (activeType === 'tables') return type.includes('table');
        if (activeType === 'benches-stools') return type.includes('bench') || type.includes('stool');
        return type.includes(activeType.replace(/s$/, ''));
    };

    const updateUrl = () => {
        const url = new URL(window.location.href);
        const materials = materialInputs.filter((input) => input.checked).map((input) => input.value);
        activeType === 'all' ? url.searchParams.delete('type') : url.searchParams.set('type', activeType);
        activeSort === 'featured' ? url.searchParams.delete('sort') : url.searchParams.set('sort', activeSort);
        materials.length ? url.searchParams.set('materials', materials.join(',')) : url.searchParams.delete('materials');
        visibleLimit > pageSize ? url.searchParams.set('view', String(visibleLimit)) : url.searchParams.delete('view');
        window.history.replaceState({}, '', url);
    };

    const render = (syncUrl = true) => {
        const materials = materialInputs.filter((input) => input.checked).map((input) => input.value);
        const filtered = cards.filter((card) => matchesType(card) && (!materials.length || materials.includes(card.dataset.plpMaterial)));
        const sorted = [...filtered].sort((a, b) => {
            if (activeSort === 'price-low') return Number(a.dataset.plpPrice) - Number(b.dataset.plpPrice);
            if (activeSort === 'price-high') return Number(b.dataset.plpPrice) - Number(a.dataset.plpPrice);
            if (activeSort === 'name') return a.dataset.plpName.localeCompare(b.dataset.plpName);
            return Number(a.dataset.plpIndex) - Number(b.dataset.plpIndex);
        });
        const displayed = sorted.slice(0, visibleLimit);

        cards.forEach((card) => { card.hidden = !displayed.includes(card); });
        sorted.forEach((card) => grid.insertBefore(card, empty));
        count.textContent = String(filtered.length);
        showing.textContent = String(displayed.length);
        empty.hidden = filtered.length !== 0;
        loadMore.hidden = displayed.length >= filtered.length;
        root.style.setProperty('--veylune-plp-progress', filtered.length ? String(displayed.length / filtered.length) : '0');
        grid.setAttribute('aria-busy', 'false');
        grid.classList.remove('is-updating');
        loadMore.removeAttribute('aria-disabled');
        loadMore.textContent = 'Load more';
        if (syncUrl) updateUrl();
    };

    const scheduleRender = (syncUrl = true) => {
        if (renderFrame) window.cancelAnimationFrame(renderFrame);
        grid.setAttribute('aria-busy', 'true');
        grid.classList.add('is-updating');
        renderFrame = window.requestAnimationFrame(() => {
            render(syncUrl);
            renderFrame = null;
        });
    };

    const closeMenus = (except = null, restoreFocus = false) => {
        menuPairs.forEach(([toggle, panel]) => {
            if (panel !== except && !panel.hidden) {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                if (restoreFocus) toggle.focus();
            }
        });
    };

    const openMenu = (toggle, panel) => {
        const opening = panel.hidden;
        closeMenus(opening ? panel : null);
        panel.hidden = !opening;
        toggle.setAttribute('aria-expanded', String(opening));
        if (opening) {
            window.requestAnimationFrame(() => panel.querySelector('input, button')?.focus());
        }
    };

    const applyUrl = () => {
        const params = new URL(window.location.href).searchParams;
        const requestedType = params.get('type') || 'all';
        activeType = chips.some((chip) => chip.dataset.plpType === requestedType) ? requestedType : 'all';
        activeSort = sortOptions.some((option) => option.dataset.plpSort === params.get('sort')) ? params.get('sort') : 'featured';
        const requestedMaterials = (params.get('materials') || '').split(',').filter(Boolean);
        visibleLimit = Math.max(pageSize, Number(params.get('view')) || pageSize);
        chips.forEach((chip) => { const selected = chip.dataset.plpType === activeType; chip.classList.toggle('is-active', selected); chip.setAttribute('aria-pressed', String(selected)); });
        materialInputs.forEach((input) => { input.checked = requestedMaterials.includes(input.value); });
        sortOptions.forEach((option) => option.setAttribute('aria-checked', String(option.dataset.plpSort === activeSort)));
        const currentSort = sortOptions.find((option) => option.dataset.plpSort === activeSort);
        sortToggle.firstChild.textContent = `Sort: ${currentSort.textContent.trim()} `;
        render(false);
    };

    chips.forEach((chip) => chip.addEventListener('click', () => {
        activeType = chip.dataset.plpType;
        visibleLimit = pageSize;
        chips.forEach((item) => { const selected = item === chip; item.classList.toggle('is-active', selected); item.setAttribute('aria-pressed', String(selected)); });
        scheduleRender();
    }));
    materialInputs.forEach((input) => input.addEventListener('change', () => { visibleLimit = pageSize; scheduleRender(); }));
    sortOptions.forEach((option) => option.addEventListener('click', () => {
        activeSort = option.dataset.plpSort;
        visibleLimit = pageSize;
        sortOptions.forEach((item) => item.setAttribute('aria-checked', String(item === option)));
        sortToggle.firstChild.textContent = `Sort: ${option.textContent.trim()} `;
        closeMenus(null, true);
        scheduleRender();
    }));
    loadMore.addEventListener('click', () => {
        visibleLimit += pageSize;
        loadMore.setAttribute('aria-disabled', 'true');
        loadMore.textContent = 'Loading...';
        scheduleRender();
    });
    menuPairs.forEach(([toggle, panel]) => toggle.addEventListener('click', () => openMenu(toggle, panel)));
    sortPanel.addEventListener('keydown', (event) => {
        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const currentIndex = sortOptions.indexOf(document.activeElement);
        let nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? sortOptions.length - 1 : currentIndex + (event.key === 'ArrowDown' ? 1 : -1);
        nextIndex = (nextIndex + sortOptions.length) % sortOptions.length;
        sortOptions[nextIndex].focus();
    });
    document.addEventListener('click', (event) => { if (!event.target.closest('.veylune-plp-menu')) closeMenus(); });
    root.addEventListener('keydown', (event) => { if (event.key === 'Escape') { event.preventDefault(); closeMenus(null, true); } });
    window.addEventListener('popstate', applyUrl);
    applyUrl();
});
