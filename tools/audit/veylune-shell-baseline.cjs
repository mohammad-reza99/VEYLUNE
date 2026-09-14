const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const browserExecutable = process.argv[3] || process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const { chromium } = require(playwrightModule);

const projectRoot = path.resolve(__dirname, '..', '..');
const outputRoot = process.env.VEYLUNE_BASELINE_OUTPUT
    ? path.resolve(projectRoot, process.env.VEYLUNE_BASELINE_OUTPUT)
    : path.join(projectRoot, 'reports', 'visual-baselines', 'phase-2-1a');
const baseUrl = process.env.VEYLUNE_BASE_URL || 'https://veylune-shopware.ddev.site';

const routes = [
    { id: 'home', path: '/' },
    { id: 'category-furniture', path: '/categories/furniture' },
    { id: 'discover-room', path: '/discover?q=room' },
    { id: 'account-login', path: '/account/login' },
    { id: 'cart-empty', path: '/checkout/cart' },
];

const viewports = [
    { id: 'desktop', width: 1440, height: 1000 },
    { id: 'tablet', width: 834, height: 1112 },
    { id: 'mobile', width: 390, height: 844 },
];

const round = (value) => typeof value === 'number' ? Math.round(value * 100) / 100 : value;

async function computedSnapshot(page) {
    return page.evaluate(() => {
        const snapshot = (selector) => {
            const element = document.querySelector(selector);
            if (!element) return null;
            const rect = element.getBoundingClientRect();
            const style = getComputedStyle(element);
            return {
                selector,
                rect: {
                    x: rect.x,
                    y: rect.y,
                    width: rect.width,
                    height: rect.height,
                    top: rect.top,
                    right: rect.right,
                    bottom: rect.bottom,
                    left: rect.left,
                },
                display: style.display,
                position: style.position,
                visibility: style.visibility,
                opacity: style.opacity,
                color: style.color,
                backgroundColor: style.backgroundColor,
                borderColor: style.borderColor,
                borderRadius: style.borderRadius,
                boxShadow: style.boxShadow,
                fontFamily: style.fontFamily,
                fontSize: style.fontSize,
                fontWeight: style.fontWeight,
                lineHeight: style.lineHeight,
                letterSpacing: style.letterSpacing,
                gridTemplateColumns: style.gridTemplateColumns,
                gap: style.gap,
                padding: style.padding,
            };
        };

        const selectors = [
            '[data-veylune-header]',
            '.veylune-marketplace-utility',
            '.veylune-marketplace-utility__inner',
            '.veylune-marketplace-family',
            '.veylune-marketplace-family__primary',
            '.veylune-marketplace-utility__announcement',
            '.veylune-marketplace-utility__links',
            '.veylune-marketplace-utility__links span',
            '.veylune-header__bar',
            '.veylune-header__inner',
            '.veylune-header__logo',
            '.veylune-marketplace-search',
            '.veylune-marketplace-search__input',
            '.veylune-marketplace-search__submit',
            '.veylune-header__nav',
            '.veylune-header__actions',
            '.veylune-marketplace-department-rail',
            '.veylune-mobile-nav',
        ];

        return {
            viewport: { width: innerWidth, height: innerHeight },
            document: {
                title: document.title,
                lang: document.documentElement.lang,
                scrollWidth: document.documentElement.scrollWidth,
                clientWidth: document.documentElement.clientWidth,
                horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
            },
            elements: Object.fromEntries(selectors.map((selector) => [selector, snapshot(selector)])),
            actions: {
                accountHref: document.querySelector('.veylune-marketplace-action[href*="account"]')?.getAttribute('href') || null,
                cartHref: document.querySelector('.veylune-header__bag')?.getAttribute('href') || null,
                departmentLinks: [...document.querySelectorAll('.veylune-marketplace-department-rail a')].map((link) => ({
                    label: link.textContent.trim(),
                    href: link.getAttribute('href'),
                })),
                navLinks: [...document.querySelectorAll('.veylune-header__nav a')].map((link) => ({
                    label: link.textContent.trim(),
                    href: link.getAttribute('href'),
                    mega: link.getAttribute('data-veylune-mega-trigger'),
                })),
            },
        };
    });
}

async function interactionSnapshot(page, viewport) {
    const result = { mode: viewport.id, checks: {} };

    if (viewport.width >= 1200) {
        const trigger = page.locator('[data-veylune-mega-trigger]').first();
        if (await trigger.count()) {
            await trigger.hover();
            await page.waitForTimeout(180);
            result.checks.megaHoverOpen = await page.locator('[data-veylune-mega]').evaluate((element) => ({
                open: element.classList.contains('is-open'),
                ariaHidden: element.getAttribute('aria-hidden'),
            }));
            await page.mouse.move(viewport.width - 4, viewport.height - 4);
            await page.waitForTimeout(280);
            result.checks.megaPointerLeaveClose = await page.locator('[data-veylune-mega]').evaluate((element) => !element.classList.contains('is-open'));
            await trigger.focus();
            await page.waitForTimeout(160);
            await page.keyboard.press('Escape');
            await page.waitForTimeout(80);
            result.checks.megaEscapeClose = await page.locator('[data-veylune-mega]').evaluate((element) => !element.classList.contains('is-open'));
            result.checks.megaFocusRestored = await trigger.evaluate((element) => document.activeElement === element);
        }

        const search = page.locator('[data-vli-header-search-input]');
        if (await search.count()) {
            await search.focus();
            await search.fill('room');
            await page.waitForTimeout(100);
            result.checks.searchSuggestOpen = await page.locator('[data-vli-header-suggest]').evaluate((element) => ({
                hidden: element.hidden,
                inputExpanded: document.querySelector('[data-vli-header-search-input]')?.getAttribute('aria-expanded'),
            }));
            await page.keyboard.press('Escape');
            await page.waitForTimeout(80);
            result.checks.searchEscapeClose = await page.locator('[data-vli-header-suggest]').evaluate((element) => element.hidden);
        }
    } else if (viewport.width >= 768) {
        result.checks.mobileToggleHidden = await page.locator('[data-veylune-mobile-toggle]:visible').count() === 0;
        result.checks.tabletNavVisible = await page.locator('.veylune-header__nav:visible').count() > 0;

        const search = page.locator('[data-vli-header-search-input]');
        if (await search.count()) {
            await search.focus();
            await search.fill('room');
            await page.waitForTimeout(100);
            result.checks.searchSuggestOpen = await page.locator('[data-vli-header-suggest]').evaluate((element) => ({
                hidden: element.hidden,
                inputExpanded: document.querySelector('[data-vli-header-search-input]')?.getAttribute('aria-expanded'),
            }));
            await page.keyboard.press('Escape');
            await page.waitForTimeout(80);
            result.checks.searchEscapeClose = await page.locator('[data-vli-header-suggest]').evaluate((element) => element.hidden);
        }
    } else {
        const toggle = page.locator('[data-veylune-mobile-toggle]:visible').first();
        const drawer = page.locator('[data-veylune-mobile-nav]');
        if (await toggle.count() && await drawer.count()) {
            await toggle.focus();
            await toggle.click();
            await page.waitForTimeout(100);
            result.checks.mobileDrawerOpen = await drawer.evaluate((element) => ({
                open: element.classList.contains('is-open'),
                ariaHidden: element.getAttribute('aria-hidden'),
                bodyLocked: document.body.classList.contains('veylune-overlay-active'),
            }));
            await page.keyboard.press('Escape');
            await page.waitForTimeout(100);
            result.checks.mobileDrawerEscapeClose = await drawer.evaluate((element) => !element.classList.contains('is-open'));
            result.checks.mobileDrawerFocusRestored = await toggle.evaluate((element) => document.activeElement === element);
        }
    }

    return result;
}

async function run() {
    fs.mkdirSync(outputRoot, { recursive: true });
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable });
    const report = {
        schemaVersion: '1.0',
        phase: process.env.VEYLUNE_CAPTURE_PHASE || '2.1A',
        capturedAt: new Date().toISOString(),
        baseUrl,
        routes: routes.map(({ id, path: routePath }) => ({ id, path: routePath })),
        viewports,
        captures: [],
    };

    try {
        for (const viewport of viewports) {
            const context = await browser.newContext({
                viewport: { width: viewport.width, height: viewport.height },
                ignoreHTTPSErrors: true,
                reducedMotion: 'reduce',
                colorScheme: 'light',
            });
            const page = await context.newPage();

            for (const route of routes) {
                const consoleErrors = [];
                const pageErrors = [];
                const failedResponses = [];
                const onConsole = (message) => {
                    if (message.type() === 'error') consoleErrors.push(message.text());
                };
                const onPageError = (error) => pageErrors.push(String(error.message || error));
                const onResponse = (response) => {
                    if (response.status() >= 400 && response.request().resourceType() !== 'image') {
                        failedResponses.push(`${response.status()} ${response.request().method()} ${response.url()}`);
                    }
                };
                page.on('console', onConsole);
                page.on('pageerror', onPageError);
                page.on('response', onResponse);

                const response = await page.goto(baseUrl + route.path, { waitUntil: 'networkidle', timeout: 45000 });
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.waitForTimeout(150);

                const computed = await computedSnapshot(page);
                for (const element of Object.values(computed.elements)) {
                    if (!element) continue;
                    for (const key of Object.keys(element.rect)) element.rect[key] = Math.round(element.rect[key] * 100) / 100;
                }
                const interactions = route.id === 'home' ? await interactionSnapshot(page, viewport) : null;
                const screenshotPath = path.join(outputRoot, `${viewport.id}--${route.id}.png`);
                await page.screenshot({
                    path: screenshotPath,
                    clip: { x: 0, y: 0, width: viewport.width, height: Math.min(viewport.height, 360) },
                });

                report.captures.push({
                    viewport: viewport.id,
                    route: route.id,
                    path: route.path,
                    status: response?.status() ?? null,
                    finalUrl: page.url(),
                    screenshot: path.relative(projectRoot, screenshotPath).replaceAll('\\', '/'),
                    computed,
                    interactions,
                    errors: { consoleErrors, pageErrors, failedResponses },
                });

                page.off('console', onConsole);
                page.off('pageerror', onPageError);
                page.off('response', onResponse);
            }

            await context.close();
        }
    } finally {
        await browser.close();
    }

    const reportPath = path.join(outputRoot, 'shell-baseline.json');
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');

    const interactionFailed = (capture) => {
        const checks = capture.interactions?.checks;
        if (!checks) return false;

        if (capture.interactions.mode === 'desktop') {
            return checks.megaHoverOpen?.open !== true ||
                checks.megaHoverOpen?.ariaHidden !== 'false' ||
                checks.megaPointerLeaveClose !== true ||
                checks.megaEscapeClose !== true ||
                checks.megaFocusRestored !== true ||
                checks.searchSuggestOpen?.hidden !== false ||
                checks.searchSuggestOpen?.inputExpanded !== 'true' ||
                checks.searchEscapeClose !== true;
        }

        if (capture.interactions.mode === 'tablet') {
            return checks.mobileToggleHidden !== true ||
                checks.tabletNavVisible !== true ||
                checks.searchSuggestOpen?.hidden !== false ||
                checks.searchSuggestOpen?.inputExpanded !== 'true' ||
                checks.searchEscapeClose !== true;
        }

        return checks.mobileDrawerOpen?.open !== true ||
            checks.mobileDrawerOpen?.ariaHidden !== 'false' ||
            checks.mobileDrawerOpen?.bodyLocked !== true ||
            checks.mobileDrawerEscapeClose !== true ||
            checks.mobileDrawerFocusRestored !== true;
    };

    const failed = report.captures.filter((capture) =>
        capture.status !== 200 ||
        capture.computed.document.horizontalOverflow ||
        capture.errors.consoleErrors.length > 0 ||
        capture.errors.pageErrors.length > 0 ||
        capture.errors.failedResponses.length > 0 ||
        interactionFailed(capture)
    );

    console.log(`captures=${report.captures.length}`);
    console.log(`screenshots=${report.captures.length}`);
    console.log(`issue_captures=${failed.length}`);
    console.log(`report=${reportPath}`);
    if (failed.length > 0) process.exitCode = 2;
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
