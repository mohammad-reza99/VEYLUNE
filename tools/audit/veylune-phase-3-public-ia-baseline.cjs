const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const browserExecutable = process.argv[3] || process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const contractPath = process.argv[4] || path.resolve(process.cwd(), 'veylune-surface-contract.json');
const outputRoot = process.env.VEYLUNE_IA_OUTPUT || path.resolve(process.cwd(), 'phase-3-1a-public-ia');
const baseUrl = process.env.VEYLUNE_BASE_URL || 'https://veylune-shopware.ddev.site';
const { chromium } = require(playwrightModule);

const contract = JSON.parse(fs.readFileSync(contractPath, 'utf8'));
const groups = contract.public.groups;
const routes = groups.flatMap((group) => group.routes.map((route) => ({
    group: group.id,
    owner: group.owner,
    priorClassification: group.currentClassification,
    route,
})));
const viewports = [
    { id: 'desktop', width: 1440, height: 1000 },
    { id: 'mobile', width: 390, height: 844 },
];
const representativeRoutes = new Set(groups.map((group) => group.routes[0]));
representativeRoutes.add('/journal');
representativeRoutes.add('/inspiration');
const allowedDirectOutcomes = {
    '/account': [302, 303],
    '/about': [301, 302],
    '/checkout/confirm': [200, 302, 303, 401, 404],
    '/wishlist': [200, 302, 404],
};

function safeName(value) {
    if (value === '/') return 'home';
    return value.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'route';
}

async function directOutcome(context, route) {
    const response = await context.request.get(`${baseUrl}${route}`, { maxRedirects: 0, failOnStatusCode: false });
    return {
        status: response.status(),
        location: response.headers().location || null,
    };
}

async function run() {
    fs.mkdirSync(outputRoot, { recursive: true });
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable });
    const report = {
        schemaVersion: '1.0',
        phase: '3.1A',
        capturedAt: new Date().toISOString(),
        reference: {
            url: 'https://www.wayfair.com/',
            capturedAt: '2026-09-19',
            observedPatterns: [
                'service utility rail',
                'dominant search and account cart utilities',
                'intentional primary navigation',
                'department navigation',
                'dense product and editorial discovery',
            ],
        },
        baseUrl,
        routeCount: routes.length,
        viewports,
        captures: [],
    };

    for (const viewport of viewports) {
        const context = await browser.newContext({
            viewport: { width: viewport.width, height: viewport.height },
            ignoreHTTPSErrors: true,
            reducedMotion: 'reduce',
        });
        for (const routeDefinition of routes) {
            const page = await context.newPage();
            const errors = { consoleErrors: [], pageErrors: [], failedResponses: [] };
            page.on('console', (message) => {
                if (message.type() === 'error') errors.consoleErrors.push(message.text());
            });
            page.on('pageerror', (error) => errors.pageErrors.push(error.message));
            page.on('response', (response) => {
                if (response.status() >= 500) errors.failedResponses.push({ status: response.status(), url: response.url() });
            });

            const direct = await directOutcome(context, routeDefinition.route);
            const response = await page.goto(`${baseUrl}${routeDefinition.route}`, { waitUntil: 'domcontentloaded', timeout: 45000 });
            await page.waitForTimeout(500);
            const computed = await page.evaluate(() => {
                const isVisible = (element) => {
                    const style = getComputedStyle(element);
                    const rect = element.getBoundingClientRect();
                    return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
                };
                const emptyTargets = [...document.querySelectorAll('a, button')].filter((element) => {
                    if (!isVisible(element)) return false;
                    if (element.matches('button[type="submit"], button[aria-label], [data-bs-toggle], [data-veylune-mobile-toggle]')) return false;
                    const text = (element.textContent || '').trim();
                    const label = (element.getAttribute('aria-label') || '').trim();
                    const href = (element.getAttribute('href') || '').trim();
                    return !text && !label && (!href || href === '#');
                }).length;
                const brokenImages = [...document.images].filter((image) => image.complete && image.naturalWidth === 0).map((image) => image.currentSrc || image.src).slice(0, 20);
                const bodyText = (document.body?.innerText || '').toLowerCase();
                const privatePreviewMarkers = [
                    '__veylune-preview',
                    'private preview',
                    'preview catalog',
                    'development preview',
                ].filter((marker) => bodyText.includes(marker));
                const legacySelectors = [
                    '.veylune-page--legacy',
                    '[data-veylune-legacy]',
                    '.legacy-page',
                    '.legacy-surface',
                ].filter((selector) => document.querySelector(selector));
                return {
                    title: document.title,
                    lang: document.documentElement.lang,
                    mainCount: document.querySelectorAll('main').length,
                    h1Count: document.querySelectorAll('h1').length,
                    headerPresent: Boolean(document.querySelector('header, .veylune-header')),
                    footerPresent: Boolean(document.querySelector('footer, .veylune-footer')),
                    horizontalOverflow: document.documentElement.scrollWidth > window.innerWidth + 1,
                    emptyTargets,
                    brokenImages,
                    privatePreviewMarkers,
                    legacySelectors,
                    bodyClasses: [...document.body.classList],
                };
            });

            const expectedStatuses = allowedDirectOutcomes[routeDefinition.route] || [200];
            const directAccepted = expectedStatuses.includes(direct.status);
            const issueCount = errors.consoleErrors.length + errors.pageErrors.length + errors.failedResponses.length;
            let classification = 'current';
            const reasons = [];
            if (!directAccepted) reasons.push(`unexpected_direct_status_${direct.status}`);
            if (!computed.headerPresent || !computed.footerPresent || computed.mainCount !== 1) reasons.push('shell_or_main_contract');
            if (computed.horizontalOverflow) reasons.push('horizontal_overflow');
            if (computed.emptyTargets > 0) reasons.push('empty_action_targets');
            if (computed.brokenImages.length > 0) reasons.push('broken_images');
            if (computed.privatePreviewMarkers.length > 0) reasons.push('private_preview_copy');
            if (computed.legacySelectors.length > 0) reasons.push('legacy_structure');
            if (issueCount > 0) reasons.push('runtime_errors');
            if (reasons.some((reason) => ['unexpected_direct_status_' + direct.status, 'shell_or_main_contract', 'runtime_errors'].includes(reason))) {
                classification = 'broken';
            } else if (reasons.length > 0) {
                classification = 'mixed';
            }
            if (['/checkout/confirm', '/wishlist'].includes(routeDefinition.route) && [404, 401].includes(direct.status)) {
                classification = 'decision_required';
                reasons.push('phase_3_behavior_decision');
            }

            let screenshot = null;
            if (representativeRoutes.has(routeDefinition.route) || classification !== 'current') {
                screenshot = `${viewport.id}--${safeName(routeDefinition.route)}.png`;
                await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
            }
            report.captures.push({
                viewport: viewport.id,
                ...routeDefinition,
                direct,
                followedStatus: response?.status() || null,
                finalUrl: page.url(),
                classification,
                reasons: [...new Set(reasons)],
                screenshot,
                computed,
                errors,
            });
            await page.close();
        }
        await context.close();
    }

    const counts = report.captures.reduce((carry, capture) => {
        carry[capture.classification] = (carry[capture.classification] || 0) + 1;
        return carry;
    }, {});
    report.summary = {
        routeCount: routes.length,
        surfaceCount: report.captures.length,
        screenshotCount: report.captures.filter((capture) => capture.screenshot).length,
        classifications: counts,
        runtimeIssueSurfaces: report.captures.filter((capture) => Object.values(capture.errors).some((items) => items.length > 0)).length,
        overflowSurfaces: report.captures.filter((capture) => capture.computed.horizontalOverflow).length,
        brokenImageSurfaces: report.captures.filter((capture) => capture.computed.brokenImages.length > 0).length,
        privatePreviewCopySurfaces: report.captures.filter((capture) => capture.computed.privatePreviewMarkers.length > 0).length,
        legacyStructureSurfaces: report.captures.filter((capture) => capture.computed.legacySelectors.length > 0).length,
    };
    const reportPath = path.join(outputRoot, 'public-ia-baseline.json');
    fs.writeFileSync(reportPath, `${JSON.stringify(report, null, 2)}\n`);
    await browser.close();
    console.log(JSON.stringify(report.summary));
    console.log(`report=${reportPath}`);
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
