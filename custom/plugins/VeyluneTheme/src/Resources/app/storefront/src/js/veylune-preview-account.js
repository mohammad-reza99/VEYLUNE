document.querySelectorAll('[data-veylune-account-preview]').forEach((root) => {
    const tabs = Array.from(root.querySelectorAll('[data-account-tab]'));
    const panels = Array.from(root.querySelectorAll('[data-account-panel]'));
    const loginForm = root.querySelector('[data-account-login]');
    const registerForm = root.querySelector('[data-account-register]');
    const loginStatus = root.querySelector('[data-account-login-status]');
    const registerStatus = root.querySelector('[data-account-register-status]');
    const forgot = root.querySelector('[data-account-forgot]');
    const recoveryDialog = root.querySelector('[data-account-recovery-dialog]');
    const recoveryForm = root.querySelector('[data-account-recovery-form]');
    const recoveryClose = root.querySelector('[data-account-recovery-close]');
    const recoveryStatus = root.querySelector('[data-account-recovery-status]');
    const projectEmpty = root.querySelector('[data-account-project-empty]');
    const projectList = root.querySelector('[data-account-project-list]');
    const projectCount = root.querySelector('[data-account-project-count]');
    const projectFilter = root.querySelector('[data-account-project-filter]');
    const projectSort = root.querySelector('[data-account-project-sort]');
    const projectSave = root.querySelector('[data-account-project-save]');
    const projectNotice = root.querySelector('[data-account-project-notice]');
    const projectDialog = root.querySelector('[data-account-project-dialog]');
    const projectRenameForm = root.querySelector('[data-account-project-rename]');
    const projectDialogClose = root.querySelector('[data-account-project-dialog-close]');
    const projectDialogStatus = root.querySelector('[data-account-project-dialog-status]');
    const addressDialog = root.querySelector('[data-account-address-dialog]');
    const addressOpen = Array.from(root.querySelectorAll('[data-account-address-open]'));
    const addressClose = root.querySelector('[data-account-address-close]');
    const addressList = root.querySelector('[data-account-address-list]');
    const addressEmpty = root.querySelector('[data-account-address-empty]');
    const addressCount = root.querySelector('[data-account-address-count]');
    const addressNotice = root.querySelector('[data-account-address-notice]');
    const addressForm = root.querySelector('[data-account-address-form]');
    const addressCancel = root.querySelector('[data-account-address-cancel]');
    const addressSubmit = root.querySelector('[data-account-address-submit]');
    const archiveDialog = root.querySelector('[data-account-archive-dialog]');
    const archiveOpen = root.querySelector('[data-account-archive-open]');
    const archiveClose = root.querySelector('[data-account-archive-close]');
    const archiveList = root.querySelector('[data-account-archive-list]');
    const archiveEmpty = root.querySelector('[data-account-archive-empty]');
    const archiveCount = root.querySelector('[data-account-archive-count]');
    const archiveNotice = root.querySelector('[data-account-archive-notice]');
    const ordersDialog = root.querySelector('[data-account-orders-dialog]');
    const ordersOpen = Array.from(root.querySelectorAll('[data-account-orders-open]'));
    const ordersClose = root.querySelector('[data-account-orders-close]');
    const ordersList = root.querySelector('[data-account-orders-list]');
    const ordersEmpty = root.querySelector('[data-account-orders-empty]');
    const ordersEmptyTitle = root.querySelector('[data-account-orders-empty-title]');
    const ordersEmptyCopy = root.querySelector('[data-account-orders-empty-copy]');
    const ordersCount = root.querySelector('[data-account-orders-count]');
    const ordersNotice = root.querySelector('[data-account-orders-notice]');
    const ordersSearch = root.querySelector('[data-account-orders-search]');
    const ordersFilter = root.querySelector('[data-account-orders-filter]');
    const settingsDialog = root.querySelector('[data-account-settings-dialog]');
    const settingsOpen = Array.from(root.querySelectorAll('[data-account-settings-open]'));
    const settingsClose = root.querySelector('[data-account-settings-close]');
    const settingsForm = root.querySelector('[data-account-settings-form]');
    const settingsStatus = root.querySelector('[data-account-settings-status]');
    const settingsSummary = root.querySelector('[data-account-settings-summary-copy]');
    const readinessDialog = root.querySelector('[data-account-readiness-dialog]');
    const readinessOpen = Array.from(root.querySelectorAll('[data-account-readiness-open]'));
    const readinessClose = root.querySelector('[data-account-readiness-close]');
    const readinessList = root.querySelector('[data-account-readiness-list]');
    const readinessScore = root.querySelector('[data-account-readiness-score]');
    const readinessSummary = root.querySelector('[data-account-readiness-summary-copy]');
    const selectionStorageKey = 'veylune-private-selection-v1';
    const projectsStorageKey = 'veylune-saved-projects-v1';
    const addressesStorageKey = 'veylune-address-book-v1';
    const reviewsStorageKey = 'veylune-checkout-reviews-v1';
    const settingsStorageKey = 'veylune-profile-settings-v1';

    const selectTab = (name, focus = false) => {
        tabs.forEach((tab) => {
            const selected = tab.dataset.accountTab === name;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            if (selected && focus) tab.focus();
        });
        panels.forEach((panel) => {
            panel.hidden = panel.dataset.accountPanel !== name;
        });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab.dataset.accountTab));
        tab.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0
                : event.key === 'End' ? tabs.length - 1
                    : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            selectTab(tabs[next].dataset.accountTab, true);
        });
    });

    const validate = (form) => {
        const fields = Array.from(form.querySelectorAll('[required]'));
        const invalid = fields.filter((field) => {
            const value = field.type === 'checkbox' ? field.checked : field.value.trim();
            const emailValid = field.type !== 'email' || !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            const passwordValid = field.type !== 'password' || !value || value.length >= 8;
            const valid = Boolean(value) && emailValid && passwordValid;
            if (valid) field.removeAttribute('aria-invalid');
            else field.setAttribute('aria-invalid', 'true');
            return !valid;
        });
        invalid[0]?.focus();
        return invalid.length === 0;
    };

    loginForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validate(loginForm)) {
            loginStatus.textContent = 'Enter a valid email and password of at least 8 characters.';
            return;
        }
        loginStatus.textContent = 'Details validated. Authentication activation is still pending.';
    });

    registerForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validate(registerForm)) {
            registerStatus.textContent = 'Complete the highlighted account details.';
            return;
        }
        registerStatus.textContent = 'Details validated. No customer account has been created.';
    });

    forgot.addEventListener('click', () => {
        const loginEmail = loginForm.querySelector('[name="email"]');
        const recoveryEmail = recoveryForm.querySelector('[name="recoveryEmail"]');
        if (loginEmail.value && !recoveryEmail.value) recoveryEmail.value = loginEmail.value;
        recoveryStatus.textContent = 'Enter the account email to validate this recovery preview.';
        recoveryDialog.showModal();
        recoveryEmail.focus();
    });

    recoveryForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const email = recoveryForm.querySelector('[name="recoveryEmail"]');
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
        email.toggleAttribute('aria-invalid', !valid);
        if (!valid) {
            recoveryStatus.textContent = 'Enter a valid email address.';
            email.focus();
            return;
        }
        recoveryStatus.textContent = 'Email validated. No recovery message was sent in preview mode.';
    });

    recoveryClose.addEventListener('click', () => recoveryDialog.close());
    recoveryDialog.addEventListener('click', (event) => {
        if (event.target === recoveryDialog) recoveryDialog.close();
    });
    recoveryDialog.addEventListener('close', () => forgot.focus());

    const formatPrice = (value) => new Intl.NumberFormat('en-US', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(value);

    let currentSelection = null;
    let projects = [];
    let addresses = [];
    let reviews = [];
    let profile = null;
    let activeProjectId = null;
    let lastAccountTrigger = null;

    try {
        const stored = JSON.parse(window.localStorage.getItem(selectionStorageKey) || 'null');
        const expired = Number(stored?.updatedAt) > 0 && Date.now() - Number(stored.updatedAt) > 30 * 24 * 60 * 60 * 1000;
        if (!expired && stored?.productName && Number(stored.unitPrice) > 0 && Number(stored.quantity) > 0) {
            currentSelection = {
                productName: String(stored.productName),
                material: String(stored.material || 'Material pending'),
                quantity: Math.min(10, Math.max(1, Number.parseInt(stored.quantity, 10) || 1)),
                unitPrice: Number(stored.unitPrice),
            };
        }
        const saved = JSON.parse(window.localStorage.getItem(projectsStorageKey) || '[]');
        if (Array.isArray(saved)) {
            projects = saved.filter((project) => project?.id && project?.name && Number(project.unitPrice) > 0)
                .slice(0, 20)
                .map((project) => ({
                    id: String(project.id),
                    name: String(project.name).slice(0, 60),
                    productName: String(project.productName || 'Selected piece'),
                    material: String(project.material || 'Material pending'),
                    quantity: Math.min(10, Math.max(1, Number.parseInt(project.quantity, 10) || 1)),
                    unitPrice: Number(project.unitPrice),
                    status: project.status === 'archived' ? 'archived' : 'active',
                    updatedAt: Number(project.updatedAt) || Date.now(),
                }));
        }
        const savedAddresses = JSON.parse(window.localStorage.getItem(addressesStorageKey) || '[]');
        if (Array.isArray(savedAddresses)) {
            addresses = savedAddresses.filter((address) => address?.id && address?.firstName && address?.street)
                .slice(0, 8)
                .map((address) => ({
                    id: String(address.id),
                    firstName: String(address.firstName).slice(0, 40),
                    lastName: String(address.lastName || '').slice(0, 40),
                    street: String(address.street).slice(0, 80),
                    postalCode: String(address.postalCode || '').slice(0, 16),
                    city: String(address.city || '').slice(0, 50),
                    country: String(address.country || '').slice(0, 40),
                    isDefault: Boolean(address.isDefault),
                }));
            if (addresses.length && !addresses.some((address) => address.isDefault)) addresses[0].isDefault = true;
        }
        const savedReviews = JSON.parse(window.localStorage.getItem(reviewsStorageKey) || '[]');
        if (Array.isArray(savedReviews)) {
            reviews = savedReviews.filter((review) => review?.id && review?.productName && Number(review.total) > 0)
                .slice(0, 12)
                .map((review) => ({
                    id: String(review.id).slice(0, 30),
                    productName: String(review.productName).slice(0, 100),
                    material: String(review.material || 'Material pending').slice(0, 60),
                    quantity: Math.min(10, Math.max(1, Number.parseInt(review.quantity, 10) || 1)),
                    total: Number(review.total),
                    destination: String(review.destination || 'Delivery details pending').slice(0, 160),
                    delivery: String(review.delivery || 'Delivery pending').slice(0, 80),
                    createdAt: Number(review.createdAt) || Date.now(),
                }));
        }
        const savedProfile = JSON.parse(window.localStorage.getItem(settingsStorageKey) || 'null');
        if (savedProfile?.firstName && savedProfile?.lastName && savedProfile?.email) {
            profile = {
                firstName: String(savedProfile.firstName).slice(0, 40),
                lastName: String(savedProfile.lastName).slice(0, 40),
                email: String(savedProfile.email).slice(0, 100),
                phone: String(savedProfile.phone || '').slice(0, 30),
                editorial: Boolean(savedProfile.editorial),
                project: Boolean(savedProfile.project),
                service: Boolean(savedProfile.service),
                updatedAt: Number(savedProfile.updatedAt) || Date.now(),
            };
        }
    } catch (error) {
        projectNotice.textContent = 'Saved projects could not be restored. You can start a new project edit.';
    }

    projectSave.disabled = !currentSelection;
    if (!currentSelection) projectSave.title = 'Add a product to the private selection first';

    const persistProjects = () => {
        try {
            window.localStorage.setItem(projectsStorageKey, JSON.stringify(projects));
        } catch (error) {
            projectNotice.textContent = 'Project updated for this session. Browser storage is unavailable.';
        }
    };

    const persistAddresses = () => {
        try {
            window.localStorage.setItem(addressesStorageKey, JSON.stringify(addresses));
        } catch (error) {
            addressNotice.textContent = 'Address updated for this session. Browser storage is unavailable.';
        }
    };

    const projectLink = () => {
        const token = new URL(window.location.href).searchParams.get('token');
        return `/__veylune-preview/cart?token=${encodeURIComponent(token || '')}`;
    };

    const createAction = (label, action, id) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.dataset.projectAction = action;
        button.dataset.projectId = id;
        return button;
    };

    const renderProjects = () => {
        const filter = projectFilter.value;
        const sort = projectSort.value;
        let visible = projects.filter((project) => filter === 'all' || project.status === filter);
        visible = [...visible].sort((a, b) => {
            if (sort === 'name') return a.name.localeCompare(b.name);
            if (sort === 'status') return a.status.localeCompare(b.status) || b.updatedAt - a.updatedAt;
            return b.updatedAt - a.updatedAt;
        });

        projectList.replaceChildren();
        projectCount.textContent = `${projects.length} project${projects.length === 1 ? '' : 's'}`;
        projectEmpty.hidden = visible.length > 0;

        visible.forEach((project) => {
            const card = document.createElement('article');
            card.className = 'veylune-account-project-card';
            card.dataset.projectStatus = project.status;

            const head = document.createElement('div');
            const badge = document.createElement('span');
            badge.textContent = project.status === 'archived' ? 'Archived' : 'Active edit';
            const updated = document.createElement('time');
            updated.dateTime = new Date(project.updatedAt).toISOString();
            updated.textContent = new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric' }).format(project.updatedAt);
            head.append(badge, updated);

            const title = document.createElement('h4');
            title.textContent = project.name;
            const details = document.createElement('p');
            details.textContent = `${project.productName} · ${project.material} · Qty ${project.quantity}`;
            const estimate = document.createElement('strong');
            estimate.textContent = formatPrice(project.unitPrice * project.quantity);

            const actions = document.createElement('div');
            actions.append(
                createAction('Rename', 'rename', project.id),
                createAction(project.status === 'archived' ? 'Restore' : 'Archive', 'archive', project.id),
                createAction('Remove', 'remove', project.id)
            );
            if (project.status === 'active') {
                const continueLink = document.createElement('a');
                continueLink.href = projectLink();
                continueLink.textContent = 'Continue project';
                actions.prepend(continueLink);
            }
            card.append(head, title, details, estimate, actions);
            projectList.append(card);
        });
        renderArchive();
        renderReadiness();
    };

    const renderAddresses = () => {
        addressList.replaceChildren();
        addressEmpty.hidden = addresses.length > 0;
        addressCount.textContent = addresses.length
            ? `${addresses.length} delivery address${addresses.length === 1 ? '' : 'es'} saved.`
            : 'No delivery addresses saved.';
        addresses.forEach((address) => {
            const card = document.createElement('article');
            const heading = document.createElement('div');
            const title = document.createElement('h3');
            title.textContent = `${address.firstName} ${address.lastName}`.trim();
            heading.append(title);
            if (address.isDefault) {
                const badge = document.createElement('span');
                badge.textContent = 'Default';
                heading.append(badge);
            }
            const details = document.createElement('p');
            details.textContent = `${address.street} · ${address.postalCode} ${address.city} · ${address.country}`;
            const actions = document.createElement('div');
            actions.append(createAction('Edit', 'edit-address', address.id));
            if (!address.isDefault) actions.append(createAction('Set as default', 'default-address', address.id));
            actions.append(createAction('Remove', 'remove-address', address.id));
            card.append(heading, details, actions);
            addressList.append(card);
        });
        renderReadiness();
    };

    const renderArchive = () => {
        const archived = projects.filter((project) => project.status === 'archived')
            .sort((a, b) => b.updatedAt - a.updatedAt);
        archiveList.replaceChildren();
        archiveEmpty.hidden = archived.length > 0;
        archiveCount.textContent = archived.length
            ? `${archived.length} archived project${archived.length === 1 ? '' : 's'}.`
            : 'No projects archived.';
        archived.forEach((project) => {
            const card = document.createElement('article');
            const title = document.createElement('h3');
            title.textContent = project.name;
            const details = document.createElement('p');
            details.textContent = `${project.productName} · ${project.material} · Qty ${project.quantity}`;
            const meta = document.createElement('div');
            const estimate = document.createElement('strong');
            estimate.textContent = formatPrice(project.unitPrice * project.quantity);
            const restore = createAction('Restore project', 'restore-archive', project.id);
            meta.append(estimate, restore);
            card.append(title, details, meta);
            archiveList.append(card);
        });
    };

    const renderOrders = () => {
        const term = ordersSearch.value.trim().toLowerCase();
        const now = Date.now();
        const visible = reviews.filter((review) => {
            const status = now - review.createdAt > 30 * 24 * 60 * 60 * 1000 ? 'expired' : 'reviewed';
            const matchesStatus = ordersFilter.value === 'all' || ordersFilter.value === status;
            const matchesTerm = !term || `${review.id} ${review.productName}`.toLowerCase().includes(term);
            return matchesStatus && matchesTerm;
        });
        ordersList.replaceChildren();
        ordersEmpty.hidden = visible.length > 0;
        ordersEmptyTitle.textContent = reviews.length ? 'No matching checkout reviews.' : 'No checkout reviews yet.';
        ordersEmptyCopy.textContent = reviews.length
            ? 'Try another review number, product name or status.'
            : 'Complete the private checkout review to create a safe preview record.';
        ordersCount.textContent = reviews.length
            ? `${reviews.length} checkout review${reviews.length === 1 ? '' : 's'} saved.`
            : 'No checkout reviews saved.';
        visible.forEach((review) => {
            const expired = now - review.createdAt > 30 * 24 * 60 * 60 * 1000;
            const card = document.createElement('article');
            const head = document.createElement('div');
            const ref = document.createElement('strong');
            ref.textContent = review.id;
            const status = document.createElement('span');
            status.textContent = expired ? 'Expired' : 'Reviewed';
            head.append(ref, status);
            const title = document.createElement('h3');
            title.textContent = review.productName;
            const details = document.createElement('p');
            details.textContent = `${review.material} · Qty ${review.quantity} · ${review.delivery}`;
            const destination = document.createElement('p');
            destination.textContent = review.destination;
            const foot = document.createElement('div');
            const date = document.createElement('time');
            date.dateTime = new Date(review.createdAt).toISOString();
            date.textContent = new Intl.DateTimeFormat('en', { dateStyle: 'medium' }).format(review.createdAt);
            const total = document.createElement('strong');
            total.textContent = formatPrice(review.total);
            foot.append(date, total);
            card.append(head, title, details, destination, foot);
            ordersList.append(card);
        });
        if (visible.length) {
            ordersNotice.textContent = `${visible.length} checkout review${visible.length === 1 ? '' : 's'} shown.`;
        } else {
            ordersNotice.textContent = reviews.length ? 'No checkout reviews match the current search and status.' : 'No checkout reviews saved.';
        }
        renderReadiness();
    };

    const resetAddressForm = () => {
        addressForm.reset();
        addressForm.elements.addressId.value = '';
        addressCancel.hidden = true;
        addressSubmit.textContent = 'Add address';
        addressForm.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
    };

    const renderSettings = () => {
        settingsForm.reset();
        settingsForm.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
        if (!profile) {
            settingsSummary.textContent = 'Profile and communication preferences are ready to review.';
            settingsStatus.textContent = 'No profile settings have been saved in this browser.';
            return;
        }
        Object.entries(profile).forEach(([key, value]) => {
            const field = settingsForm.elements[key];
            if (!field) return;
            if (field.type === 'checkbox') field.checked = Boolean(value);
            else field.value = value;
        });
        const preferenceCount = ['editorial', 'project', 'service'].filter((key) => profile[key]).length;
        settingsSummary.textContent = `${profile.firstName}'s profile · ${preferenceCount} communication preference${preferenceCount === 1 ? '' : 's'}.`;
        settingsStatus.textContent = `Saved ${new Intl.DateTimeFormat('en', { dateStyle: 'medium' }).format(profile.updatedAt)} in this browser.`;
        renderReadiness();
    };

    const renderReadiness = () => {
        const checks = [
            { label: 'Profile details', detail: profile ? `${profile.firstName} ${profile.lastName}` : 'Add name and email in Profile Settings', ready: Boolean(profile) },
            { label: 'Default delivery address', detail: addresses.find((address) => address.isDefault)?.city || 'Add a default address', ready: addresses.some((address) => address.isDefault) },
            { label: 'Saved project context', detail: projects.length ? `${projects.length} project${projects.length === 1 ? '' : 's'} prepared` : 'Save a project edit', ready: projects.length > 0 },
            { label: 'Checkout review', detail: reviews.length ? `${reviews.length} review${reviews.length === 1 ? '' : 's'} recorded` : 'Complete a private checkout review', ready: reviews.length > 0 },
        ];
        const readyCount = checks.filter((check) => check.ready).length;
        readinessScore.textContent = `${readyCount} of ${checks.length} ready`;
        readinessSummary.textContent = readyCount === checks.length
            ? 'Preview handoff checklist complete. Secure activation is still required.'
            : `${readyCount} of ${checks.length} activation checks prepared.`;
        readinessList.replaceChildren();
        checks.forEach((check) => {
            const item = document.createElement('article');
            item.dataset.readinessState = check.ready ? 'ready' : 'pending';
            const mark = document.createElement('span');
            mark.textContent = check.ready ? 'Ready' : 'Pending';
            const copy = document.createElement('div');
            const title = document.createElement('h3');
            title.textContent = check.label;
            const detail = document.createElement('p');
            detail.textContent = check.detail;
            copy.append(title, detail);
            item.append(mark, copy);
            readinessList.append(item);
        });
    };

    projectFilter.addEventListener('change', renderProjects);
    projectSort.addEventListener('change', renderProjects);
    projectSave.addEventListener('click', () => {
        if (!currentSelection) {
            projectNotice.textContent = 'Add a product to the private selection before saving a project.';
            return;
        }
        const sequence = projects.length + 1;
        projects.unshift({
            id: `project-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
            name: `Project Edit ${sequence}`,
            ...currentSelection,
            status: 'active',
            updatedAt: Date.now(),
        });
        projects = projects.slice(0, 20);
        persistProjects();
        renderProjects();
        projectNotice.textContent = `Project Edit ${sequence} saved.`;
    });

    projectList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-project-action]');
        if (!button) return;
        const project = projects.find((item) => item.id === button.dataset.projectId);
        if (!project) return;
        if (button.dataset.projectAction === 'rename') {
            activeProjectId = project.id;
            projectRenameForm.elements.projectName.value = project.name;
            projectDialogStatus.textContent = '';
            projectDialog.showModal();
            return;
        }
        if (button.dataset.projectAction === 'archive') {
            project.status = project.status === 'archived' ? 'active' : 'archived';
            project.updatedAt = Date.now();
            projectNotice.textContent = project.status === 'archived' ? `${project.name} archived.` : `${project.name} restored.`;
        }
        if (button.dataset.projectAction === 'remove') {
            projects = projects.filter((item) => item.id !== project.id);
            projectNotice.textContent = `${project.name} removed.`;
        }
        persistProjects();
        renderProjects();
    });

    projectRenameForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = projectRenameForm.elements.projectName;
        const value = input.value.trim();
        if (!value) {
            input.setAttribute('aria-invalid', 'true');
            projectDialogStatus.textContent = 'Enter a project name.';
            input.focus();
            return;
        }
        const project = projects.find((item) => item.id === activeProjectId);
        if (project) {
            project.name = value.slice(0, 60);
            project.updatedAt = Date.now();
            persistProjects();
            renderProjects();
            projectNotice.textContent = `Project renamed to ${project.name}.`;
        }
        input.removeAttribute('aria-invalid');
        projectDialog.close();
    });
    projectDialogClose.addEventListener('click', () => projectDialog.close());
    projectDialog.addEventListener('click', (event) => {
        if (event.target === projectDialog) projectDialog.close();
    });
    projectDialog.addEventListener('close', () => {
        root.querySelector(`[data-project-id="${activeProjectId}"][data-project-action="rename"]`)?.focus();
    });

    addressOpen.forEach((button) => button.addEventListener('click', () => {
        lastAccountTrigger = button;
        resetAddressForm();
        addressDialog.showModal();
    }));
    addressClose.addEventListener('click', () => addressDialog.close());
    addressDialog.addEventListener('click', (event) => {
        if (event.target === addressDialog) addressDialog.close();
    });
    addressDialog.addEventListener('close', () => lastAccountTrigger?.focus());
    addressCancel.addEventListener('click', resetAddressForm);

    addressForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validate(addressForm)) {
            addressNotice.textContent = 'Complete every required delivery field.';
            return;
        }
        const data = new FormData(addressForm);
        const id = String(data.get('addressId') || '');
        const address = {
            id: id || `address-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
            firstName: String(data.get('firstName')).trim().slice(0, 40),
            lastName: String(data.get('lastName')).trim().slice(0, 40),
            street: String(data.get('street')).trim().slice(0, 80),
            postalCode: String(data.get('postalCode')).trim().slice(0, 16),
            city: String(data.get('city')).trim().slice(0, 50),
            country: String(data.get('country')).trim().slice(0, 40),
            isDefault: data.get('isDefault') === 'on' || addresses.length === 0,
        };
        if (address.isDefault) addresses.forEach((item) => { item.isDefault = false; });
        const existing = addresses.findIndex((item) => item.id === id);
        if (existing >= 0) addresses.splice(existing, 1, address);
        else addresses.unshift(address);
        addresses = addresses.slice(0, 8);
        persistAddresses();
        renderAddresses();
        addressNotice.textContent = existing >= 0 ? 'Delivery address updated.' : 'Delivery address added.';
        resetAddressForm();
    });

    addressList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-project-action]');
        if (!button) return;
        const address = addresses.find((item) => item.id === button.dataset.projectId);
        if (!address) return;
        if (button.dataset.projectAction === 'edit-address') {
            Object.entries(address).forEach(([key, value]) => {
                const field = addressForm.elements[key];
                if (!field) return;
                if (field.type === 'checkbox') field.checked = Boolean(value);
                else field.value = value;
            });
            addressCancel.hidden = false;
            addressSubmit.textContent = 'Save changes';
            addressForm.elements.firstName.focus();
            return;
        }
        if (button.dataset.projectAction === 'default-address') {
            addresses.forEach((item) => { item.isDefault = item.id === address.id; });
            addressNotice.textContent = `${address.firstName}'s address is now the default.`;
        }
        if (button.dataset.projectAction === 'remove-address') {
            const wasDefault = address.isDefault;
            addresses = addresses.filter((item) => item.id !== address.id);
            if (wasDefault && addresses.length) addresses[0].isDefault = true;
            addressNotice.textContent = 'Delivery address removed.';
            resetAddressForm();
        }
        persistAddresses();
        renderAddresses();
    });

    archiveOpen.addEventListener('click', () => archiveDialog.showModal());
    archiveClose.addEventListener('click', () => archiveDialog.close());
    archiveDialog.addEventListener('click', (event) => {
        if (event.target === archiveDialog) archiveDialog.close();
    });
    archiveDialog.addEventListener('close', () => archiveOpen.focus());
    archiveList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-project-action="restore-archive"]');
        if (!button) return;
        const project = projects.find((item) => item.id === button.dataset.projectId);
        if (!project) return;
        project.status = 'active';
        project.updatedAt = Date.now();
        persistProjects();
        renderProjects();
        projectNotice.textContent = `${project.name} restored.`;
        archiveNotice.textContent = `${project.name} moved back to active projects.`;
    });

    ordersOpen.forEach((button) => button.addEventListener('click', () => {
        lastAccountTrigger = button;
        renderOrders();
        ordersDialog.showModal();
    }));
    ordersClose.addEventListener('click', () => ordersDialog.close());
    ordersDialog.addEventListener('click', (event) => {
        if (event.target === ordersDialog) ordersDialog.close();
    });
    ordersDialog.addEventListener('close', () => lastAccountTrigger?.focus());
    ordersSearch.addEventListener('input', renderOrders);
    ordersFilter.addEventListener('change', renderOrders);

    settingsOpen.forEach((button) => button.addEventListener('click', () => {
        lastAccountTrigger = button;
        renderSettings();
        settingsDialog.showModal();
    }));
    settingsClose.addEventListener('click', () => settingsDialog.close());
    settingsDialog.addEventListener('click', (event) => {
        if (event.target === settingsDialog) settingsDialog.close();
    });
    settingsDialog.addEventListener('close', () => lastAccountTrigger?.focus());
    settingsForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validate(settingsForm)) {
            settingsStatus.textContent = 'Complete the required personal details with a valid email address.';
            return;
        }
        const field = (name) => settingsForm.querySelector(`[name="${name}"]`);
        profile = {
            firstName: field('firstName').value.trim().slice(0, 40),
            lastName: field('lastName').value.trim().slice(0, 40),
            email: field('email').value.trim().slice(0, 100),
            phone: field('phone').value.trim().slice(0, 30),
            editorial: field('editorial').checked,
            project: field('project').checked,
            service: field('service').checked,
            updatedAt: Date.now(),
        };
        try {
            window.localStorage.setItem(settingsStorageKey, JSON.stringify(profile));
            renderSettings();
            settingsStatus.textContent = 'Preview profile and communication preferences saved in this browser.';
        } catch (error) {
            renderSettings();
            settingsStatus.textContent = 'Settings updated for this session. Browser storage is unavailable.';
        }
    });

    readinessOpen.forEach((button) => button.addEventListener('click', () => {
        lastAccountTrigger = button;
        renderReadiness();
        readinessDialog.showModal();
    }));
    readinessClose.addEventListener('click', () => readinessDialog.close());
    readinessDialog.addEventListener('click', (event) => {
        if (event.target === readinessDialog) readinessDialog.close();
    });
    readinessDialog.addEventListener('close', () => lastAccountTrigger?.focus());

    renderAddresses();
    renderOrders();
    renderSettings();
    renderProjects();
});
