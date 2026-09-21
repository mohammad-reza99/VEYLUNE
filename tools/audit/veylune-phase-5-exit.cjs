const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const contractPath = process.argv[3] || path.resolve(process.cwd(), 'config/veylune-phase-5-exit-contract.json');
const contract = JSON.parse(fs.readFileSync(contractPath, 'utf8'));
const routeContractPath = process.argv[4] || path.resolve(path.dirname(contractPath), 'veylune-phase-5-discovery-contract.json');
const routeContract = JSON.parse(fs.readFileSync(routeContractPath, 'utf8'));
const outputRoot = process.env.VEYLUNE_PHASE5_OUTPUT || path.resolve(process.cwd(), contract.evidenceDirectory);
const privateToken = process.env.VEYLUNE_DRAFT_PREVIEW_TOKEN || '';
const browserExecutable = process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const { chromium } = require(playwrightModule);

if (!privateToken) throw new Error('VEYLUNE_DRAFT_PREVIEW_TOKEN is required.');
fs.mkdirSync(outputRoot, { recursive: true });

const routes = [
    ...routeContract.publicRoutes.map((route) => ({ scope: 'public', route })),
    ...routeContract.privateRoutes.map((route) => ({ scope: 'private', route })),
];
const privateDestinations = routeContract.privateRoutes.filter((route) => route !== '/__veylune-preview/catalog');
const publicDestinations = routeContract.publicRoutes.slice(0, 22);

const safeName = (value) => value.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
const authorizedUrl = (route) => {
    const url = new URL(route, contract.baseUrl);
    url.searchParams.set('token', privateToken);
    return url.toString();
};
const publicUrl = (route) => new URL(route, contract.baseUrl).toString();
const emptyErrors = () => ({ consoleErrors: [], pageErrors: [], failedResponses: [] });
const attachErrors = (page, errors) => {
    page.on('console', (message) => { if (message.type() === 'error') errors.consoleErrors.push(message.text()); });
    page.on('pageerror', (error) => errors.pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.status() >= 500) errors.failedResponses.push({ status: response.status(), url: response.url() });
    });
};

async function inspectPage(page, scope) {
    await page.evaluate(async () => {
        const images = [...document.images];
        images.forEach((image) => { image.loading = 'eager'; });
        await Promise.all(images.map((image) => {
            if (image.complete) return Promise.resolve();
            return new Promise((resolve) => {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', resolve, { once: true });
                window.setTimeout(resolve, 5000);
            });
        }));
        window.scrollTo(0, 0);
    });
    return page.evaluate((currentScope) => {
        const visible = (element) => {
            const style = getComputedStyle(element);
            const rect = element.getBoundingClientRect();
            return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
        };
        const missingAccessibleNames = [...document.querySelectorAll('a,button,input,select,summary')]
            .filter(visible)
            .filter((element) => {
                if (element.matches('input[type="hidden"]')) return false;
                const labelledBy = (element.getAttribute('aria-labelledby') || '').trim();
                const labelledByText = labelledBy.split(/\s+/).filter(Boolean).some((id) => (document.getElementById(id)?.textContent || '').trim());
                const associatedLabel = [...(element.labels || [])].some((label) => (label.textContent || '').trim());
                return !(element.textContent || '').trim()
                    && !(element.getAttribute('aria-label') || '').trim()
                    && !(element.getAttribute('title') || '').trim()
                    && !labelledByText
                    && !associatedLabel
                    && !(element.matches('input[type="image"]') && (element.getAttribute('alt') || '').trim());
            })
            .map((element) => element.outerHTML.slice(0, 200));
        const brokenImages = [...document.images]
            .filter((image) => !image.complete || image.naturalWidth === 0)
            .map((image) => image.currentSrc || image.src);
        const privateRoot = document.querySelector('[data-veylune-plp], [data-veylune-preview-marketplace-home]');
        const publicRoot = document.querySelector('[data-veylune-living-index-discovery], [data-veylune-living-index-search]');
        return {
            mainCount: document.querySelectorAll('main').length,
            h1Count: document.querySelectorAll('h1').length,
            header: Boolean(document.querySelector('.header-main, header')),
            footer: Boolean(document.querySelector('footer, .footer-main')),
            overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
            brokenImages,
            missingAccessibleNames,
            scopeRootCorrect: currentScope === 'private' ? Boolean(privateRoot) : Boolean(publicRoot),
            sharedListings: document.querySelectorAll('[data-discovery-listing]').length,
            sharedProductCards: document.querySelectorAll('[data-discovery-card-kind="product"]').length,
            contextCards: document.querySelectorAll('[data-discovery-card-kind="context"]').length,
            adminMedia: document.querySelectorAll('[data-media-source="shopware_admin_media"]').length,
        };
    }, scope);
}

async function captureSurface(browser, viewport, definition) {
    const context = await browser.newContext({ viewport, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    const url = definition.scope === 'private' ? authorizedUrl(definition.route) : publicUrl(definition.route);
    const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(350);
    const computed = await inspectPage(page, definition.scope);
    const screenshot = `${viewport.id}--${definition.scope}--${safeName(definition.route)}.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const headers = response?.headers() || {};
    const item = {
        viewport: viewport.id, scope: definition.scope, route: definition.route,
        status: response?.status() || null, screenshot, computed, errors,
        privateHeaders: definition.scope === 'private' ? {
            cacheControl: headers['cache-control'] || '',
            robots: await page.locator('meta[name="robots"]').getAttribute('content').catch(() => ''),
        } : null,
    };
    await context.close();
    return item;
}

async function privateDestinationScenario(browser, viewport, route) {
    const context = await browser.newContext({ viewport, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    await page.goto(authorizedUrl(route), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(200);
    const cards = page.locator('[data-plp-card]');
    const total = await cards.count();
    const initialVisible = await page.locator('[data-plp-card]:visible').count();
    const sortOption = page.locator('[data-plp-sort="price-low"]');
    await page.locator('[data-plp-sort-toggle]').click();
    await sortOption.click();
    await page.waitForTimeout(100);
    const prices = await page.locator('[data-plp-card]:visible').evaluateAll((elements) => elements.map((element) => Number(element.dataset.plpPrice)));
    const sortPass = prices.every((price, index) => index === 0 || prices[index - 1] <= price);

    const usePopover = viewport.width < 992;
    if (usePopover) await page.locator('[data-plp-filter-toggle]').click();
    const filterRoot = usePopover ? page.locator('[data-plp-filter-panel]') : page.locator('.veylune-plp-filter-rail');
    const firstFilter = filterRoot.locator('input').first();
    let filterPass = true;
    if (await firstFilter.count()) {
        await firstFilter.check();
        await page.waitForTimeout(100);
        const filter = await firstFilter.evaluate((input) => ({
            value: input.value,
            kind: input.hasAttribute('data-plp-material') ? 'material' : input.hasAttribute('data-plp-price-range') ? 'price' : 'status',
        }));
        filterPass = await page.locator('[data-plp-card]:visible').evaluateAll((elements, current) => elements.length > 0 && elements.every((card) => {
            if (current.kind === 'material') return (card.dataset.plpMaterials || '').split(',').includes(current.value);
            if (current.kind === 'status') return card.dataset.plpStatus === current.value;
            const price = Number(card.dataset.plpPrice);
            if (current.value === 'under_500') return price < 500;
            if (current.value === '500_1000') return price >= 500 && price < 1000;
            if (current.value === '1000_2000') return price >= 1000 && price < 2000;
            return price >= 2000;
        }), filter);
        await page.locator('[data-plp-filter-clear]:visible').first().click();
        await page.waitForTimeout(100);
    }

    let panelContained = true;
    if (usePopover) {
        await page.locator('[data-plp-filter-toggle]').click();
        const box = await page.locator('[data-plp-filter-panel]').boundingBox();
        panelContained = Boolean(box && box.x >= 0 && box.y >= 0 && box.x + box.width <= viewport.width + 1 && box.y + box.height <= viewport.height + 1);
        await page.keyboard.press('Escape');
    } else {
        await page.locator('[data-plp-sort-toggle]').click();
        await page.locator('[data-plp-sort-toggle]').dispatchEvent('pointerleave', { pointerType: 'mouse' });
        await page.locator('[data-plp-sort-panel]').dispatchEvent('pointerleave', { pointerType: 'mouse' });
        await page.waitForTimeout(240);
        panelContained = await page.locator('[data-plp-sort-panel]').isHidden();
    }

    const beforeMore = await page.locator('[data-plp-card]:visible').count();
    const loadMore = page.locator('[data-plp-load-more]');
    let loadMorePass = total <= 12 ? await loadMore.isHidden() : false;
    if (total > 12) {
        await loadMore.click();
        await page.waitForTimeout(100);
        loadMorePass = await page.locator('[data-plp-card]:visible').count() > beforeMore;
    }
    const screenshot = `${viewport.id}--interaction--${safeName(route)}.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const pass = total > 0 && initialVisible === Math.min(total, 12) && sortPass && filterPass && panelContained && loadMorePass
        && errors.consoleErrors.length === 0 && errors.pageErrors.length === 0 && errors.failedResponses.length === 0;
    await context.close();
    return { viewport: viewport.id, route, total, initialVisible, sortPass, filterPass, panelContained, loadMorePass, screenshot, errors, pass };
}

async function searchScenario(browser, viewport, zero = false) {
    const context = await browser.newContext({ viewport, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    const query = zero ? 'zzzx-no-confident-match' : 'room';
    await page.goto(publicUrl(`/discover?q=${query}`), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(200);
    let pass;
    let details;
    if (zero) {
        const empty = await page.locator('.vli-search-page__empty').isVisible();
        const recoveryLinks = await page.locator('.vli-search-page__empty a').count();
        pass = empty && recoveryLinks >= 4;
        details = { empty, recoveryLinks };
    } else {
        const cards = page.locator('[data-discovery-card-kind="context"]');
        const initialTitles = await cards.locator('.vli-search-card__title').allTextContents();
        await page.selectOption('#vli-search-sort', 'title');
        await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.locator('.vli-search-sort button').click()]);
        const sortedTitles = await page.locator('[data-discovery-card-kind="context"] .vli-search-card__title').allTextContents();
        const alphabetic = [...sortedTitles].sort((a, b) => a.localeCompare(b));
        const save = page.locator('[data-vli-selection-add]').first();
        const before = await save.getAttribute('aria-pressed');
        await save.click();
        const after = await save.getAttribute('aria-pressed');
        const facets = await page.locator('.vli-search-sidebar__group a, .vli-search-mobile-filters a').count();
        pass = initialTitles.length > 0 && JSON.stringify(sortedTitles) === JSON.stringify(alphabetic) && before !== after && facets > 0;
        details = { results: initialTitles.length, sorted: JSON.stringify(sortedTitles) === JSON.stringify(alphabetic), saved: before !== after, facets };
    }
    pass = pass && errors.consoleErrors.length === 0 && errors.pageErrors.length === 0 && errors.failedResponses.length === 0;
    const screenshot = `${viewport.id}--interaction--search-${zero ? 'zero' : 'results'}.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    await context.close();
    return { viewport: viewport.id, kind: zero ? 'zero' : 'results', ...details, screenshot, errors, pass };
}

async function run() {
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable, args: ['--ignore-certificate-errors'] });
    const report = { schemaVersion: '1.0', phase: '5', capturedAt: new Date().toISOString(), surfaces: [], privateDestinations: [], searchScenarios: [], unauthorizedPrivateRoutes: [] };
    for (const viewportDefinition of contract.viewports) {
        const viewport = { width: viewportDefinition.width, height: viewportDefinition.height };
        for (const definition of routes) report.surfaces.push(await captureSurface(browser, { ...viewport, id: viewportDefinition.id }, definition));
        for (const route of privateDestinations) report.privateDestinations.push(await privateDestinationScenario(browser, { ...viewport, id: viewportDefinition.id }, route));
        report.searchScenarios.push(await searchScenario(browser, { ...viewport, id: viewportDefinition.id }, false));
        report.searchScenarios.push(await searchScenario(browser, { ...viewport, id: viewportDefinition.id }, true));
    }
    const requestContext = await browser.newContext({ ignoreHTTPSErrors: true });
    for (const route of routeContract.privateRoutes) {
        const response = await requestContext.request.get(publicUrl(route), { maxRedirects: 0 });
        report.unauthorizedPrivateRoutes.push({ route, status: response.status(), pass: response.status() === 404 });
    }
    await requestContext.close();
    await browser.close();

    const hasRuntimeIssue = (item) => item.errors.consoleErrors.length || item.errors.pageErrors.length || item.errors.failedResponses.length;
    const publicDestinationSurfaces = report.surfaces.filter((item) => item.scope === 'public' && publicDestinations.includes(item.route));
    report.summary = {
        routes: routes.length,
        viewports: contract.viewports.length,
        surfaces: report.surfaces.length,
        status200Surfaces: report.surfaces.filter((item) => item.status === 200).length,
        runtimeIssueSurfaces: report.surfaces.filter(hasRuntimeIssue).length,
        overflowSurfaces: report.surfaces.filter((item) => item.computed.overflow).length,
        brokenImageSurfaces: report.surfaces.filter((item) => item.computed.brokenImages.length).length,
        accessibilityIssueSurfaces: report.surfaces.filter((item) => item.computed.missingAccessibleNames.length || item.computed.mainCount !== 1 || item.computed.h1Count < 1 || !item.computed.header || !item.computed.footer || !item.computed.scopeRootCorrect).length,
        privateDestinationScenarios: report.privateDestinations.length,
        failedPrivateDestinationScenarios: report.privateDestinations.filter((item) => !item.pass).length,
        searchScenarios: report.searchScenarios.length,
        failedSearchScenarios: report.searchScenarios.filter((item) => !item.pass).length,
        unauthorizedPrivateRoutes: report.unauthorizedPrivateRoutes.length,
        unauthorizedPrivateRouteFailures: report.unauthorizedPrivateRoutes.filter((item) => !item.pass).length,
        publicDiscoveryListings: new Set(publicDestinationSurfaces.filter((item) => item.computed.sharedListings === 1).map((item) => item.route)).size,
        publicProductSurfaces: publicDestinationSurfaces.filter((item) => item.computed.sharedProductCards > 0).length,
        adminEditorialDestinations: new Set(publicDestinationSurfaces.filter((item) => item.computed.adminMedia > 0).map((item) => item.route)).size,
        screenshots: report.surfaces.length + report.privateDestinations.length + report.searchScenarios.length,
    };
    fs.writeFileSync(path.join(outputRoot, 'phase-5-exit.json'), `${JSON.stringify(report, null, 2)}\n`);
    console.log(JSON.stringify(report.summary));
}

run().catch((error) => { console.error(error); process.exitCode = 1; });
