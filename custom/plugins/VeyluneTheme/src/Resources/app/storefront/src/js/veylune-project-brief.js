const initVeyluneProjectBrief = () => {
    const page = document.querySelector('[data-vli-project-brief-page]');
    const intake = page?.querySelector('[data-vli-project-intake]');
    const form = intake?.closest('form');

    if (!page || !intake || !form) return;

    const storageKey = 'veylune-living-index-selection-v1';
    const allowedTypes = new Set(['Object', 'Room', 'Material', 'Style', 'Maker', 'Project']);
    const source = String(page.dataset.vliProjectSource || 'direct').slice(0, 24);
    const query = String(page.dataset.vliProjectQuery || '').trim().slice(0, 100);
    const objectReference = String(page.dataset.vliProjectObject || '').trim().slice(0, 64);
    const subject = form.querySelector('[name="subject"]');
    const comment = form.querySelector('[name="comment"]');
    const preview = intake.querySelector('[data-vli-brief-preview]');
    const selectionPanel = intake.querySelector('[data-vli-brief-selection]');
    const selectionList = intake.querySelector('[data-vli-brief-selection-list]');
    const selectionCount = intake.querySelector('[data-vli-brief-selection-count]');
    const includeSelection = intake.querySelector('[data-vli-brief-include-selection]');
    const fieldControls = [...intake.querySelectorAll('[data-vli-brief-field]')];
    const startMarker = '[Veylune project brief]';
    const endMarker = '[/Veylune project brief]';
    let selectionItems = [];

    const safeText = (value, maximumLength) => String(value || '')
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, maximumLength);

    const safePath = (value) => {
        try {
            const url = new URL(String(value || ''), window.location.origin);
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

    const readSelection = () => {
        if (source !== 'selection') return [];

        try {
            const payload = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
            const candidates = Array.isArray(payload) ? payload : payload?.items;
            if (!Array.isArray(candidates)) return [];

            const keys = new Set();

            return candidates.map((candidate) => {
                const key = safeText(candidate?.key, 96);
                const type = safeText(candidate?.type, 24);
                const title = safeText(candidate?.title, 100);
                const url = safePath(candidate?.url);

                if (!/^[a-z0-9][a-z0-9:-]*$/i.test(key)
                    || keys.has(key)
                    || !allowedTypes.has(type)
                    || !title
                    || !url
                ) {
                    return null;
                }

                keys.add(key);
                return { key, type, title, url };
            }).filter(Boolean).slice(0, 12);
        } catch (error) {
            return [];
        }
    };

    const fieldValue = (name) => {
        const control = intake.querySelector(`[data-vli-brief-field="${name}"]`);
        return safeText(control?.value, name === 'location' ? 100 : 80) || 'Not specified';
    };

    const stripGeneratedBrief = (value) => {
        const text = String(value || '');
        const markerPosition = text.indexOf(endMarker);

        if (!text.startsWith(startMarker) || markerPosition < 0) return text.trim();

        return text
            .slice(markerPosition + endMarker.length)
            .replace(/^\s*Client notes:\s*/i, '')
            .trim();
    };

    const selectionSummary = () => {
        if (source !== 'selection') return 'Not part of this entry path';
        if (selectionItems.length === 0) return 'No valid saved paths found';
        if (!includeSelection?.checked) return `${selectionItems.length} saved paths found; not included`;

        return `${selectionItems.length} saved paths included by client`;
    };

    const briefRows = () => {
        const rows = [
            ['Inquiry', fieldValue('intent')],
            ['Primary space', fieldValue('space')],
            ['Project stage', fieldValue('stage')],
            ['Preferred timing', fieldValue('timing')],
            ['Project location', fieldValue('location')],
            ['Investment frame', fieldValue('investment')],
            ['Saved selection', selectionSummary()],
        ];

        if (query) rows.push(['Discovery context', query]);
        if (objectReference) rows.push(['Object reference', objectReference]);

        return rows;
    };

    const renderPreview = () => {
        if (!preview) return;

        const fragment = document.createDocumentFragment();
        briefRows().forEach(([term, description]) => {
            const row = document.createElement('div');
            const dt = document.createElement('dt');
            const dd = document.createElement('dd');
            dt.textContent = term;
            dd.textContent = description;
            row.append(dt, dd);
            fragment.append(row);
        });
        preview.replaceChildren(fragment);
    };

    const renderSelection = () => {
        selectionItems = readSelection();
        const hasSelection = selectionItems.length > 0;

        if (selectionPanel) selectionPanel.hidden = !hasSelection;
        if (selectionCount) selectionCount.textContent = String(selectionItems.length);
        if (includeSelection && !hasSelection) includeSelection.checked = false;
        if (!selectionList) return;

        const fragment = document.createDocumentFragment();
        selectionItems.forEach((item) => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            const type = document.createElement('span');
            link.href = item.url;
            link.textContent = item.title;
            type.textContent = item.type;
            listItem.append(link, type);
            fragment.append(listItem);
        });
        selectionList.replaceChildren(fragment);
    };

    const composeBrief = () => {
        const lines = [startMarker];
        briefRows().forEach(([term, description]) => lines.push(`${term}: ${description}`));

        if (includeSelection?.checked && selectionItems.length > 0) {
            lines.push('', 'Included saved paths:');
            selectionItems.forEach((item, index) => {
                lines.push(`${index + 1}. ${item.type} — ${item.title} — ${item.url}`);
            });
        }

        lines.push(endMarker);
        return lines.join('\n');
    };

    const updateMessage = () => {
        renderPreview();

        if (!(comment instanceof HTMLTextAreaElement)) return;
        const notes = stripGeneratedBrief(comment.value);
        comment.value = `${composeBrief()}\n\nClient notes:\n${notes}`;
    };

    const updateGeneratedSubject = () => {
        if (!(subject instanceof HTMLInputElement)) return;
        if (subject.value.trim() && subject.dataset.vliGeneratedSubject !== 'true') return;

        const space = fieldValue('space');
        const intent = fieldValue('intent');
        subject.value = `Project inquiry — ${space === 'Not decided' ? intent : space}`;
        subject.dataset.vliGeneratedSubject = 'true';
    };

    const initialIntent = intake.querySelector('[data-vli-brief-field="intent"]');
    if (initialIntent instanceof HTMLSelectElement) {
        if (source === 'trade') initialIntent.value = 'Trade project support';
        if (source === 'object') initialIntent.value = 'Furniture sourcing';
        if (source === 'direct') initialIntent.value = 'General studio inquiry';
    }

    renderSelection();
    updateGeneratedSubject();
    updateMessage();

    fieldControls.forEach((control) => {
        control.addEventListener('change', () => {
            updateGeneratedSubject();
            updateMessage();
        });
        if (control instanceof HTMLInputElement) {
            control.addEventListener('input', renderPreview);
        }
    });

    includeSelection?.addEventListener('change', updateMessage);
    subject?.addEventListener('input', () => {
        subject.dataset.vliGeneratedSubject = 'false';
    });
    form.addEventListener('submit', updateMessage, true);

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey || source !== 'selection') return;
        renderSelection();
        updateMessage();
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneProjectBrief, { once: true });
} else {
    initVeyluneProjectBrief();
}
