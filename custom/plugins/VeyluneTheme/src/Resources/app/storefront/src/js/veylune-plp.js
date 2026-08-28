const roots = document.querySelectorAll('[data-veylune-plp]');

const normalize = (value = '') => value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

roots.forEach((root) => {
    const grid = root.querySelector('[data-plp-grid]');
    const cards = Array.from(root.querySelectorAll('[data-plp-card]'));
    const chips = Array.from(root.querySelectorAll('[data-plp-type]'));
    const filterToggle = root.querySelector('[data-plp-filter-toggle]');
    const filterPanel = root.querySelector('[data-plp-filter-panel]');
    const sortToggle = root.querySelector('[data-plp-sort-toggle]');
    const sortPanel = root.querySelector('[data-plp-sort-panel]');
    const sortOptions = Array.from(root.querySelectorAll('[data-plp-sort]'));
    const materialInputs = Array.from(root.querySelectorAll('[data-plp-material]'));
    const count = root.querySelector('[data-plp-count]');
    const empty = root.querySelector('[data-plp-empty]');
    let activeType = 'all';
    let activeSort = 'featured';

    const matchesType = (card) => {
        if (activeType === 'all') return true;
        const type = normalize(card.dataset.plpType);
        if (activeType === 'chairs') return type.includes('chair');
        if (activeType === 'tables') return type.includes('table');
        if (activeType === 'benches-stools') return type.includes('bench') || type.includes('stool');
        return type.includes(activeType.replace(/s$/, ''));
    };

    const render = () => {
        const materials = materialInputs.filter((input) => input.checked).map((input) => input.value);
        const visible = cards.filter((card) => matchesType(card) && (!materials.length || materials.includes(card.dataset.plpMaterial)));
        const sorted = [...visible].sort((a, b) => {
            if (activeSort === 'price-low') return Number(a.dataset.plpPrice) - Number(b.dataset.plpPrice);
            if (activeSort === 'price-high') return Number(b.dataset.plpPrice) - Number(a.dataset.plpPrice);
            if (activeSort === 'name') return a.dataset.plpName.localeCompare(b.dataset.plpName);
            return Number(a.dataset.plpIndex) - Number(b.dataset.plpIndex);
        });

        cards.forEach((card) => { card.hidden = !visible.includes(card); });
        sorted.forEach((card) => grid.append(card));
        count.textContent = String(visible.length);
        empty.hidden = visible.length !== 0;
    };

    const closeMenus = (except = null) => {
        [[filterToggle, filterPanel], [sortToggle, sortPanel]].forEach(([toggle, panel]) => {
            if (panel !== except) {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    };

    chips.forEach((chip) => chip.addEventListener('click', () => {
        activeType = chip.dataset.plpType;
        chips.forEach((item) => {
            const selected = item === chip;
            item.classList.toggle('is-active', selected);
            item.setAttribute('aria-pressed', String(selected));
        });
        render();
    }));

    [[filterToggle, filterPanel], [sortToggle, sortPanel]].forEach(([toggle, panel]) => {
        toggle.addEventListener('click', () => {
            const opening = panel.hidden;
            closeMenus(opening ? panel : null);
            panel.hidden = !opening;
            toggle.setAttribute('aria-expanded', String(opening));
        });
    });

    materialInputs.forEach((input) => input.addEventListener('change', render));
    sortOptions.forEach((option) => option.addEventListener('click', () => {
        activeSort = option.dataset.plpSort;
        sortOptions.forEach((item) => item.setAttribute('aria-checked', String(item === option)));
        sortToggle.firstChild.textContent = `Sort: ${option.textContent.trim()} `;
        closeMenus();
        render();
    }));

    document.addEventListener('click', (event) => { if (!root.contains(event.target)) closeMenus(); });
    root.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeMenus(); });
    render();
});
