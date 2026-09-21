const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const contractPath = process.argv[3] || path.resolve(process.cwd(), 'config/veylune-phase-5-discovery-contract.json');
const outputRoot = process.env.VEYLUNE_DISCOVERY_OUTPUT || path.resolve(process.cwd(), 'reports/visual-baselines/phase-5-1a-discovery');
const privateToken = process.env.VEYLUNE_DRAFT_PREVIEW_TOKEN || '';
const browserExecutable = process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const { chromium } = require(playwrightModule);
const contract = JSON.parse(fs.readFileSync(contractPath, 'utf8'));
const baseUrl = process.env.VEYLUNE_BASE_URL || contract.baseUrl;

if (!privateToken) throw new Error('VEYLUNE_DRAFT_PREVIEW_TOKEN is required.');

const routes = [
    ...contract.publicRoutes.map((route) => ({ scope: 'public', route })),
    ...contract.privateRoutes.map((route) => ({ scope: 'private', route })),
];

function routeUrl(definition) {
    const url = new URL(definition.route, baseUrl);
    if (definition.scope === 'private') url.searchParams.set('token', privateToken);
    return url.toString();
}

function safeName(value) {
    return value.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
}

function emptyErrors() {
    return { consoleErrors: [], pageErrors: [], failedResponses: [] };
}

function attachErrors(page, errors) {
    page.on('console', (message) => {
        if (message.type() === 'error') errors.consoleErrors.push(message.text());
    });
    page.on('pageerror', (error) => errors.pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.status() >= 500) errors.failedResponses.push({ status: response.status(), url: response.url() });
    });
}

async function computedState(page, scope) {
    return page.evaluate((currentScope) => {
        const visible = (element) => {
            const style = getComputedStyle(element);
            const rect = element.getBoundingClientRect();
            return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
        };
        const interactive = [...document.querySelectorAll('a, button, input, select, summary')].filter(visible);
        const missingAccessibleNames = interactive.filter((element) => {
            if (element.matches('input[type="hidden"]')) return false;
            const text = (element.textContent || '').trim();
            const label = (element.getAttribute('aria-label') || '').trim();
            const title = (element.getAttribute('title') || '').trim();
            const labelledBy = (element.getAttribute('aria-labelledby') || '').trim();
            const alt = element.matches('input[type="image"]') ? (element.getAttribute('alt') || '').trim() : '';
            const associatedLabel = [...(element.labels || [])]
                .some((candidate) => (candidate.textContent || '').trim());
            return !text && !label && !title && !labelledBy && !alt && !associatedLabel;
        }).map((element) => element.outerHTML.slice(0, 180));
        const brokenImages = [...document.images]
            .filter((image) => image.complete && image.naturalWidth === 0)
            .map((image) => image.currentSrc || image.src)
            .slice(0, 20);
        const privateRoot = document.querySelector('[data-veylune-plp], [data-veylune-preview-marketplace-home]');
        const publicDiscoveryRoot = document.querySelector('[data-veylune-living-index-discovery], [data-veylune-living-index-search]');
        return {
            title: document.title,
            mainCount: document.querySelectorAll('main').length,
            h1Count: document.querySelectorAll('h1').length,
            headerPresent: Boolean(document.querySelector('header, .veylune-header')),
            footerPresent: Boolean(document.querySelector('footer, .veylune-footer')),
            horizontalOverflow: document.documentElement.scrollWidth > window.innerWidth + 1,
            brokenImages,
            missingAccessibleNames,
            productCards: document.querySelectorAll('[data-plp-card], .product-box, .vli-search-card').length,
            semanticProductImages: [...document.querySelectorAll('[data-plp-card] img, .product-box img, .vli-search-card img')].filter((image) => image.getAttribute('alt')?.trim()).length,
            filterControls: document.querySelectorAll('[data-plp-material], [data-plp-price-range], [data-plp-status-filter], .vli-index__controls select[name="material"], .vli-search-filter-rail a').length,
            sortControls: document.querySelectorAll('[data-plp-sort], select[name="sort"]').length,
            paginationControls: document.querySelectorAll('[data-plp-load-more], .pagination, [data-pagination]').length,
            searchForms: document.querySelectorAll('form[role="search"], .vli-search-page form[action*="discover"]').length,
            emptyStates: document.querySelectorAll('.veylune-plp-empty:not([hidden]), .vli-index__empty, .vli-search-page__empty').length,
            selectionActions: document.querySelectorAll('[data-vli-selection-add]').length,
            saveActions: document.querySelectorAll('[data-plp-wishlist], [data-vli-selection-add]').length,
            cartActions: document.querySelectorAll('[data-preview-card-add], [data-pdp-add-selection], [data-pdp-add-selection-mobile]').length,
            privateRootPresent: Boolean(privateRoot),
            publicDiscoveryRootPresent: Boolean(publicDiscoveryRoot),
            scopeRootCorrect: currentScope === 'private' ? Boolean(privateRoot) : Boolean(publicDiscoveryRoot),
        };
    }, scope);
}

async function captureSurface(browser, viewport, definition) {
    const context = await browser.newContext({
        viewport: { width: viewport.width, height: viewport.height },
        ignoreHTTPSErrors: true,
        reducedMotion: 'reduce',
    });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    const response = await page.goto(routeUrl(definition), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(500);
    const computed = await computedState(page, definition.scope);
    const responseHeaders = response?.headers() || {};
    const screenshot = `${viewport.id}--${definition.scope}--${safeName(definition.route)}.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const capture = {
        viewport: viewport.id,
        scope: definition.scope,
        route: definition.route,
        status: response?.status() || null,
        finalPath: new URL(page.url()).pathname + new URL(page.url()).search.replace(/token=[^&]+/, 'token=REDACTED'),
        screenshot,
        computed,
        privateHeaders: definition.scope === 'private' ? {
            cacheControl: responseHeaders['cache-control'] || '',
            robots: await page.locator('meta[name="robots"]').getAttribute('content').catch(() => null),
        } : null,
        errors,
    };
    await context.close();
    return capture;
}

async function privateListingInteraction(browser, viewport) {
    const context = await browser.newContext({ viewport, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    const definition = { scope: 'private', route: contract.representativeInteractions.privateListing };
    await page.goto(routeUrl(definition), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(350);
    const initialVisible = await page.locator('[data-plp-card]:visible').count();
    const initialTotal = await page.locator('[data-plp-card]').count();
    let mobileFilterContained = true;
    if (viewport.width < 768) {
        await page.locator('[data-plp-filter-toggle]').click();
        mobileFilterContained = await page.locator('[data-plp-filter-panel]').evaluate((element) => {
            const rect = element.getBoundingClientRect();
            return rect.left >= -1 && rect.right <= window.innerWidth + 1;
        });
    }
    const wood = page.locator('[data-plp-material][value="wood"]:visible').first();
    if (await wood.count()) await wood.check();
    await page.waitForTimeout(100);
    const filteredVisible = await page.locator('[data-plp-card]:visible').count();
    const clearEnabled = await page.locator('[data-plp-filter-clear]:visible').first().isEnabled().catch(() => false);
    if (viewport.width < 768) {
        await page.locator('[data-plp-filter-toggle]').click();
    }
    await page.locator('[data-plp-sort-toggle]').click();
    const sortContained = await page.locator('[data-plp-sort-panel]').evaluate((element) => {
        const rect = element.getBoundingClientRect();
        return rect.left >= -1 && rect.right <= window.innerWidth + 1;
    });
    await page.locator('[data-plp-sort="price-low"]').click();
    const sortLabel = (await page.locator('[data-plp-sort-toggle]').innerText()).trim();
    const loadMore = page.locator('[data-plp-load-more]');
    const loadMoreVisible = await loadMore.isVisible().catch(() => false);
    if (loadMoreVisible) await loadMore.click();
    const afterLoadVisible = await page.locator('[data-plp-card]:visible').count();
    const firstCard = page.locator('[data-plp-card]:visible').first();
    const productHref = await firstCard.locator('a[href*="/catalog/product/"]').first().getAttribute('href');
    const save = firstCard.locator('[data-plp-wishlist]');
    const saveBefore = await save.getAttribute('aria-pressed').catch(() => null);
    if (await save.count()) await save.click();
    const saveAfter = await save.getAttribute('aria-pressed').catch(() => null);
    const add = firstCard.locator('[data-preview-card-add]');
    if (await add.count()) await add.click();
    const cartCount = await page.evaluate(() => {
        try {
            const state = JSON.parse(localStorage.getItem('veylune-private-selection-v2') || 'null');
            return Array.isArray(state?.items) ? state.items.length : 0;
        } catch (error) {
            return 0;
        }
    });
    const screenshot = `${viewport.width < 768 ? 'mobile' : 'desktop'}--interaction--private-listing.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const result = {
        id: `${viewport.width < 768 ? 'mobile' : 'desktop'}-private-listing`,
        pass: initialTotal === 19 && initialVisible === 12 && filteredVisible > 0 && filteredVisible < initialTotal
            && clearEnabled && mobileFilterContained && sortContained && sortLabel.includes('Price: Low to high')
            && afterLoadVisible >= filteredVisible && Boolean(productHref) && saveBefore !== saveAfter && cartCount === 1
            && Object.values(errors).every((items) => items.length === 0),
        initialTotal, initialVisible, filteredVisible, clearEnabled, mobileFilterContained, sortContained,
        sortLabel, loadMoreVisible, afterLoadVisible, productHref: productHref ? new URL(productHref, baseUrl).pathname : null,
        saveBefore, saveAfter, cartCount, screenshot, errors,
    };
    await context.close();
    return result;
}

async function publicListingInteraction(browser, viewport) {
    const context = await browser.newContext({ viewport, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    await page.goto(new URL(contract.representativeInteractions.publicListing, baseUrl).toString(), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(300);
    const result = await page.evaluate(() => ({
        root: Boolean(document.querySelector('[data-veylune-living-index-discovery]')),
        products: document.querySelectorAll('.product-box').length,
        empty: Boolean(document.querySelector('.vli-index__empty')),
        recoveryLinks: document.querySelectorAll('.vli-index__empty a').length,
        filterSortForm: Boolean(document.querySelector('.vli-index__controls')),
    }));
    const screenshot = `${viewport.width < 768 ? 'mobile' : 'desktop'}--interaction--public-listing.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const output = {
        id: `${viewport.width < 768 ? 'mobile' : 'desktop'}-public-listing`,
        pass: result.root && result.products === 0 && result.empty && result.recoveryLinks >= 2 && !result.filterSortForm
            && Object.values(errors).every((items) => items.length === 0),
        activationState: 'no_public_products_until_publication_gate',
        ...result,
        screenshot,
        errors,
    };
    await context.close();
    return output;
}

async function searchInteraction(browser, viewport, zeroResult) {
    const context = await browser.newContext({ viewport, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    const route = zeroResult ? contract.representativeInteractions.zeroResult : contract.representativeInteractions.searchResults;
    await page.goto(new URL(route, baseUrl).toString(), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(300);
    const result = zeroResult
        ? await page.evaluate(() => ({
            empty: Boolean(document.querySelector('.vli-search-page__empty')),
            recoveryLinks: document.querySelectorAll('.vli-search-page__empty a').length,
            searchForm: Boolean(document.querySelector('form[role="search"]')),
        }))
        : await page.evaluate(() => ({
            resultCards: document.querySelectorAll('[data-vli-search-result]').length,
            sort: Boolean(document.querySelector('.vli-search-sort select[name="sort"]')),
            facets: document.querySelectorAll('.vli-search-filter-rail a, .vli-search-mobile-filters a').length,
            selectionActions: document.querySelectorAll('[data-vli-selection-add]').length,
        }));
    let saved = null;
    if (!zeroResult) {
        const save = page.locator('[data-vli-selection-add]').first();
        const before = await save.getAttribute('aria-pressed').catch(() => null);
        if (await save.count()) await save.click();
        const after = await save.getAttribute('aria-pressed').catch(() => null);
        saved = { before, after, changed: before !== after };
    }
    const kind = zeroResult ? 'zero-result' : 'search-results';
    const screenshot = `${viewport.width < 768 ? 'mobile' : 'desktop'}--interaction--${kind}.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const pass = zeroResult
        ? result.empty && result.recoveryLinks >= 4 && result.searchForm
        : result.resultCards > 0 && result.sort && result.facets > 0 && result.selectionActions > 0 && saved?.changed;
    const output = {
        id: `${viewport.width < 768 ? 'mobile' : 'desktop'}-${kind}`,
        pass: Boolean(pass) && Object.values(errors).every((items) => items.length === 0),
        ...result,
        saved,
        screenshot,
        errors,
    };
    await context.close();
    return output;
}

async function run() {
    fs.mkdirSync(outputRoot, { recursive: true });
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable });
    const report = {
        schemaVersion: '1.0',
        phase: '5.1A',
        capturedAt: new Date().toISOString(),
        baseUrl,
        viewports: contract.viewports,
        routeDefinitions: routes,
        captures: [],
        interactions: [],
        implementationFamilies: [
            { id: 'private_catalog', template: 'catalog-preview-destination.html.twig', card: 'catalog-preview-card.html.twig', behavior: 'veylune-plp-v2.js', data: 'DraftCatalogPreviewService' },
            { id: 'public_destinations', template: 'living-index-discovery.html.twig', card: 'box-standard.html.twig', behavior: 'server_get_filter_sort', data: 'ProductExposureService' },
            { id: 'public_search', template: 'living-index-search.html.twig', card: 'living-index-search-results.html.twig', behavior: 'server_get_axis_sort', data: 'LivingIndexUtilityController' },
        ],
        knownUnificationGaps: [
            'private_and_public_destination_cards_use_different_templates',
            'private_listing_is_client_filtered_while_public_listing_is_server_filtered',
            'private_load_more_has_no_public_equivalent',
            'public_products_are_fail_closed_until_supplier_publication_approval',
            'search_results_are_context_cards_not_product_cards',
            'private_saved_piece_public_selection_and_cart_state_need_explicit_labels',
            'public_discovery_scene_media_still_uses_theme_assets',
            'loading_and_transport_error_states_are_not_shared',
        ],
    };

    for (const viewport of contract.viewports) {
        for (const definition of routes) {
            report.captures.push(await captureSurface(browser, viewport, definition));
        }
        const size = { width: viewport.width, height: viewport.height };
        report.interactions.push(await privateListingInteraction(browser, size));
        report.interactions.push(await publicListingInteraction(browser, size));
        report.interactions.push(await searchInteraction(browser, size, false));
        report.interactions.push(await searchInteraction(browser, size, true));
    }

    const issueSurface = (capture) => Object.values(capture.errors).some((items) => items.length > 0);
    report.summary = {
        publicRoutes: contract.publicRoutes.length,
        privateRoutes: contract.privateRoutes.length,
        routes: routes.length,
        viewports: contract.viewports.length,
        surfaces: report.captures.length,
        screenshots: report.captures.length + report.interactions.length,
        http200Surfaces: report.captures.filter((capture) => capture.status === 200).length,
        runtimeIssueSurfaces: report.captures.filter(issueSurface).length,
        overflowSurfaces: report.captures.filter((capture) => capture.computed.horizontalOverflow).length,
        brokenImageSurfaces: report.captures.filter((capture) => capture.computed.brokenImages.length > 0).length,
        missingAccessibleNameSurfaces: report.captures.filter((capture) => capture.computed.missingAccessibleNames.length > 0).length,
        privateHeaderFailures: report.captures.filter((capture) => capture.scope === 'private' && (
            !capture.privateHeaders?.cacheControl.includes('private')
            || !capture.privateHeaders?.cacheControl.includes('no-store')
            || !capture.privateHeaders?.robots?.includes('noindex')
        )).length,
        interactionScenarios: report.interactions.length,
        failedInteractions: report.interactions.filter((interaction) => !interaction.pass).map((interaction) => interaction.id),
        implementationFamilies: report.implementationFamilies.length,
        knownUnificationGaps: report.knownUnificationGaps.length,
    };
    fs.writeFileSync(path.join(outputRoot, 'discovery-baseline.json'), `${JSON.stringify(report, null, 2)}\n`);
    await browser.close();
    console.log(JSON.stringify(report.summary));
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
