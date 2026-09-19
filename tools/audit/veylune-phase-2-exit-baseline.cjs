const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const browserExecutable = process.argv[3] || process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const { chromium } = require(playwrightModule);

const baseUrl = process.env.VEYLUNE_BASE_URL || 'https://veylune-shopware.ddev.site';
const outputRoot = process.env.VEYLUNE_EXIT_OUTPUT || path.resolve(process.cwd(), 'phase-2-1h-exit');
const routes = [
    ['home', '/'],
    ['category-furniture', '/categories/furniture'],
    ['discover-room', '/discover?q=room'],
    ['account-login', '/account/login'],
    ['cart', '/checkout/cart'],
    ['privacy', '/legal/privacy'],
    ['contact', '/contact-studio'],
    ['trade', '/trade-program'],
    ['consultation', '/private-consultation'],
    ['editions', '/editions'],
];
const viewports = [
    { id: 'desktop', width: 1440, height: 1000 },
    { id: 'tablet', width: 834, height: 1112 },
    { id: 'mobile', width: 390, height: 844 },
];

const ensureDirectory = (directory) => fs.mkdirSync(directory, { recursive: true });
const screenshotName = (viewport, surface) => `${viewport}--${surface}.png`;

async function focusEvidence(page, locator) {
    if (!(await locator.count()) || !(await locator.first().isVisible())) return null;
    await page.keyboard.press('Tab');
    await locator.first().focus();
    await page.waitForTimeout(180);
    return locator.first().evaluate((element) => {
        const style = getComputedStyle(element);
        const rect = element.getBoundingClientRect();
        return {
            tag: element.tagName.toLowerCase(),
            active: element === document.activeElement,
            outlineStyle: style.outlineStyle,
            outlineWidth: style.outlineWidth,
            outlineColor: style.outlineColor,
            outlineOffset: style.outlineOffset,
            boxShadow: style.boxShadow,
            width: Math.round(rect.width * 100) / 100,
            height: Math.round(rect.height * 100) / 100,
        };
    });
}

async function run() {
    ensureDirectory(outputRoot);
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable });
    const report = {
        schemaVersion: '1.0',
        phase: '2.1H',
        capturedAt: new Date().toISOString(),
        baseUrl,
        routes: routes.map(([id, routePath]) => ({ id, path: routePath })),
        viewports,
        cookie: [],
        footer: [],
        routeCaptures: [],
        reducedMotion: [],
    };

    try {
        for (const viewport of viewports) {
            const context = await browser.newContext({
                viewport: { width: viewport.width, height: viewport.height },
                ignoreHTTPSErrors: true,
                reducedMotion: 'no-preference',
                colorScheme: 'light',
            });
            const page = await context.newPage();
            let consoleErrors = [];
            let pageErrors = [];
            let failedResponses = [];
            page.on('console', (message) => {
                if (message.type() === 'error') consoleErrors.push(message.text());
            });
            page.on('pageerror', (error) => pageErrors.push(String(error)));
            page.on('response', (response) => {
                if (response.status() >= 400 && response.request().resourceType() !== 'image') {
                    failedResponses.push({ url: response.url(), status: response.status() });
                }
            });

            const homeResponse = await page.goto(baseUrl + '/', { waitUntil: 'networkidle', timeout: 45000 });
            const cookie = page.locator('.cookie-permission-container');
            await cookie.waitFor({ state: 'visible', timeout: 10000 });
            const cookieChecks = await cookie.evaluate((element) => {
                const rect = element.getBoundingClientRect();
                const buttons = [...element.querySelectorAll('button, .btn')]
                    .filter((button) => getComputedStyle(button).display !== 'none')
                    .map((button) => {
                        const buttonRect = button.getBoundingClientRect();
                        return {
                            label: (button.textContent || '').trim().replace(/\s+/g, ' '),
                            width: Math.round(buttonRect.width * 100) / 100,
                            height: Math.round(buttonRect.height * 100) / 100,
                        };
                    });
                const privacy = element.querySelector('a');
                return {
                    visible: rect.width > 0 && rect.height > 0,
                    viewportContained: rect.left >= -1 && rect.right <= innerWidth + 1,
                    buttons,
                    privacyHref: privacy?.getAttribute('href') || '',
                    horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
                };
            });
            cookieChecks.focus = await focusEvidence(page, cookie.locator('button, .btn'));
            if (cookieChecks.privacyHref) {
                const privacyResponse = await context.request.get(new URL(cookieChecks.privacyHref, baseUrl).href, { ignoreHTTPSErrors: true });
                cookieChecks.privacyStatus = privacyResponse.status();
            }
            const cookieScreenshot = screenshotName(viewport.id, 'cookie');
            await page.screenshot({ path: path.join(outputRoot, cookieScreenshot), fullPage: false });
            report.cookie.push({ viewport: viewport.id, status: homeResponse?.status() || null, screenshot: cookieScreenshot, checks: cookieChecks });

            const technicalConsent = page.getByRole('button', { name: 'Only technically required', exact: true });
            if (await technicalConsent.isVisible().catch(() => false)) {
                await technicalConsent.click();
                await page.waitForTimeout(120);
            }

            for (const [routeId, routePath] of routes) {
                consoleErrors = [];
                pageErrors = [];
                failedResponses = [];
                const response = await page.goto(baseUrl + routePath, { waitUntil: 'networkidle', timeout: 45000 });
                await page.waitForTimeout(80);
                const computed = await page.evaluate(() => ({
                    title: document.title,
                    lang: document.documentElement.lang,
                    horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
                    mainCount: document.querySelectorAll('main').length,
                    h1Count: document.querySelectorAll('main h1').length,
                    headerPresent: Boolean(document.querySelector('[data-veylune-header]')),
                    footerPresent: Boolean(document.querySelector('.veylune-footer')),
                    emptyTargets: [...document.querySelectorAll('a[href], button')].filter((element) => {
                        if (element.tagName === 'A') {
                            const href = element.getAttribute('href');
                            return href === '' || href === '#';
                        }
                        return false;
                    }).length,
                }));
                const focus = {
                    search: await focusEvidence(page, page.locator('[data-veylune-header-search-input]')),
                    account: await focusEvidence(page, page.locator('[data-veylune-header-account]')),
                    cart: await focusEvidence(page, page.locator('[data-veylune-header-cart]')),
                };
                let mobileTargets = null;
                if (viewport.id === 'mobile') {
                    mobileTargets = await page.evaluate(() => {
                        const selectors = [
                            '[data-veylune-mobile-toggle]',
                            '[data-veylune-header-account]',
                            '[data-veylune-header-cart]',
                            '[data-veylune-header-search-form] button',
                        ];
                        return selectors.map((selector) => {
                            const element = document.querySelector(selector);
                            if (!element) return { selector, missing: true };
                            const rect = element.getBoundingClientRect();
                            return { selector, width: Math.round(rect.width * 100) / 100, height: Math.round(rect.height * 100) / 100 };
                        });
                    });
                }
                report.routeCaptures.push({
                    viewport: viewport.id,
                    route: routeId,
                    path: routePath,
                    status: response?.status() || null,
                    finalUrl: page.url(),
                    computed,
                    focus,
                    mobileTargets,
                    errors: { consoleErrors, pageErrors, failedResponses },
                });
            }

            await page.goto(baseUrl + '/', { waitUntil: 'networkidle', timeout: 45000 });
            const footer = page.locator('.veylune-footer');
            await footer.scrollIntoViewIfNeeded();
            await page.waitForTimeout(100);
            const footerChecks = await footer.evaluate((element) => {
                const rect = element.getBoundingClientRect();
                const links = [...element.querySelectorAll('a')].map((link) => ({
                    label: (link.textContent || '').trim().replace(/\s+/g, ' '),
                    href: link.getAttribute('href') || '',
                }));
                return {
                    visible: rect.width > 0 && rect.height > 0,
                    viewportContained: rect.left >= -1 && rect.right <= innerWidth + 1,
                    horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
                    links,
                };
            });
            footerChecks.focus = await focusEvidence(page, footer.locator('a'));
            footerChecks.internalRoutes = [];
            const internalFooterRoutes = [...new Set(footerChecks.links.map((link) => link.href).filter((href) => href.startsWith('/')))];
            for (const routePath of internalFooterRoutes) {
                const response = await context.request.get(new URL(routePath, baseUrl).href, { ignoreHTTPSErrors: true });
                footerChecks.internalRoutes.push({ href: routePath, status: response.status() });
            }
            const footerScreenshot = screenshotName(viewport.id, 'footer');
            await page.screenshot({ path: path.join(outputRoot, footerScreenshot), fullPage: false });
            report.footer.push({ viewport: viewport.id, screenshot: footerScreenshot, checks: footerChecks });
            await context.close();

            const reducedContext = await browser.newContext({
                viewport: { width: viewport.width, height: viewport.height },
                ignoreHTTPSErrors: true,
                reducedMotion: 'reduce',
                colorScheme: 'light',
            });
            const reducedPage = await reducedContext.newPage();
            await reducedPage.goto(baseUrl + '/', { waitUntil: 'networkidle', timeout: 45000 });
            const reduced = await reducedPage.evaluate(() => {
                const selectors = ['a', 'button', 'input', 'summary'];
                return selectors.map((selector) => {
                    const element = [...document.querySelectorAll(selector)].find((candidate) => {
                        const rect = candidate.getBoundingClientRect();
                        return rect.width > 0 && rect.height > 0;
                    });
                    if (!element) return { selector, missing: true };
                    const style = getComputedStyle(element);
                    return {
                        selector,
                        transitionDuration: style.transitionDuration,
                        animationDuration: style.animationDuration,
                        animationIterationCount: style.animationIterationCount,
                    };
                });
            });
            report.reducedMotion.push({ viewport: viewport.id, controls: reduced });
            await reducedContext.close();
        }
    } finally {
        await browser.close();
    }

    const reportPath = path.join(outputRoot, 'shell-exit-baseline.json');
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
    const issueRoutes = report.routeCaptures.filter((capture) =>
        capture.status !== 200 || capture.computed.horizontalOverflow || capture.computed.mainCount !== 1 ||
        !capture.computed.headerPresent || !capture.computed.footerPresent || capture.computed.emptyTargets > 0 ||
        capture.errors.consoleErrors.length || capture.errors.pageErrors.length || capture.errors.failedResponses.length
    ).length;
    console.log(`routes=${report.routeCaptures.length}`);
    console.log(`screenshots=${report.cookie.length + report.footer.length}`);
    console.log(`issue_routes=${issueRoutes}`);
    console.log(`report=${reportPath}`);
    if (issueRoutes > 0) process.exitCode = 1;
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
