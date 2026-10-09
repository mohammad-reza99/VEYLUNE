'use strict';

const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

const criticalRoutes = [
    ['home', '/'],
    ['catalog', '/catalog'],
    ['search', '/catalog/search?q=chair'],
    ['product', '/catalog/product/F02'],
    ['cart', '/checkout/cart'],
    ['account', '/account/login'],
    ['contact', '/contact-studio'],
];

test.describe('Veylune critical public journeys', () => {
    for (const [name, path] of criticalRoutes) {
        test(`${name} loads without runtime or server errors`, async ({ page }) => {
            const pageErrors = [];
            const serverErrors = [];
            page.on('pageerror', (error) => pageErrors.push(error.message));
            page.on('response', (response) => {
                if (response.status() >= 500) {
                    serverErrors.push(`${response.status()} ${response.url()}`);
                }
            });

            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
            expect(response?.status()).toBeLessThan(400);
            await expect(page.locator('body')).toBeVisible();
            expect(pageErrors).toEqual([]);
            expect(serverErrors).toEqual([]);
        });
    }

    test('catalog exposes active public cards and no draft status', async ({ page }) => {
        await page.goto('/catalog');
        const cards = page.locator('[data-discovery-card-scope="public"]');
        await expect(cards.first()).toBeVisible();
        expect(await cards.count()).toBeGreaterThan(0);
        await expect(page.getByText('Supplier Selection', { exact: true })).toHaveCount(0);
    });

    test('search returns a relevant active product', async ({ page }) => {
        await page.goto('/catalog/search?q=chair');
        await expect(page.locator('[data-discovery-card-scope="public"]').first()).toBeVisible();
        await expect(page.getByRole('heading', { name: /chair/i }).first()).toBeVisible();
    });

    test('PLP filter and sort controls match the responsive interaction', async ({ page, isMobile }) => {
        await page.goto('/catalog/category/furniture');
        if (!isMobile) {
            await expect(page.locator('.veylune-plp-filter-rail')).toBeVisible();
            await expect(page.locator('[data-plp-filter-toggle]')).toBeHidden();
            return;
        }

        const filterToggle = page.locator('[data-plp-filter-toggle]');
        const filterPanel = page.locator('[data-plp-filter-panel]');
        await filterToggle.click();
        await expect(filterPanel).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(filterPanel).toBeHidden();

        const sortToggle = page.locator('[data-plp-sort-toggle]');
        const sortPanel = page.locator('[data-plp-sort-panel]');
        await sortToggle.click();
        await expect(sortPanel).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(sortPanel).toBeHidden();
    });

    test('public product uses native Shopware cart', async ({ page }) => {
        await page.goto('/catalog/product/F02');
        const form = page.locator('form[data-add-to-cart="true"]');
        await expect(form).toBeVisible();
        await form.getByRole('button', { name: /add to cart/i }).click();
        await page.goto('/checkout/cart');
        await expect(page.getByText(/Liora Curved Sofa/i).first()).toBeVisible();
        await expect(page.getByText('Your cart is empty')).toHaveCount(0);
    });

    test('mobile pages do not overflow the viewport', async ({ page, isMobile }) => {
        test.skip(!isMobile, 'Mobile project only');
        for (const path of ['/', '/catalog/category/furniture', '/catalog/product/F02', '/checkout/cart']) {
            await page.goto(path, { waitUntil: 'domcontentloaded' });
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            expect(overflow, `${path} horizontal overflow`).toBeLessThanOrEqual(1);
        }
    });

    test('public pages expose complete indexable metadata and healthy images', async ({ page, isMobile }) => {
        test.skip(isMobile, 'Metadata is viewport-independent');

        for (const path of ['/', '/catalog', '/catalog/product/F02', '/contact-studio']) {
            await page.goto(path, { waitUntil: 'networkidle' });

            expect((await page.title()).trim(), `${path} title`).not.toBe('');
            await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', /\S/);
            await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', /^https:\/\/veylune-shopware\.ddev\.site\//);
            expect(await page.locator('h1').count(), `${path} H1 count`).toBe(1);

            const robots = await page.locator('meta[name="robots"]').getAttribute('content');
            expect(robots?.toLowerCase() ?? '', `${path} robots`).not.toContain('noindex');

            const brokenImages = await page.locator('img').evaluateAll((images) => images
                .filter((image) => image.currentSrc && (!image.complete || image.naturalWidth === 0))
                .map((image) => image.currentSrc));
            expect(brokenImages, `${path} broken images`).toEqual([]);
        }
    });

    test('homepage stays inside the initial asset budget', async ({ page, isMobile }) => {
        test.skip(isMobile, 'Performance budget is measured once on desktop');
        await page.goto('/', { waitUntil: 'networkidle' });

        const resources = await page.evaluate(() => performance.getEntriesByType('resource').map((entry) => ({
            name: entry.name,
            bytes: entry.encodedBodySize,
            type: entry.initiatorType,
        })));
        const criticalResources = resources.filter((entry) => ['css', 'font', 'img', 'script'].includes(entry.type));
        const initialBytes = criticalResources.reduce((total, entry) => total + entry.bytes, 0);
        const oversizedImages = resources.filter((entry) => entry.type === 'img' && entry.bytes > 512 * 1024);
        const legacyPngs = resources.filter((entry) => /\/veylune-(?:category|promo|room)-.+\.png(?:\?|$)/i.test(entry.name));

        expect(oversizedImages, 'Images larger than 512 KiB').toEqual([]);
        expect(legacyPngs, 'Legacy PNG variants loaded by the storefront').toEqual([]);
        expect(initialBytes, `Initial critical resources: ${initialBytes} bytes`).toBeLessThanOrEqual(3 * 1024 * 1024);
    });

    test('header overlays close through expected pointer and keyboard paths', async ({ page, isMobile }) => {
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        if (isMobile) {
            const mobileToggle = page.locator('[data-veylune-mobile-toggle]');
            await mobileToggle.click();
            await expect(mobileToggle).toHaveAttribute('aria-expanded', 'true');
            await page.keyboard.press('Escape');
            await expect(mobileToggle).toHaveAttribute('aria-expanded', 'false');
            return;
        }

        const megaTrigger = page.locator('[data-veylune-mega-trigger]').first();
        const mega = page.locator('[data-veylune-mega]');
        await megaTrigger.hover();
        await expect(mega).toHaveClass(/is-open/);
        await page.locator('main').hover({ position: { x: 10, y: 200 } });
        await expect(mega).not.toHaveClass(/is-open/);

        const localeTrigger = page.locator('[data-veylune-locale-trigger]');
        await localeTrigger.click();
        await expect(localeTrigger).toHaveAttribute('aria-expanded', 'true');
        await page.locator('main').click({ position: { x: 10, y: 200 } });
        await expect(localeTrigger).toHaveAttribute('aria-expanded', 'false');
    });

    test('rendered public navigation has no dead internal links', async ({ page, request, isMobile }) => {
        test.skip(isMobile, 'Internal route crawl is viewport-independent');
        const internalUrls = new Set();

        for (const path of ['/', '/catalog', '/catalog/category/furniture', '/catalog/product/F02']) {
            await page.goto(path, { waitUntil: 'domcontentloaded' });
            const publicOrigin = new URL(page.url()).origin;
            const links = await page.locator('a[href]').evaluateAll((anchors) => anchors.map((anchor) => anchor.href));
            for (const href of links) {
                const url = new URL(href);
                if (url.origin !== publicOrigin || url.hash || url.pathname === '/account/logout') continue;
                internalUrls.add(`${url.pathname}${url.search}`);
            }
        }

        for (const url of [...internalUrls].slice(0, 80)) {
            const response = await request.get(url, { maxRedirects: 8 });
            expect(response.status(), `${url} returned ${response.status()}`).toBeLessThan(400);
        }
    });

    test('critical surfaces have no serious or critical automated accessibility violations', async ({ page }) => {
        for (const path of ['/', '/catalog/category/furniture', '/catalog/product/F02', '/account/login']) {
            await page.goto(path, { waitUntil: 'domcontentloaded' });
            const results = await new AxeBuilder({ page })
                .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
                .analyze();
            const blocking = results.violations.filter((violation) => ['serious', 'critical'].includes(violation.impact));
            expect(blocking, `${path}: ${blocking.map((item) => item.id).join(', ')}`).toEqual([]);
        }
    });
});
