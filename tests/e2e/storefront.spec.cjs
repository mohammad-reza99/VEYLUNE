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
