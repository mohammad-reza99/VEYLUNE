export const selectionStorageKey = 'veylune-private-selection-v2';
export const legacySelectionStorageKey = 'veylune-private-selection-v1';

const maxAge = 30 * 24 * 60 * 60 * 1000;
const maxItems = 24;
const maxQuantity = 10;

export const clampSelectionQuantity = (value) => Math.min(maxQuantity, Math.max(1, Number.parseInt(value, 10) || 1));

const normalizeMaterial = (value) => String(value || 'Material pending').replace(/\s+/g, ' ').trim().slice(0, 60);
const lineIdFor = (productId, material) => `${productId}::${normalizeMaterial(material).toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;

const normalizeItem = (item) => {
    const productId = String(item?.productId || '').toUpperCase().trim();
    const productName = String(item?.productName || '').replace(/\s+/g, ' ').trim().slice(0, 100);
    const material = normalizeMaterial(item?.material);
    const unitPrice = Number(item?.unitPrice);

    if (!/^[A-Z][0-9]{2}$/.test(productId) || !productName || !Number.isFinite(unitPrice) || unitPrice <= 0) return null;

    return {
        lineId: lineIdFor(productId, material),
        productId,
        productName,
        material,
        quantity: clampSelectionQuantity(item?.quantity),
        unitPrice,
    };
};

const emptyState = () => ({
    version: 2,
    items: [],
    postalCode: '',
    service: 'studio',
    activeLineId: '',
    updatedAt: Date.now(),
});

const normalizeState = (state) => {
    const seen = new Set();
    const items = (Array.isArray(state?.items) ? state.items : [])
        .map(normalizeItem)
        .filter((item) => item && !seen.has(item.lineId) && seen.add(item.lineId))
        .slice(0, maxItems);
    const requestedActive = String(state?.activeLineId || '');

    return {
        version: 2,
        items,
        postalCode: String(state?.postalCode || '').trim().toUpperCase().slice(0, 10),
        service: String(state?.service || 'studio').trim().slice(0, 20),
        activeLineId: items.some((item) => item.lineId === requestedActive) ? requestedActive : (items[0]?.lineId || ''),
        updatedAt: Number(state?.updatedAt) || Date.now(),
    };
};

const legacyToState = (legacy) => {
    const item = normalizeItem(legacy);
    if (!item) return emptyState();

    return normalizeState({
        items: [item],
        postalCode: legacy.postalCode,
        service: legacy.service,
        activeLineId: item.lineId,
        updatedAt: legacy.updatedAt,
    });
};

const removeStoredSelection = () => {
    window.localStorage.removeItem(selectionStorageKey);
    window.localStorage.removeItem(legacySelectionStorageKey);
};

export const writeSelectionState = (candidate) => {
    const state = normalizeState({ ...candidate, updatedAt: Date.now() });
    try {
        if (!state.items.length) {
            removeStoredSelection();
            return state;
        }

        window.localStorage.setItem(selectionStorageKey, JSON.stringify(state));
        const active = state.items.find((item) => item.lineId === state.activeLineId) || state.items[0];
        window.localStorage.setItem(legacySelectionStorageKey, JSON.stringify({
            ...active,
            postalCode: state.postalCode,
            service: state.service,
            updatedAt: state.updatedAt,
        }));
    } catch (error) {
        // The current page can still use the returned in-memory state.
    }
    return state;
};

export const readSelectionState = () => {
    try {
        const stored = JSON.parse(window.localStorage.getItem(selectionStorageKey) || 'null');
        const expired = Number(stored?.updatedAt) > 0 && Date.now() - Number(stored.updatedAt) > maxAge;
        if (stored?.version === 2 && !expired) {
            const state = normalizeState(stored);
            if (state.items.length) return state;
        }
        if (stored) window.localStorage.removeItem(selectionStorageKey);

        const legacy = JSON.parse(window.localStorage.getItem(legacySelectionStorageKey) || 'null');
        const legacyExpired = Number(legacy?.updatedAt) > 0 && Date.now() - Number(legacy.updatedAt) > maxAge;
        if (!legacyExpired) {
            const migrated = legacyToState(legacy);
            if (migrated.items.length) return writeSelectionState(migrated);
        }
        if (legacy) window.localStorage.removeItem(legacySelectionStorageKey);
    } catch (error) {
        try { removeStoredSelection(); } catch (storageError) { /* Storage is unavailable. */ }
    }
    return emptyState();
};

export const upsertSelectionItem = (candidateState, candidateItem) => {
    const state = normalizeState(candidateState);
    const item = normalizeItem(candidateItem);
    if (!item) return { state, item: null };

    const existing = state.items.find((entry) => entry.lineId === item.lineId);
    if (existing) {
        existing.quantity = clampSelectionQuantity(existing.quantity + item.quantity);
        existing.unitPrice = item.unitPrice;
        existing.productName = item.productName;
        state.activeLineId = existing.lineId;
        return { state, item: existing };
    }

    state.items.push(item);
    state.items = state.items.slice(0, maxItems);
    state.activeLineId = item.lineId;
    return { state, item };
};

export const selectionQuantity = (state) => state.items.reduce((sum, item) => sum + clampSelectionQuantity(item.quantity), 0);
export const selectionSubtotal = (state) => state.items.reduce((sum, item) => sum + item.unitPrice * clampSelectionQuantity(item.quantity), 0);

export const emitSelectionChange = (state) => {
    window.dispatchEvent(new CustomEvent('veylune:selection-change', {
        detail: { quantity: selectionQuantity(state), lines: state.items.length },
    }));
};
