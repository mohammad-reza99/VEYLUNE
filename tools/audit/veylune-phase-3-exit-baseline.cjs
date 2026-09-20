const fs = require('node:fs');
const path = require('node:path');

const playwrightModule = process.argv[2] || process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright';
const browserExecutable = process.argv[3] || process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined;
const surfaceContractPath = process.argv[4] || path.resolve(process.cwd(), 'phase3-surface-contract.json');
const outputRoot = process.env.VEYLUNE_PHASE3_OUTPUT || path.resolve(process.cwd(), 'phase-3-exit');
const baseUrl = process.env.VEYLUNE_BASE_URL || 'https://veylune-shopware.ddev.site';
const { chromium } = require(playwrightModule);

const surfaceContract = JSON.parse(fs.readFileSync(surfaceContractPath, 'utf8'));
const routeDefinitions = surfaceContract.public.groups.flatMap((group) => group.routes.map((route) => ({
    group: group.id,
    owner: group.owner,
    route,
})));
const viewports = [
    { id: 'desktop', width: 1440, height: 1000 },
    { id: 'mobile', width: 390, height: 844 },
];
const expectedDirect = {
    '/about': { statuses: [301], location: '/about-studio' },
    '/account': { statuses: [302, 303], locationPrefix: '/account/login' },
    '/checkout/confirm': { statuses: [303], location: '/checkout/cart?checkout=guarded' },
    '/wishlist': { statuses: [302], location: '/selection?source=wishlist' },
};
const canonicalRedirects = [
    { path: '/collections/permanent-collections', status: 301, location: '/collections/permanent' },
    { path: '/collections/editorial-collections', status: 301, location: '/collections/editorial' },
    { path: '/consultation', status: 301, location: '/private-consultation' },
];

function safeName(value) {
    if (value === '/') return 'home';
    return value.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'route';
}

async function directOutcome(request, route) {
    try {
        const response = await request.get(`${baseUrl}${route}`, { maxRedirects: 0, failOnStatusCode: false, timeout: 10000 });
        const outcome = { status: response.status(), location: response.headers().location || null, error: null };
        await response.dispose();
        return outcome;
    } catch (error) {
        return { status: null, location: null, error: error.message };
    }
}

async function dismissCookie(page) {
    const button = page.getByRole('button', { name: /Only technically required/i });
    if (await button.count() && await button.first().isVisible()) {
        await button.first().click();
        await page.waitForTimeout(120);
    }
}

async function keyboardEvidence(page) {
    await page.locator('body').focus();
    const samples = [];
    const seen = new Set();
    for (let index = 0; index < 10; index += 1) {
        await page.keyboard.press('Tab');
        const sample = await page.evaluate(() => {
            const element = document.activeElement;
            if (!(element instanceof HTMLElement) || element === document.body) return null;
            const style = getComputedStyle(element);
            const focusFrames = [element, element.parentElement, element.parentElement?.parentElement].filter(Boolean);
            const hasVisibleFocusFrame = focusFrames.some((frame) => {
                const frameStyle = getComputedStyle(frame);
                const frameOutline = Number.parseFloat(frameStyle.outlineWidth) || 0;
                return (frameOutline >= 2 && frameStyle.outlineStyle !== 'none') || (frameStyle.boxShadow !== 'none' && frameStyle.boxShadow !== '');
            });
            const rect = element.getBoundingClientRect();
            const name = (element.getAttribute('aria-label') || element.textContent || element.getAttribute('title') || '').trim().replace(/\s+/g, ' ').slice(0, 120);
            const outline = Number.parseFloat(style.outlineWidth) || 0;
            const shadow = style.boxShadow;
            return {
                tag: element.tagName.toLowerCase(),
                name,
                outlineWidth: style.outlineWidth,
                outlineStyle: style.outlineStyle,
                boxShadow: shadow,
                visibleIndicator: hasVisibleFocusFrame,
                inViewport: rect.bottom >= 0 && rect.top <= window.innerHeight && rect.right >= 0 && rect.left <= window.innerWidth,
            };
        });
        if (!sample) continue;
        const key = `${sample.tag}:${sample.name}`;
        if (!seen.has(key)) {
            seen.add(key);
            samples.push(sample);
        }
    }
    return samples;
}

async function inspectPage(page) {
    return page.evaluate(() => {
        const visible = (element) => {
            if (!(element instanceof HTMLElement)) return false;
            const style = getComputedStyle(element);
            const rect = element.getBoundingClientRect();
            return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
        };
        const accessibleName = (element) => {
            const labelledBy = (element.getAttribute('aria-labelledby') || '').split(/\s+/).filter(Boolean)
                .map((id) => document.getElementById(id)?.textContent || '').join(' ').trim();
            const nativeLabels = element.labels ? [...element.labels].map((label) => label.textContent || '').join(' ').trim() : '';
            return (element.getAttribute('aria-label') || labelledBy || nativeLabels || element.getAttribute('alt') || element.getAttribute('title') || element.textContent || element.value || '').trim();
        };
        const interactive = [...document.querySelectorAll('a[href], button, input:not([type="hidden"]), select, textarea, summary, [role="button"]')].filter(visible);
        const missingAccessibleNames = interactive.filter((element) => !accessibleName(element)).map((element) => element.outerHTML.slice(0, 220));
        const emptyTargets = [...document.querySelectorAll('a, button')].filter((element) => {
            if (!visible(element)) return false;
            const name = accessibleName(element);
            if (!name) return true;
            if (element.tagName === 'A') {
                const href = (element.getAttribute('href') || '').trim();
                return href === '' || href === '#';
            }
            return false;
        }).map((element) => element.outerHTML.slice(0, 220));
        const brokenImages = [...document.images].filter((image) => image.complete && image.naturalWidth === 0).map((image) => image.currentSrc || image.src);
        const missingImageAlt = [...document.images].filter((image) => visible(image) && !image.hasAttribute('alt')).map((image) => image.currentSrc || image.src);
        const nestedInteractive = [...document.querySelectorAll('a a, a button, button a, button button, summary a, summary button')].map((element) => element.outerHTML.slice(0, 220));
        const unresolvedFragments = [...document.querySelectorAll('a[href^="#"]')].filter((anchor) => {
            const value = anchor.getAttribute('href');
            return value && value !== '#' && !document.getElementById(decodeURIComponent(value.slice(1)));
        }).map((anchor) => anchor.getAttribute('href'));
        const touchTargets = [...document.querySelectorAll('button, input:not([type="hidden"]), select, summary, .btn, [role="button"], [data-veylune-header-account], [data-veylune-header-cart], [data-veylune-mobile-toggle]')]
            .filter(visible)
            .map((element) => {
                const rect = element.getBoundingClientRect();
                return {
                    tag: element.tagName.toLowerCase(),
                    name: accessibleName(element).replace(/\s+/g, ' ').slice(0, 100),
                    width: Math.round(rect.width * 100) / 100,
                    height: Math.round(rect.height * 100) / 100,
                    exception: element.matches('input[type="checkbox"], input[type="radio"], .btn-link-inline') || Boolean(element.closest('.data-protection-information')),
                };
            });
        const links = [...document.querySelectorAll('a[href]')].filter(visible).map((anchor) => ({
            href: anchor.href,
            rawHref: anchor.getAttribute('href'),
            label: accessibleName(anchor).replace(/\s+/g, ' ').slice(0, 160),
        }));
        const forms = [...document.querySelectorAll('form')].filter(visible).map((form) => ({
            action: form.action,
            method: (form.method || 'get').toUpperCase(),
            accessibleName: accessibleName(form),
            unnamedControls: [...form.querySelectorAll('input:not([type="hidden"]), select, textarea')].filter(visible).filter((control) => !control.name).length,
        }));
        const oldCmsMarkers = [...document.querySelectorAll('[class^="veylune-legacy"], [class*=" veylune-legacy"], [data-veylune-legacy]')]
            .filter(visible).map((element) => element.className || element.getAttribute('data-veylune-legacy') || element.tagName).slice(0, 30);
        const bodyText = (document.body?.innerText || '').toLowerCase();
        const privatePreviewMarkers = ['__veylune-preview', 'private preview', 'preview catalog', 'development preview'].filter((marker) => bodyText.includes(marker));
        return {
            title: document.title,
            lang: document.documentElement.lang,
            mainCount: document.querySelectorAll('main').length,
            h1Count: document.querySelectorAll('h1').length,
            headerPresent: Boolean(document.querySelector('header, .veylune-header')),
            footerPresent: Boolean(document.querySelector('footer, .veylune-footer')),
            horizontalOverflow: document.documentElement.scrollWidth > window.innerWidth + 1,
            missingAccessibleNames,
            emptyTargets,
            brokenImages,
            missingImageAlt,
            nestedInteractive,
            unresolvedFragments,
            touchTargets,
            links,
            forms,
            oldCmsMarkers,
            privatePreviewMarkers,
            checkoutGuard: Boolean(document.querySelector('[data-veylune-checkout-guard]')),
            publicWishlistControls: document.querySelectorAll('.veylune-product-card__wishlist, .veylune-header__utility[title*="Wishlist"], [data-wishlist]').length,
        };
    });
}

async function shellInteractions(browser) {
    const results = {};
    const desktop = await browser.newContext({ viewport: { width: 1440, height: 1000 }, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const page = await desktop.newPage();
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
    await dismissCookie(page);
    const trigger = page.locator('[data-veylune-mega-trigger]').first();
    if (await trigger.count()) {
        await trigger.focus();
        await page.waitForTimeout(100);
        const expandedAfterFocus = await trigger.getAttribute('aria-expanded');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(100);
        const escapeClosed = await trigger.getAttribute('aria-expanded');
        const focusRestored = await trigger.evaluate((element) => document.activeElement === element);
        await trigger.focus();
        await page.waitForTimeout(100);
        await page.mouse.click(12, 920);
        await page.waitForTimeout(100);
        const outsideClosed = await trigger.getAttribute('aria-expanded');
        await trigger.hover();
        await page.waitForTimeout(100);
        await page.mouse.move(12, 920);
        await page.waitForTimeout(220);
        const leaveClosed = await trigger.getAttribute('aria-expanded');
        results.desktopMega = {
            expandedAfterFocus: expandedAfterFocus === 'true',
            escapeClosed: escapeClosed === 'false',
            focusRestored,
            outsideClosed: outsideClosed === 'false',
            pointerLeaveClosed: leaveClosed === 'false',
        };
    }
    await desktop.close();

    const mobile = await browser.newContext({ viewport: { width: 390, height: 844 }, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
    const mobilePage = await mobile.newPage();
    await mobilePage.goto(baseUrl, { waitUntil: 'domcontentloaded' });
    await dismissCookie(mobilePage);
    const toggle = mobilePage.locator('[data-veylune-mobile-toggle]').first();
    if (await toggle.count()) {
        await toggle.click();
        await mobilePage.waitForTimeout(240);
        const open = await toggle.getAttribute('aria-expanded');
        const drawer = mobilePage.locator('[data-veylune-mobile-nav], .veylune-mobile-nav').first();
        const contained = await drawer.evaluate((element) => {
            const rect = element.getBoundingClientRect();
            return rect.left >= -1 && rect.right <= window.innerWidth + 1;
        });
        const focusInside = await mobilePage.evaluate(() => Boolean(document.activeElement?.closest('[data-veylune-mobile-nav], .veylune-mobile-nav')));
        await mobilePage.keyboard.press('Escape');
        await mobilePage.waitForTimeout(100);
        results.mobileDrawer = {
            opened: open === 'true',
            contained,
            focusInside,
            escapeClosed: await toggle.getAttribute('aria-expanded') === 'false',
            focusRestored: await toggle.evaluate((element) => document.activeElement === element),
        };
    }
    await mobile.close();
    return results;
}

async function run() {
    fs.mkdirSync(outputRoot, { recursive: true });
    const browser = await chromium.launch({ headless: true, executablePath: browserExecutable });
    const reportPath = path.join(outputRoot, 'phase-3-exit-baseline.json');
    const resumeFromCheckpoint = process.env.VEYLUNE_PHASE3_RESUME === '1' && fs.existsSync(reportPath);
    const report = resumeFromCheckpoint ? JSON.parse(fs.readFileSync(reportPath, 'utf8')) : {
        schemaVersion: '1.0',
        phase: '3.1H',
        capturedAt: new Date().toISOString(),
        reference: {
            url: 'https://www.wayfair.com/',
            capturedAt: '2026-09-20',
            observedPatterns: ['utility services', 'dominant search', 'account and cart actions', 'primary discovery navigation', 'department navigation', 'dense internal discovery paths'],
        },
        baseUrl,
        routeDefinitions,
        viewports,
        captures: [],
        canonicalRedirects: [],
        internalActions: [],
        shellInteractions: {},
        specialStates: {},
    };
    const internalActions = new Map();

    if (resumeFromCheckpoint) {
        report.internalActions = [];
        report.canonicalRedirects = [];
        for (const capture of report.captures) {
            for (const link of capture.computed.links) {
                try {
                    const url = new URL(link.href);
                    if (url.origin === new URL(baseUrl).origin && !url.pathname.startsWith('/logout')) {
                        url.hash = '';
                        const key = `${url.pathname}${url.search}`;
                        if (!internalActions.has(key)) internalActions.set(key, { href: key, sources: [] });
                        const action = internalActions.get(key);
                        if (action.sources.length < 8) action.sources.push({ route: capture.route, label: link.label });
                    }
                } catch {}
            }
        }
    }

    if (!resumeFromCheckpoint) for (const viewport of viewports) {
        const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height }, ignoreHTTPSErrors: true, reducedMotion: 'reduce' });
        for (const definition of routeDefinitions) {
            const page = await context.newPage();
            const errors = { consoleErrors: [], pageErrors: [], failedResponses: [] };
            page.on('console', (message) => { if (message.type() === 'error') errors.consoleErrors.push(message.text()); });
            page.on('pageerror', (error) => errors.pageErrors.push(error.message));
            page.on('response', (response) => { if (response.status() >= 400) errors.failedResponses.push({ status: response.status(), url: response.url() }); });
            const direct = await directOutcome(context.request, definition.route);
            const response = await page.goto(`${baseUrl}${definition.route}`, { waitUntil: 'domcontentloaded', timeout: 45000 });
            await dismissCookie(page);
            await page.waitForTimeout(180);
            const computed = await inspectPage(page);
            const screenshot = `${viewport.id}--${safeName(definition.route)}.png`;
            await page.screenshot({ path: path.join(outputRoot, screenshot), fullPage: false });
            const keyboard = await keyboardEvidence(page);
            for (const link of computed.links) {
                try {
                    const url = new URL(link.href);
                    if (url.origin === new URL(baseUrl).origin && !url.pathname.startsWith('/logout')) {
                        url.hash = '';
                        const key = `${url.pathname}${url.search}`;
                        if (!internalActions.has(key)) internalActions.set(key, { href: key, sources: [] });
                        const action = internalActions.get(key);
                        if (action.sources.length < 8) action.sources.push({ route: definition.route, label: link.label });
                    }
                } catch {}
            }
            const expected = expectedDirect[new URL(`${baseUrl}${definition.route}`).pathname] || { statuses: [200] };
            let directAccepted = expected.statuses.includes(direct.status);
            if (expected.location) directAccepted = directAccepted && direct.location === expected.location;
            if (expected.locationPrefix) directAccepted = directAccepted && (direct.location || '').startsWith(expected.locationPrefix);
            report.captures.push({
                viewport: viewport.id,
                ...definition,
                direct,
                directAccepted,
                followedStatus: response?.status() || null,
                finalUrl: page.url(),
                screenshot,
                computed,
                keyboard,
                errors,
            });
            await page.close();
        }
        await context.close();
    }
    fs.writeFileSync(reportPath, `${JSON.stringify(report, null, 2)}\n`);

    const requestContext = await browser.newContext({ ignoreHTTPSErrors: true });
    for (const redirect of canonicalRedirects) {
        const outcome = await directOutcome(requestContext.request, redirect.path);
        report.canonicalRedirects.push({ ...redirect, outcome, pass: outcome.status === redirect.status && outcome.location === redirect.location });
    }
    const actions = [...internalActions.values()];
    const actionResults = new Array(actions.length);
    let actionIndex = 0;
    const actionWorker = async () => {
        while (actionIndex < actions.length) {
            const currentIndex = actionIndex;
            actionIndex += 1;
            const action = actions[currentIndex];
            const direct = await directOutcome(requestContext.request, action.href);
            try {
                const followed = await requestContext.request.get(`${baseUrl}${action.href}`, { maxRedirects: 8, failOnStatusCode: false, timeout: 10000 });
                const followedStatus = followed.status();
                const finalUrl = followed.url();
                await followed.dispose();
                actionResults[currentIndex] = { ...action, directStatus: direct.status, directLocation: direct.location, directError: direct.error, followedStatus, finalUrl, error: null, pass: [200, 204].includes(followedStatus) };
            } catch (error) {
                actionResults[currentIndex] = { ...action, directStatus: direct.status, directLocation: direct.location, directError: direct.error, followedStatus: null, finalUrl: null, error: error.message, pass: false };
            }
        }
    };
    await Promise.all(Array.from({ length: Math.min(4, actions.length) }, () => actionWorker()));
    report.internalActions.push(...actionResults);
    await requestContext.close();
    fs.writeFileSync(reportPath, `${JSON.stringify(report, null, 2)}\n`);

    const specialContext = await browser.newContext({ ignoreHTTPSErrors: true });
    const zero = await specialContext.request.get(`${baseUrl}/discover?q=zxqv987654321`, { failOnStatusCode: false, timeout: 10000 });
    const zeroBody = await zero.text();
    const unknown = await specialContext.request.get(`${baseUrl}/__phase-3-exit/not-found`, { failOnStatusCode: false, timeout: 10000 });
    const unknownBody = await unknown.text();
    report.specialStates = {
        zeroResult: { status: zero.status(), countMarker: zeroBody.includes('<strong>0</strong> results'), recoveryCopy: zeroBody.includes('No confident index match') },
        controlled404: { status: unknown.status(), releaseMarker: unknownBody.includes('data-veylune-release-error'), noIndex: unknownBody.includes('noindex,nofollow') },
    };
    await zero.dispose();
    await unknown.dispose();
    await specialContext.close();
    report.shellInteractions = await shellInteractions(browser);

    const currentFailures = report.captures.filter((capture) => !capture.directAccepted
        || capture.followedStatus !== 200
        || capture.computed.mainCount !== 1
        || capture.computed.h1Count < 1
        || !capture.computed.headerPresent
        || !capture.computed.footerPresent
        || capture.computed.horizontalOverflow
        || capture.computed.missingAccessibleNames.length
        || capture.computed.emptyTargets.length
        || capture.computed.brokenImages.length
        || capture.computed.missingImageAlt.length
        || capture.computed.nestedInteractive.length
        || capture.computed.unresolvedFragments.length
        || capture.computed.privatePreviewMarkers.length
        || capture.computed.publicWishlistControls > 0
        || capture.keyboard.length === 0
        || capture.keyboard.some((sample) => !sample.visibleIndicator)
        || Object.values(capture.errors).some((items) => items.length));
    const undersizedMobileTargets = report.captures.filter((capture) => capture.viewport === 'mobile')
        .flatMap((capture) => capture.computed.touchTargets.map((target) => ({ route: capture.route, ...target })))
        .filter((target) => !target.exception && (target.width < 44 || target.height < 44));
    const oldCmsSurfaces = report.captures.filter((capture) => capture.computed.oldCmsMarkers.length > 0).map((capture) => ({ viewport: capture.viewport, route: capture.route, markers: capture.computed.oldCmsMarkers }));
    const formOccurrences = report.captures.flatMap((capture) => capture.computed.forms.map((form) => ({ route: capture.route, viewport: capture.viewport, ...form })));
    const uniqueFormActions = new Set(formOccurrences.map((form) => `${form.method}:${form.action}`));
    const invalidForms = formOccurrences.filter((form) => !form.action || !['GET', 'POST'].includes(form.method) || form.unnamedControls > 0);
    report.summary = {
        routeCount: routeDefinitions.length,
        surfaceCount: report.captures.length,
        screenshotCount: report.captures.length,
        currentFailureSurfaces: currentFailures.map((capture) => `${capture.viewport}:${capture.route}`),
        internalActionCount: report.internalActions.length,
        failedInternalActions: report.internalActions.filter((action) => !action.pass).map((action) => action.href),
        failedCanonicalRedirects: report.canonicalRedirects.filter((redirect) => !redirect.pass).map((redirect) => redirect.path),
        formOccurrenceCount: formOccurrences.length,
        uniqueFormActionCount: uniqueFormActions.size,
        invalidForms,
        undersizedMobileTargets,
        oldCmsSurfaces,
        overflowSurfaces: report.captures.filter((capture) => capture.computed.horizontalOverflow).length,
        brokenImageSurfaces: report.captures.filter((capture) => capture.computed.brokenImages.length).length,
        publicWishlistControlSurfaces: report.captures.filter((capture) => capture.computed.publicWishlistControls > 0).length,
        privatePreviewCopySurfaces: report.captures.filter((capture) => capture.computed.privatePreviewMarkers.length).length,
        runtimeIssueSurfaces: report.captures.filter((capture) => Object.values(capture.errors).some((items) => items.length)).length,
    };
    fs.writeFileSync(reportPath, `${JSON.stringify(report, null, 2)}\n`);
    await browser.close();
    console.log(JSON.stringify(report.summary));
    console.log(`report=${reportPath}`);
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
