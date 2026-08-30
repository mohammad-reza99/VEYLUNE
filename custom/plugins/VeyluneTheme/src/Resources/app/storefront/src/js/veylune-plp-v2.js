const normalize = (value = '') => value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

document.querySelectorAll('[data-veylune-plp]').forEach((root) => {
    const pageSize = 12;
    const grid = root.querySelector('[data-plp-grid]');
    const cards = Array.from(root.querySelectorAll('[data-plp-card]'));
    const chips = Array.from(root.querySelectorAll('[data-plp-type]'));
    const materialInputs = Array.from(root.querySelectorAll('[data-plp-material]'));
    const priceInputs = Array.from(root.querySelectorAll('[data-plp-price-range]'));
    const statusInputs = Array.from(root.querySelectorAll('[data-plp-status-filter]'));
    const filterInputs = [...materialInputs, ...priceInputs, ...statusInputs];
    const sortOptions = Array.from(root.querySelectorAll('[data-plp-sort]'));
    const filterToggle = root.querySelector('[data-plp-filter-toggle]');
    const filterPanel = root.querySelector('[data-plp-filter-panel]');
    const filterClearButtons = Array.from(root.querySelectorAll('[data-plp-filter-clear]'));
    const sortToggle = root.querySelector('[data-plp-sort-toggle]');
    const sortPanel = root.querySelector('[data-plp-sort-panel]');
    const count = root.querySelector('[data-plp-count]');
    const matchedCount = root.querySelector('[data-plp-matched-count]');
    const showing = root.querySelector('[data-plp-showing]');
    const empty = root.querySelector('[data-plp-empty]');
    const loadMore = root.querySelector('[data-plp-load-more]');
    const activeFilters = root.querySelector('[data-plp-active-filters]');
    const status = root.querySelector('[data-plp-status]');
    let activeType = 'all';
    let activeSort = 'featured';
    let visibleLimit = pageSize;
    let renderFrame = null;

    const menuPairs = [[filterToggle, filterPanel], [sortToggle, sortPanel]];
    const focusableSelector = 'button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const selectedValues = (inputs) => [...new Set(inputs.filter((input) => input.checked).map((input) => input.value))];
    const inputLabel = (inputs, value) => inputs.find((input) => input.value === value)?.nextElementSibling?.textContent?.trim() || value;

    const inputsForKind = (kind) => ({ material: materialInputs, price: priceInputs, status: statusInputs }[kind] || []);

    const trapPanelFocus = (event, panel) => {
        if (event.key !== 'Tab' || panel.hidden) return;
        const focusable = Array.from(panel.querySelectorAll(focusableSelector)).filter((element) => element.offsetParent !== null);
        if (!focusable.length) {
            event.preventDefault();
            return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    const appendActiveFilter = (kind, value, label) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.plpRemoveFilter = kind;
        button.dataset.plpFilterValue = value;
        button.textContent = `${label} x`;
        activeFilters.append(button);
    };

    const renderActiveFilters = (materials, prices, statuses) => {
        activeFilters.replaceChildren();
        if (activeType !== 'all') {
            const chip = chips.find((item) => item.dataset.plpType === activeType);
            appendActiveFilter('type', activeType, chip?.textContent?.trim() || activeType);
        }
        materials.forEach((value) => appendActiveFilter('material', value, inputLabel(materialInputs, value)));
        prices.forEach((value) => appendActiveFilter('price', value, inputLabel(priceInputs, value)));
        statuses.forEach((value) => appendActiveFilter('status', value, inputLabel(statusInputs, value)));
        const activeCount = (activeType === 'all' ? 0 : 1) + materials.length + prices.length + statuses.length;
        activeFilters.hidden = activeCount === 0;
        filterClearButtons.forEach((button) => { button.disabled = activeCount === 0; });
        filterToggle.firstChild.textContent = activeCount ? `Filter (${activeCount}) ` : 'Filter ';
    };

    const matchesType = (card) => {
        if (activeType === 'all') return true;
        const type = normalize(card.dataset.plpType);
        if (activeType === 'chairs') return type.includes('chair');
        if (activeType === 'tables') return type.includes('table');
        if (activeType === 'benches-stools') return type.includes('bench') || type.includes('stool');
        return type.includes(activeType.replace(/s$/, ''));
    };

    const matchesPrice = (card, ranges) => {
        if (!ranges.length) return true;
        const price = Number(card.dataset.plpPrice);
        return ranges.some((range) => {
            if (range === 'under_500') return price < 500;
            if (range === '500_1000') return price >= 500 && price < 1000;
            if (range === '1000_2000') return price >= 1000 && price < 2000;
            return range === '2000_plus' && price >= 2000;
        });
    };

    const updateUrl = (materials, prices, statuses) => {
        const url = new URL(window.location.href);
        activeType === 'all' ? url.searchParams.delete('type') : url.searchParams.set('type', activeType);
        activeSort === 'featured' ? url.searchParams.delete('sort') : url.searchParams.set('sort', activeSort);
        materials.length ? url.searchParams.set('materials', materials.join(',')) : url.searchParams.delete('materials');
        prices.length ? url.searchParams.set('prices', prices.join(',')) : url.searchParams.delete('prices');
        statuses.length ? url.searchParams.set('statuses', statuses.join(',')) : url.searchParams.delete('statuses');
        visibleLimit > pageSize ? url.searchParams.set('view', String(visibleLimit)) : url.searchParams.delete('view');
        if (url.href !== window.location.href) {
            window.history.pushState({}, '', url);
        }
    };

    const render = (syncUrlState = true) => {
        const materials = selectedValues(materialInputs);
        const prices = selectedValues(priceInputs);
        const statuses = selectedValues(statusInputs);
        const filtered = cards.filter((card) => matchesType(card)
            && (!materials.length || materials.some((material) => (card.dataset.plpMaterials || '').split(',').includes(material)))
            && matchesPrice(card, prices)
            && (!statuses.length || statuses.includes(card.dataset.plpStatus)));
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
        matchedCount.textContent = String(filtered.length);
        showing.textContent = String(displayed.length);
        empty.hidden = filtered.length !== 0;
        renderActiveFilters(materials, prices, statuses);
        status.textContent = `${filtered.length} pieces match the current filters. ${displayed.length} shown.`;
        loadMore.hidden = displayed.length >= filtered.length;
        root.style.setProperty('--veylune-plp-progress', filtered.length ? String(displayed.length / filtered.length) : '0');
        grid.setAttribute('aria-busy', 'false');
        grid.classList.remove('is-updating');
        loadMore.removeAttribute('aria-disabled');
        loadMore.textContent = 'Load more';
        if (syncUrlState) updateUrl(materials, prices, statuses);
    };

    const scheduleRender = (syncUrlState = true) => {
        if (renderFrame) window.cancelAnimationFrame(renderFrame);
        grid.setAttribute('aria-busy', 'true');
        grid.classList.add('is-updating');
        renderFrame = window.requestAnimationFrame(() => {
            render(syncUrlState);
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
        if (opening) window.requestAnimationFrame(() => panel.querySelector('input, button')?.focus());
    };

    const applyUrl = () => {
        const params = new URL(window.location.href).searchParams;
        const requestedType = params.get('type') || 'all';
        activeType = chips.some((chip) => chip.dataset.plpType === requestedType) ? requestedType : 'all';
        activeSort = sortOptions.some((option) => option.dataset.plpSort === params.get('sort')) ? params.get('sort') : 'featured';
        const requested = {
            material: new Set((params.get('materials') || '').split(',').filter(Boolean)),
            price: new Set((params.get('prices') || '').split(',').filter(Boolean)),
            status: new Set((params.get('statuses') || '').split(',').filter(Boolean)),
        };
        visibleLimit = Math.max(pageSize, Number(params.get('view')) || pageSize);
        chips.forEach((chip) => { const selected = chip.dataset.plpType === activeType; chip.classList.toggle('is-active', selected); chip.setAttribute('aria-pressed', String(selected)); });
        materialInputs.forEach((input) => { input.checked = requested.material.has(input.value); });
        priceInputs.forEach((input) => { input.checked = requested.price.has(input.value); });
        statusInputs.forEach((input) => { input.checked = requested.status.has(input.value); });
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
    filterInputs.forEach((input) => input.addEventListener('change', () => {
        const selector = input.hasAttribute('data-plp-material') ? '[data-plp-material]' : input.hasAttribute('data-plp-price-range') ? '[data-plp-price-range]' : '[data-plp-status-filter]';
        root.querySelectorAll(selector).forEach((peer) => { if (peer.value === input.value) peer.checked = input.checked; });
        visibleLimit = pageSize;
        scheduleRender();
    }));
    filterClearButtons.forEach((button) => button.addEventListener('click', () => {
        filterInputs.forEach((input) => { input.checked = false; });
        activeType = 'all';
        chips.forEach((chip) => { const selected = chip.dataset.plpType === 'all'; chip.classList.toggle('is-active', selected); chip.setAttribute('aria-pressed', String(selected)); });
        visibleLimit = pageSize;
        scheduleRender();
        if (button.closest('[data-plp-filter-panel]')) filterToggle.focus();
        closeMenus();
    }));
    activeFilters.addEventListener('click', (event) => {
        const button = event.target.closest('[data-plp-remove-filter]');
        if (!button) return;
        const kind = button.dataset.plpRemoveFilter;
        const value = button.dataset.plpFilterValue;
        if (kind === 'type') {
            activeType = 'all';
            chips.forEach((chip) => { const selected = chip.dataset.plpType === 'all'; chip.classList.toggle('is-active', selected); chip.setAttribute('aria-pressed', String(selected)); });
        } else {
            inputsForKind(kind).filter((input) => input.value === value).forEach((input) => { input.checked = false; });
        }
        visibleLimit = pageSize;
        scheduleRender();
        if (filterToggle.offsetParent !== null) filterToggle.focus();
    });
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
        const option = event.target.closest('[data-plp-sort]');
        if (option && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            option.click();
            return;
        }
        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const currentIndex = sortOptions.indexOf(document.activeElement);
        let nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? sortOptions.length - 1 : currentIndex + (event.key === 'ArrowDown' ? 1 : -1);
        nextIndex = (nextIndex + sortOptions.length) % sortOptions.length;
        sortOptions[nextIndex].focus();
    });
    filterPanel.addEventListener('keydown', (event) => trapPanelFocus(event, filterPanel));
    sortPanel.addEventListener('keydown', (event) => trapPanelFocus(event, sortPanel));
    document.addEventListener('click', (event) => { if (!event.target.closest('.veylune-plp-menu')) closeMenus(); });
    root.addEventListener('keydown', (event) => { if (event.key === 'Escape') { event.preventDefault(); closeMenus(null, true); } });
    window.addEventListener('popstate', applyUrl);
    applyUrl();
});
