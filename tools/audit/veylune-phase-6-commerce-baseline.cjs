const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const contractPath = process.argv[3] || path.resolve(process.cwd(), 'config/veylune-phase-6-commerce-baseline-contract.json');
const contract = JSON.parse(fs.readFileSync(contractPath, 'utf8'));
const projectRoot = process.env.VEYLUNE_PROJECT_ROOT || process.cwd();
const productSource = JSON.parse(fs.readFileSync(path.resolve(projectRoot, contract.productSource), 'utf8'));
const mediaSource = JSON.parse(fs.readFileSync(path.resolve(projectRoot, contract.mediaSource), 'utf8'));
const outputRoot = process.env.VEYLUNE_PHASE6_OUTPUT || path.resolve(projectRoot, contract.evidenceDirectory);
const privateToken = process.env.VEYLUNE_DRAFT_PREVIEW_TOKEN || '';
const browserExecutable = process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const { chromium } = require(playwrightModule);

if (!privateToken) throw new Error('VEYLUNE_DRAFT_PREVIEW_TOKEN is required.');
fs.mkdirSync(outputRoot, { recursive: true });

const products = (productSource.records || []).map((record) => ({
    recordId: record.recordId,
    sku: record.sku,
    name: record.name,
    productType: record.productType,
    databaseProductId: record.databaseProductId,
    media: (mediaSource.records || []).find((item) => item.recordId === record.recordId) || null,
}));

if (products.length !== contract.expected.products) {
    throw new Error(`Expected ${contract.expected.products} products, found ${products.length}.`);
}

const safeName = (value) => value.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
const urlFor = (route, authorized = false) => {
    const url = new URL(route, contract.baseUrl);
    if (authorized) url.searchParams.set('token', privateToken);
    return url.toString();
};
const privateProductRoute = (product) => contract.privateProductRouteTemplate.replace('{recordId}', product.recordId);
const publicProductRoute = (product) => contract.publicProductRouteTemplate.replace('{databaseProductId}', product.databaseProductId);
const emptyErrors = () => ({ consoleErrors: [], pageErrors: [], failedResponses: [] });
const attachErrors = (page, errors) => {
    page.on('console', (message) => { if (message.type() === 'error') errors.consoleErrors.push(message.text()); });
    page.on('pageerror', (error) => errors.pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.status() >= 500) errors.failedResponses.push({ status: response.status(), url: response.url() });
    });
};

async function inspectPage(page) {
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

    return page.evaluate(() => {
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
            .map((element) => element.outerHTML.slice(0, 240));
        const brokenImages = [...document.images]
            .filter((image) => !image.complete || image.naturalWidth === 0)
            .map((image) => image.currentSrc || image.src);
        return {
            title: document.title,
            mainCount: document.querySelectorAll('main').length,
            h1Count: document.querySelectorAll('h1').length,
            header: Boolean(document.querySelector('.header-main, header')),
            footer: Boolean(document.querySelector('footer, .footer-main')),
            overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
            brokenImages,
            missingAccessibleNames,
            robots: document.querySelector('meta[name="robots"]')?.content || '',
            privatePdp: Boolean(document.querySelector('[data-veylune-pdp-preview]')),
            privateCart: Boolean(document.querySelector('[data-veylune-cart-preview]')),
            privateCheckout: Boolean(document.querySelector('[data-veylune-checkout-preview]')),
            privateAccount: Boolean(document.querySelector('[data-veylune-account-preview]')),
            galleryImages: document.querySelectorAll('.veylune-pdp-gallery img').length,
            galleryThumbs: document.querySelectorAll('[data-pdp-gallery-thumb]').length,
            variantControls: document.querySelectorAll('[data-pdp-variant]').length,
            primaryActions: document.querySelectorAll('[data-pdp-add-selection], [data-pdp-add-selection-mobile]').length,
            noVerifiedReviewsCopy: document.body.textContent.includes('No verified reviews yet'),
            noBindingOrderCopy: /no (public )?order is created|non-binding private preview/i.test(document.body.textContent),
        };
    });
}

async function captureSurface(context, viewportDefinition, definition) {
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    const target = definition.authorized ? urlFor(definition.route, true) : urlFor(definition.route);
    const response = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(250);
    const computed = await inspectPage(page);
    const finalUrl = new URL(page.url());
    const screenshot = `${viewportDefinition.id}--${definition.scope}--${safeName(definition.id || definition.route)}.png`;
    await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
    const expectedStatus = definition.expectedStatus ?? 200;
    const status = response?.status() || null;
    if (expectedStatus === 404) {
        errors.consoleErrors = errors.consoleErrors.filter((message) => !message.includes('Failed to load resource'));
    }
    const finalPath = finalUrl.pathname;
    const statusPass = status === expectedStatus;
    const finalPathPass = !definition.finalPathPrefix || finalUrl.pathname.startsWith(definition.finalPathPrefix);
    const markerPass = !definition.marker || await page.locator(`[${definition.marker}]`).count() > 0;
    const accessibilityPass = computed.mainCount === 1
        && computed.h1Count >= 1
        && (expectedStatus === 404 || computed.header)
        && (expectedStatus === 404 || computed.footer)
        && computed.missingAccessibleNames.length === 0;
    await page.close();
    return {
        viewport: viewportDefinition.id,
        scope: definition.scope,
        id: definition.id,
        route: definition.route,
        status,
        expectedStatus,
        finalPath,
        screenshot,
        computed,
        errors,
        statusPass,
        finalPathPass,
        markerPass,
        accessibilityPass,
    };
}

async function privatePdpInteraction(context, viewportDefinition, product) {
    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    await page.goto(urlFor(privateProductRoute(product), true), { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.evaluate(() => window.localStorage.clear());
    await page.waitForTimeout(100);

    const rootPass = await page.locator(`[data-veylune-pdp-preview][data-product-id="${product.recordId}"]`).count() === 1;
    const thumbs = page.locator('[data-pdp-gallery-thumb]');
    const thumbCount = await thumbs.count();
    let keyboardPass = thumbCount > 0;
    if (thumbCount > 0) {
        await thumbs.first().focus();
        await page.keyboard.press('ArrowRight');
        keyboardPass = await thumbs.first().evaluate((element) => document.activeElement?.hasAttribute('data-pdp-gallery-thumb'));
    }

    await page.locator('[data-pdp-zoom-open]').click();
    const zoomOpened = await page.locator('[data-pdp-lightbox]').evaluate((dialog) => dialog.open);
    await page.keyboard.press('Escape');
    const zoomClosed = !(await page.locator('[data-pdp-lightbox]').evaluate((dialog) => dialog.open));

    const variants = page.locator('[data-pdp-variant]');
    const variantCount = await variants.count();
    let variantPass = variantCount > 0;
    if (variantCount > 1) {
        const variant = await variants.nth(1).getAttribute('data-pdp-variant');
        await variants.nth(1).click();
        variantPass = new URL(page.url()).searchParams.get('material') === variant;
    }

    await page.locator('[data-pdp-quantity-increase]').click();
    const quantityPass = await page.locator('[data-pdp-quantity-input]').inputValue() === '2';

    const postal = page.locator('[data-pdp-delivery-form] input[name="postalCode"]');
    await postal.fill('x');
    await page.locator('[data-pdp-delivery-form] button[type="submit"]').click();
    await page.waitForTimeout(50);
    const invalidDeliveryPass = await postal.getAttribute('aria-invalid') === 'true';
    await postal.fill('10115');
    await page.locator('[data-pdp-delivery-form] button[type="submit"]').click();
    await page.waitForTimeout(50);
    const validDeliveryPass = await postal.getAttribute('aria-invalid') === null
        && (await page.locator('[data-pdp-delivery-status]').textContent()).includes('10115');

    const addButton = page.locator('[data-pdp-add-selection]:visible, [data-pdp-add-selection-mobile]:visible').first();
    await addButton.click();
    const cartOpened = await page.locator('[data-pdp-cart-drawer]').evaluate((dialog) => dialog.open);
    const cartContent = await page.locator('[data-pdp-cart-content]').isVisible();
    const storedSelection = await page.evaluate(() => {
        const state = JSON.parse(window.localStorage.getItem('veylune-private-selection-v2') || 'null');
        return state?.items?.length === 1 && state.items[0].quantity === 2;
    });
    await page.locator('[data-pdp-cart-close]').click();

    const pass = rootPass && keyboardPass && zoomOpened && zoomClosed && variantPass && quantityPass
        && invalidDeliveryPass && validDeliveryPass && cartOpened && cartContent && storedSelection
        && errors.consoleErrors.length === 0 && errors.pageErrors.length === 0 && errors.failedResponses.length === 0;
    await page.close();
    return {
        viewport: viewportDefinition.id,
        recordId: product.recordId,
        productType: product.productType,
        rootPass,
        thumbCount,
        keyboardPass,
        zoomOpened,
        zoomClosed,
        variantCount,
        variantPass,
        quantityPass,
        invalidDeliveryPass,
        validDeliveryPass,
        cartOpened,
        cartContent,
        storedSelection,
        errors,
        pass,
    };
}

async function privateJourneyScenario(browser, viewportDefinition, seedProduct) {
    const context = await browser.newContext({
        viewport: { width: viewportDefinition.width, height: viewportDefinition.height },
        ignoreHTTPSErrors: true,
        reducedMotion: 'reduce',
    });
    await context.addInitScript(({ product, mediaUrl }) => {
        if (window.localStorage.getItem('veylune-private-selection-v2')) return;
        const lineId = `${product.recordId}::baseline-material`;
        window.localStorage.setItem('veylune-private-selection-v2', JSON.stringify({
            version: 2,
            items: [{
                lineId,
                productId: product.recordId,
                productName: product.name,
                material: 'Baseline material',
                quantity: 1,
                unitPrice: 1800,
                imageUrl: mediaUrl,
                imageAlt: `${product.name} product view`,
            }],
            postalCode: '',
            service: 'studio',
            activeLineId: lineId,
            updatedAt: Date.now(),
        }));
    }, { product: seedProduct, mediaUrl: seedProduct.media?.mediaUrl || '' });

    const page = await context.newPage();
    const errors = emptyErrors();
    attachErrors(page, errors);
    await page.goto(urlFor('/__veylune-preview/cart', true), { waitUntil: 'domcontentloaded', timeout: 45000 });
    const cartVisible = await page.locator('[data-cart-page-content]').isVisible();
    const cartItems = await page.locator('[data-cart-page-item]').count();
    await page.locator('[data-cart-page-item-increase]').click();
    const quantityChanged = (await page.locator('[data-cart-page-item-quantity]').textContent()) === '2';

    const cartPostal = page.locator('[data-cart-page-delivery] input[name="postalCode"]');
    await cartPostal.fill('x');
    await page.locator('[data-cart-page-delivery] button[type="submit"]').click();
    const cartInvalid = await cartPostal.getAttribute('aria-invalid') === 'true';
    await cartPostal.fill('10115');
    await page.locator('[data-cart-page-delivery] button[type="submit"]').click();
    const cartValid = await cartPostal.getAttribute('aria-invalid') === null;

    await page.locator('[data-cart-page-promo] button[type="submit"]').click();
    const promoInvalid = await page.locator('[data-cart-page-promo] input').getAttribute('aria-invalid') === 'true';
    await page.locator('[data-cart-page-promo] input').fill('BASELINE');
    await page.locator('[data-cart-page-promo] button[type="submit"]').click();
    const promoNoted = (await page.locator('[data-cart-page-promo-status]').textContent()).includes('pending activation');

    await page.locator('[data-cart-page-item-remove]').click();
    const removed = await page.locator('[data-cart-page-empty]').isVisible();
    await page.locator('[data-cart-page-undo]').click();
    const restored = await page.locator('[data-cart-page-item]').count() === 1;

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.locator('[data-cart-page-checkout]').click(),
    ]);
    const checkoutVisible = await page.locator('[data-checkout-form]').isVisible();
    const checkoutItems = await page.locator('[data-checkout-item]').count();
    await page.locator('[data-checkout-review]').click();
    const invalidFields = await page.locator('[aria-invalid="true"]').count();

    const fields = {
        email: 'baseline@example.test',
        firstName: 'Phase',
        lastName: 'Baseline',
        address: '1 Studio Way',
        city: 'Berlin',
        postalCode: '10115',
    };
    for (const [name, value] of Object.entries(fields)) await page.locator(`[name="${name}"]`).fill(value);
    await page.locator('[name="country"]').selectOption('DE');
    await page.locator('[name="reviewConsent"]').check();
    await page.locator('[data-checkout-review]').click();
    const reviewOpened = await page.locator('[data-checkout-review-dialog]').evaluate((dialog) => dialog.open);
    const noOrderCreated = (await page.locator('.veylune-checkout-review__guard').textContent()).includes('cannot submit an order');
    await page.locator('[data-checkout-review-close]').click();

    const pass = cartVisible && cartItems === 1 && quantityChanged && cartInvalid && cartValid
        && promoInvalid && promoNoted && removed && restored && checkoutVisible && checkoutItems === 1
        && invalidFields > 0 && reviewOpened && noOrderCreated
        && errors.consoleErrors.length === 0 && errors.pageErrors.length === 0 && errors.failedResponses.length === 0;
    await context.close();
    return {
        viewport: viewportDefinition.id,
        cartVisible,
        cartItems,
        quantityChanged,
        cartInvalid,
        cartValid,
        promoInvalid,
        promoNoted,
        removed,
        restored,
        checkoutVisible,
        checkoutItems,
        invalidFields,
        reviewOpened,
        noOrderCreated,
        errors,
        pass,
    };
}

async function run() {
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable, args: ['--ignore-certificate-errors'] });
    const report = {
        schemaVersion: '1.0',
        phase: '6',
        workPackage: '6.1A',
        capturedAt: new Date().toISOString(),
        productInventory: products.map((product) => ({
            recordId: product.recordId,
            sku: product.sku,
            name: product.name,
            productType: product.productType,
            databaseProductId: product.databaseProductId,
            governedCover: Boolean(product.media?.pass && product.media?.mediaUrl),
            launchApproved: false,
        })),
        surfaces: [],
        privatePdpInteractions: [],
        privateJourneyScenarios: [],
        publicProductBoundaries: [],
        unauthorizedPrivateRoutes: [],
        knownGaps: contract.knownGaps,
    };

    for (const viewportDefinition of contract.viewports) {
        const context = await browser.newContext({
            viewport: { width: viewportDefinition.width, height: viewportDefinition.height },
            ignoreHTTPSErrors: true,
            reducedMotion: 'reduce',
        });
        for (const product of products) {
            report.surfaces.push(await captureSurface(context, viewportDefinition, {
                scope: 'private-pdp',
                id: `product-${product.recordId}`,
                route: privateProductRoute(product),
                authorized: true,
                marker: 'data-veylune-pdp-preview',
            }));
        }
        for (const route of contract.privateJourneyRoutes) {
            report.surfaces.push(await captureSurface(context, viewportDefinition, {
                scope: 'private-journey',
                ...route,
                authorized: true,
            }));
        }
        for (const route of contract.publicSurfaceRoutes) {
            report.surfaces.push(await captureSurface(context, viewportDefinition, {
                scope: 'public-commerce',
                ...route,
                authorized: false,
            }));
        }
        await context.close();

        if (contract.interactionViewports.includes(viewportDefinition.id)) {
            const interactionContext = await browser.newContext({
                viewport: { width: viewportDefinition.width, height: viewportDefinition.height },
                ignoreHTTPSErrors: true,
                reducedMotion: 'reduce',
            });
            await interactionContext.addInitScript(() => window.localStorage.clear());
            for (const product of products) {
                report.privatePdpInteractions.push(await privatePdpInteraction(interactionContext, viewportDefinition, product));
            }
            await interactionContext.close();
        }
        report.privateJourneyScenarios.push(await privateJourneyScenario(browser, viewportDefinition, products[0]));
    }

    const requestContext = await browser.newContext({ ignoreHTTPSErrors: true });
    for (const product of products) {
        const response = await requestContext.request.get(urlFor(publicProductRoute(product)), { maxRedirects: 0 });
        report.publicProductBoundaries.push({
            recordId: product.recordId,
            databaseProductId: product.databaseProductId,
            route: publicProductRoute(product),
            status: response.status(),
            pass: response.status() === 404,
        });
    }
    for (const product of products) {
        const route = privateProductRoute(product);
        const response = await requestContext.request.get(urlFor(route), { maxRedirects: 0 });
        report.unauthorizedPrivateRoutes.push({ route, status: response.status(), pass: response.status() === 404 });
    }
    for (const definition of contract.privateJourneyRoutes) {
        const response = await requestContext.request.get(urlFor(definition.route), { maxRedirects: 0 });
        report.unauthorizedPrivateRoutes.push({ route: definition.route, status: response.status(), pass: response.status() === 404 });
    }
    await requestContext.close();
    await browser.close();

    const hasRuntimeIssue = (item) => item.errors.consoleErrors.length || item.errors.pageErrors.length || item.errors.failedResponses.length;
    const accessibilityIssue = (item) => !item.accessibilityPass;
    report.summary = {
        products: products.length,
        viewports: contract.viewports.length,
        privatePdpSurfaces: report.surfaces.filter((item) => item.scope === 'private-pdp').length,
        privateJourneySurfaces: report.surfaces.filter((item) => item.scope === 'private-journey').length,
        publicSurfaceSurfaces: report.surfaces.filter((item) => item.scope === 'public-commerce').length,
        surfaces: report.surfaces.length,
        statusExpectationFailures: report.surfaces.filter((item) => !item.statusPass || !item.finalPathPass || !item.markerPass).length,
        runtimeIssueSurfaces: report.surfaces.filter(hasRuntimeIssue).length,
        overflowSurfaces: report.surfaces.filter((item) => item.computed.overflow).length,
        brokenImageSurfaces: report.surfaces.filter((item) => item.computed.brokenImages.length > 0).length,
        accessibilityIssueSurfaces: report.surfaces.filter(accessibilityIssue).length,
        privatePdpInteractionScenarios: report.privatePdpInteractions.length,
        failedPrivatePdpInteractions: report.privatePdpInteractions.filter((item) => !item.pass).length,
        privateJourneyScenarios: report.privateJourneyScenarios.length,
        failedPrivateJourneyScenarios: report.privateJourneyScenarios.filter((item) => !item.pass).length,
        publicProductBoundaryChecks: report.publicProductBoundaries.length,
        failedPublicProductBoundaryChecks: report.publicProductBoundaries.filter((item) => !item.pass).length,
        unauthorizedPrivateRouteChecks: report.unauthorizedPrivateRoutes.length,
        failedUnauthorizedPrivateRouteChecks: report.unauthorizedPrivateRoutes.filter((item) => !item.pass).length,
        governedCoverProducts: report.productInventory.filter((item) => item.governedCover).length,
        launchApprovedProducts: report.productInventory.filter((item) => item.launchApproved).length,
        registeredGaps: report.knownGaps.length,
        screenshots: fs.readdirSync(outputRoot).filter((file) => file.endsWith('.png')).length,
    };

    fs.writeFileSync(path.join(outputRoot, 'commerce-baseline.json'), `${JSON.stringify(report, null, 2)}\n`);
    process.stdout.write(`${JSON.stringify(report.summary, null, 2)}\n`);
}

run().catch((error) => {
    console.error(error);
    process.exit(1);
});
