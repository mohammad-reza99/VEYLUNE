const initLivingIndexState = () => {
    const storageKey = 'veylune-living-index-selection-v1';
    const storageVersion = 1;
    const maxItems = 24;
    const maxAge = 180 * 24 * 60 * 60 * 1000;
    const allowedTypes = new Set(['Object', 'Room', 'Material', 'Style', 'Maker', 'Project']);

    const announce = (message) => {
        document.querySelectorAll('[data-vli-selection-status]').forEach((status) => {
            status.textContent = message;
        });
    };

    const safePageUrl = (rawUrl) => {
        try {
            const url = new URL(String(rawUrl || ''), window.location.origin);

            if (url.origin !== window.location.origin
                || !url.pathname.startsWith('/')
                || url.pathname.startsWith('/__veylune-')
                || url.pathname.startsWith('/checkout')
            ) {
                return null;
            }

            return `${url.pathname}${url.search}${url.hash}`;
        } catch (error) {
            return null;
        }
    };

    const safeAssetUrl = (rawUrl) => {
        try {
            const url = new URL(String(rawUrl || ''), window.location.origin);

            if (url.origin !== window.location.origin
                || (!url.pathname.includes('/assets/') && !url.pathname.includes('/media/'))
            ) {
                return null;
            }

            return url.href;
        } catch (error) {
            return null;
        }
    };

    const normalizeItem = (candidate) => {
        if (!candidate || typeof candidate !== 'object') return null;

        const key = String(candidate.key || '').trim().slice(0, 96);
        const type = String(candidate.type || '').trim().slice(0, 24);
        const title = String(candidate.title || '').trim().slice(0, 100);
        const description = String(candidate.description || '').trim().slice(0, 240);
        const url = safePageUrl(candidate.url);
        const asset = safeAssetUrl(candidate.asset);

        if (!/^[a-z0-9][a-z0-9:-]*$/i.test(key)
            || !allowedTypes.has(type)
            || !title
            || !description
            || !url
            || !asset
        ) {
            return null;
        }

        return {
            key,
            type,
            title,
            description,
            url,
            asset,
            savedAt: Number(candidate.savedAt) > 0 ? Number(candidate.savedAt) : Date.now(),
        };
    };

    const readItems = () => {
        try {
            const payload = JSON.parse(window.localStorage.getItem(storageKey) || 'null');

            if (!payload) return [];

            const updatedAt = Number(payload.updatedAt || 0);
            if (updatedAt > 0 && Date.now() - updatedAt > maxAge) {
                window.localStorage.removeItem(storageKey);
                return [];
            }

            const candidates = Array.isArray(payload) ? payload : payload.items;
            if (!Array.isArray(candidates)) return [];

            const keys = new Set();

            return candidates
                .map(normalizeItem)
                .filter((item) => {
                    if (!item || keys.has(item.key)) return false;
                    keys.add(item.key);
                    return true;
                })
                .slice(0, maxItems);
        } catch (error) {
            return [];
        }
    };

    const updateGlobalCount = (items) => {
        const count = items.length;

        document.querySelectorAll('[data-vli-selection-count]').forEach((element) => {
            element.textContent = String(count);
        });

        document.querySelectorAll('[data-vli-selection-count-label]').forEach((element) => {
            element.textContent = count === 1 ? 'path' : 'paths';
        });

        document.querySelectorAll('[data-vli-selection-link]').forEach((link) => {
            link.setAttribute('aria-label', `Open your selection, ${count} saved ${count === 1 ? 'path' : 'paths'}`);
            link.classList.toggle('has-selection', count > 0);
        });
    };

    const updateSaveButtons = (items) => {
        const savedKeys = new Set(items.map((item) => item.key));

        document.querySelectorAll('[data-vli-selection-add]').forEach((button) => {
            const selected = savedKeys.has(button.dataset.selectionKey || '');
            const label = button.querySelector('[data-vli-selection-label]');
            const icon = button.querySelector('[data-vli-selection-icon]');
            if (!button.dataset.selectionDefaultLabel) {
                button.dataset.selectionDefaultLabel = label?.textContent.trim() || button.textContent.trim();
            }
            button.setAttribute('aria-pressed', String(selected));
            button.setAttribute(
                'aria-label',
                `${selected ? 'Remove' : 'Save'} ${button.dataset.selectionTitle || 'this context'} ${selected ? 'from' : 'to'} selection`
            );

            if (label) {
                label.textContent = selected ? 'Saved' : button.dataset.selectionDefaultLabel;
                if (icon) icon.textContent = selected ? '\u2665' : '\u2661';
                return;
            }

            button.textContent = selected ? 'Saved context' : button.dataset.selectionDefaultLabel;
        });
    };

    const broadcast = (items, message = '') => {
        updateGlobalCount(items);
        updateSaveButtons(items);
        window.dispatchEvent(new CustomEvent('veylune:living-index-selection-change', {
            detail: { count: items.length, items },
        }));
        if (message) announce(message);
    };

    const writeItems = (items, message = '') => {
        const normalized = items.map(normalizeItem).filter(Boolean).slice(0, maxItems);

        try {
            if (normalized.length === 0) {
                window.localStorage.removeItem(storageKey);
            } else {
                window.localStorage.setItem(storageKey, JSON.stringify({
                    version: storageVersion,
                    updatedAt: Date.now(),
                    items: normalized,
                }));
            }
        } catch (error) {
            announce('This browser could not update the private selection.');
            return readItems();
        }

        broadcast(normalized, message);
        return normalized;
    };

    const itemFromButton = (button) => normalizeItem({
        key: button.dataset.selectionKey,
        type: button.dataset.selectionType,
        title: button.dataset.selectionTitle,
        description: button.dataset.selectionDescription,
        url: button.dataset.selectionUrl,
        asset: button.dataset.selectionAsset,
        savedAt: Date.now(),
    });

    const withReturnState = (rawUrl) => {
        const url = new URL(rawUrl, window.location.origin);
        url.searchParams.set('from', 'selection');
        return `${url.pathname}${url.search}${url.hash}`;
    };

    const selectionPage = document.querySelector('[data-veylune-living-index-selection]');

    const renderSelectionPage = (items) => {
        if (!selectionPage) return;

        const toolbar = selectionPage.querySelector('[data-vli-selection-toolbar]');
        const list = selectionPage.querySelector('[data-vli-selection-items]');
        const empty = selectionPage.querySelector('[data-vli-selection-empty]');
        const template = selectionPage.querySelector('[data-vli-selection-template]');
        const hasItems = items.length > 0;

        toolbar.hidden = !hasItems;
        list.hidden = !hasItems;
        empty.hidden = hasItems;
        list.replaceChildren();

        if (!hasItems || !(template instanceof HTMLTemplateElement)) return;

        items.forEach((item) => {
            const fragment = template.content.cloneNode(true);
            const card = fragment.querySelector('[data-vli-selection-item]');
            const image = fragment.querySelector('[data-vli-selection-item-image]');
            const mediaLink = fragment.querySelector('[data-vli-selection-item-link]');
            const openLink = fragment.querySelector('[data-vli-selection-item-open]');
            const removeButton = fragment.querySelector('[data-vli-selection-remove]');
            const itemUrl = withReturnState(item.url);

            card.dataset.selectionKey = item.key;
            image.src = item.asset;
            image.alt = `${item.title} context`;
            mediaLink.href = itemUrl;
            mediaLink.removeAttribute('tabindex');
            openLink.href = itemUrl;
            fragment.querySelector('[data-vli-selection-item-type]').textContent = item.type;
            fragment.querySelector('[data-vli-selection-item-title]').textContent = item.title;
            fragment.querySelector('[data-vli-selection-item-description]').textContent = item.description;
            removeButton.setAttribute('aria-label', `Remove ${item.title} from selection`);
            removeButton.addEventListener('click', () => {
                const nextItems = writeItems(
                    readItems().filter((candidate) => candidate.key !== item.key),
                    `${item.title} removed from your selection.`
                );
                renderSelectionPage(nextItems);
            });
            list.append(fragment);
        });
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-vli-selection-add]');
        if (!button) return;

        const item = itemFromButton(button);
        if (!item) {
            announce('This context could not be saved.');
            return;
        }

        const items = readItems();
        const existing = items.find((candidate) => candidate.key === item.key);

        if (existing) {
            const nextItems = writeItems(
                items.filter((candidate) => candidate.key !== item.key),
                `${item.title} removed from your selection.`
            );
            renderSelectionPage(nextItems);
            return;
        }

        if (items.length >= maxItems) {
            announce(`Your private selection is limited to ${maxItems} paths.`);
            return;
        }

        const nextItems = writeItems([item, ...items], `${item.title} saved to your selection.`);
        renderSelectionPage(nextItems);
    });

    const clearButton = selectionPage?.querySelector('[data-vli-selection-clear]');
    let clearConfirmationTimer = null;

    clearButton?.addEventListener('click', () => {
        if (clearButton.dataset.confirmClear !== 'true') {
            clearButton.dataset.confirmClear = 'true';
            clearButton.textContent = 'Confirm clear';
            announce('Press Confirm clear to remove every saved path.');
            window.clearTimeout(clearConfirmationTimer);
            clearConfirmationTimer = window.setTimeout(() => {
                clearButton.dataset.confirmClear = 'false';
                clearButton.textContent = 'Clear selection';
            }, 5000);
            return;
        }

        window.clearTimeout(clearConfirmationTimer);
        clearButton.dataset.confirmClear = 'false';
        clearButton.textContent = 'Clear selection';
        const nextItems = writeItems([], 'Your private selection is clear.');
        renderSelectionPage(nextItems);
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey) return;
        const items = readItems();
        updateGlobalCount(items);
        updateSaveButtons(items);
        renderSelectionPage(items);
    });

    window.addEventListener('pageshow', () => {
        const items = readItems();
        updateGlobalCount(items);
        updateSaveButtons(items);
        renderSelectionPage(items);
    });

    const initialItems = readItems();
    updateGlobalCount(initialItems);
    updateSaveButtons(initialItems);
    renderSelectionPage(initialItems);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLivingIndexState, { once: true });
} else {
    initLivingIndexState();
}
