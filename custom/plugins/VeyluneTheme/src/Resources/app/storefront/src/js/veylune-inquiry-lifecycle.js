const initVeyluneInquiryLifecycle = () => {
    const page = document.querySelector('[data-vli-project-brief-page]');
    const form = page?.querySelector('form[action="/form/contact"]');
    const status = form?.querySelector('[data-vli-inquiry-status]');
    const cmsBlock = form?.closest('.cms-block');

    if (!page || !form || !status || !cmsBlock) return;

    const submitButton = form.querySelector('button[type="submit"]');
    const validationControls = [...form.querySelectorAll('[data-validation]')];

    const controlLabel = (control) => {
        const label = control.labels?.[0]?.textContent || control.name || 'Required field';
        return label.replace(/\s*\*\s*$/, '').replace(/\s+/g, ' ').trim();
    };

    const validationIssues = () => validationControls.reduce((issues, control) => {
        const rules = String(control.dataset.validation || '').split(',');
        const value = String(control.value || '').trim();
        let message = '';

        if (rules.includes('required') && !value) {
            message = 'Add this required detail.';
        } else if (rules.includes('email') && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            message = 'Use a complete email address.';
        }

        if (!message) return issues;

        issues.push({
            control,
            id: control.id,
            label: controlLabel(control),
            message,
        });
        return issues;
    }, []);

    const setStatus = (state, title, description = '', issues = []) => {
        const fragment = document.createDocumentFragment();
        const heading = document.createElement('strong');
        heading.textContent = title;
        fragment.append(heading);

        if (description) {
            const copy = document.createElement('p');
            copy.textContent = description;
            fragment.append(copy);
        }

        if (issues.length > 0) {
            const list = document.createElement('ul');
            issues.forEach((issue) => {
                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = `#${issue.id}`;
                link.textContent = `${issue.label}: ${issue.message}`;
                item.append(link);
                list.append(item);
            });
            fragment.append(list);
        }

        status.replaceChildren(fragment);
        status.dataset.vliInquiryState = state;
        status.hidden = false;
        status.setAttribute('role', state === 'error' ? 'alert' : 'status');
    };

    const clearStatus = () => {
        status.hidden = true;
        status.removeAttribute('data-vli-inquiry-state');
        status.removeAttribute('role');
        status.replaceChildren();
    };

    const showValidationSummary = (focusSummary = false) => {
        const issues = validationIssues();

        validationControls.forEach((control) => {
            const invalid = issues.some((issue) => issue.control === control);
            if (invalid) control.setAttribute('aria-invalid', 'true');
        });

        if (issues.length === 0) {
            clearStatus();
            return false;
        }

        setStatus(
            'error',
            `${issues.length} ${issues.length === 1 ? 'detail needs' : 'details need'} your attention.`,
            'Use the links below to move directly to each field. Your brief has not been sent.',
            issues
        );

        if (focusSummary) {
            window.setTimeout(() => status.focus({ preventScroll: false }), 0);
        }

        return true;
    };

    const enhanceServerAlert = (alert) => {
        if (alert.dataset.vliInquiryEnhanced === 'true') return;
        alert.dataset.vliInquiryEnhanced = 'true';
        alert.classList.add('vli-inquiry-server-alert');
        alert.setAttribute('role', 'alert');
        alert.setAttribute('tabindex', '-1');
        form.classList.remove('vli-inquiry-form--sending');
        form.removeAttribute('aria-busy');
        submitButton?.removeAttribute('aria-disabled');
        setStatus(
            'error',
            'The studio could not accept this brief yet.',
            'Review the message below, correct the requested details, and try again. Nothing was confirmed.'
        );
        alert.focus({ preventScroll: false });
    };

    const enhanceConfirmation = (confirmation) => {
        if (confirmation.dataset.vliInquiryEnhanced === 'true') return;
        confirmation.dataset.vliInquiryEnhanced = 'true';
        confirmation.classList.add('vli-inquiry-confirmation');
        confirmation.setAttribute('role', 'status');
        confirmation.setAttribute('aria-live', 'polite');
        confirmation.setAttribute('tabindex', '-1');

        const originalMessage = document.createElement('div');
        originalMessage.className = 'vli-inquiry-confirmation__message';
        while (confirmation.firstChild) originalMessage.append(confirmation.firstChild);

        const icon = document.createElement('span');
        icon.className = 'vli-inquiry-confirmation__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '\u2713';

        const eyebrow = document.createElement('p');
        eyebrow.className = 'vli-inquiry-confirmation__eyebrow';
        eyebrow.textContent = 'Project brief received';

        const title = document.createElement('h2');
        title.textContent = 'Your brief has reached the studio review queue.';

        const copy = document.createElement('p');
        copy.textContent = 'The context you reviewed was sent through the Veylune inquiry form.';

        const boundary = document.createElement('p');
        boundary.className = 'vli-inquiry-confirmation__boundary';
        boundary.textContent = 'This confirms receipt only. Service scope, availability, pricing, delivery, and installation remain unconfirmed until agreed separately.';

        const actions = document.createElement('div');
        actions.className = 'vli-inquiry-confirmation__actions';
        const selectionLink = document.createElement('a');
        selectionLink.href = '/selection';
        selectionLink.textContent = 'Return to My Selection';
        const consultationLink = document.createElement('a');
        consultationLink.href = '/private-consultation';
        consultationLink.textContent = 'Review consultation process';
        actions.append(selectionLink, consultationLink);

        confirmation.append(icon, eyebrow, title, copy, originalMessage, boundary, actions);
        confirmation.focus({ preventScroll: false });
    };

    form.addEventListener('submit', () => {
        if (showValidationSummary(true)) return;

        setStatus(
            'sending',
            'Sending your project brief...',
            'Keep this page open while the local storefront confirms receipt.'
        );
        form.classList.add('vli-inquiry-form--sending');
        form.setAttribute('aria-busy', 'true');
        submitButton?.setAttribute('aria-disabled', 'true');
    }, true);

    validationControls.forEach((control) => {
        const update = () => {
            if (status.dataset.vliInquiryState !== 'error') return;
            showValidationSummary(false);
        };
        control.addEventListener('input', update);
        control.addEventListener('change', update);
    });

    const observer = new MutationObserver(() => {
        const confirmation = cmsBlock.querySelector('.confirm-message');
        if (confirmation) enhanceConfirmation(confirmation);

        const serverAlert = cmsBlock.querySelector('.confirm-alert');
        if (serverAlert) enhanceServerAlert(serverAlert);
    });

    observer.observe(cmsBlock, { childList: true, subtree: true });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneInquiryLifecycle, { once: true });
} else {
    initVeyluneInquiryLifecycle();
}
