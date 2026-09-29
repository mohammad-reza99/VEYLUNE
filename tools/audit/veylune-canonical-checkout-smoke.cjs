// DDEV-only canonical checkout: creates one synthetic guest and one no-charge test order.
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const base = 'https://veylune-shopware.ddev.site';
const output = process.env.VEYLUNE_TEST_OUTPUT || path.resolve('var/canonical-checkout-smoke');
const paymentId = '019f7a00000070008000000000000003';
const shippingId = '019f7a00000070008000000000000005';

(async () => {
    assert.equal(process.env.VEYLUNE_ALLOW_TEST_ORDER, '1', 'Set VEYLUNE_ALLOW_TEST_ORDER=1 to create the local no-charge order.');
    fs.mkdirSync(output, { recursive: true });
    const browser = await chromium.launch({
        headless: true,
        executablePath: process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined,
    });
    const checks = [];
    let orderId = null;

    try {
        const context = await browser.newContext({ ignoreHTTPSErrors: true });
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));

        await page.goto(base + '/catalog/test-cart/add/F02', { waitUntil: 'networkidle' });
        assert.match(page.url(), /\/checkout\/cart/);
        assert.equal(await page.locator('.line-item').count(), 1, 'canonical cart has one real Shopware line item');
        const proceed = page.locator('.checkout-aside-action .begin-checkout-btn');
        assert.match(await proceed.getAttribute('href'), /\/checkout\/confirm$/);
        checks.push('canonical cart exposes native checkout action');

        await page.goto(base + '/checkout/register', { waitUntil: 'networkidle' });
        const fields = {
            'billingAddress[firstName]': 'Veylune',
            'billingAddress[lastName]': 'Canonical QA',
            email: 'canonical-qa-' + Date.now() + '@example.invalid',
            'billingAddress[street]': 'Teststrasse 1',
            'billingAddress[zipcode]': '10115',
            'billingAddress[city]': 'Berlin',
        };
        for (const [name, value] of Object.entries(fields)) {
            await page.locator('[name="' + name + '"]').fill(value);
        }
        await page.locator('form[action$="/account/register"] button[type=submit]').click();
        await page.waitForURL('**/checkout/confirm');
        await page.waitForLoadState('networkidle');
        checks.push('fresh guest registration reaches canonical confirm');

        assert.ok(await page.locator('#paymentMethod' + paymentId).isChecked(), 'no-charge payment is selected');
        assert.ok(await page.locator('#shippingMethod' + shippingId).isChecked(), 'fixed test shipping is selected');
        assert.match(await page.locator('body').innerText(), /Place test order - no charge/);
        checks.push('local no-charge payment and fixed shipping selected');

        const consent = page.getByRole('button', { name: 'Only technically required', exact: true });
        if (await consent.isVisible()) await consent.click();

        for (const width of [390, 768, 1440]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.screenshot({ path: path.join(output, 'checkout-' + width + '.png'), fullPage: true });
            assert.equal(
                await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1),
                false,
                'horizontal overflow at ' + width
            );
            const submitBox = await page.locator('#confirmFormSubmit').boundingBox();
            assert.ok(submitBox && submitBox.x >= 0 && submitBox.x + submitBox.width <= width + 1, 'submit visible at ' + width);
            if (width === 1440) {
                const main = await page.locator('.checkout-main').boundingBox();
                const aside = await page.locator('.checkout-aside').boundingBox();
                assert.ok(aside.x > main.x + main.width - 1, 'desktop summary is alongside checkout');
            }
        }
        checks.push('confirm passes 390, 768 and 1440 responsive geometry');

        await page.locator('#tos').uncheck();
        await page.locator('#confirmFormSubmit').click();
        await page.waitForLoadState('networkidle');
        assert.ok(!page.url().includes('/checkout/finish'), 'unchecked terms cannot place an order');
        assert.ok(await page.locator('.is-invalid, .alert-danger').count() > 0, 'terms validation is visible');
        checks.push('unchecked terms are rejected with visible feedback');

        await page.locator('#tos').check();
        await page.locator('#confirmFormSubmit').click();
        await page.waitForURL('**/checkout/finish?**');
        await page.waitForLoadState('networkidle');
        orderId = new URL(page.url()).searchParams.get('orderId');
        assert.match(orderId, /^[a-f0-9]{32}$/);
        assert.match(await page.locator('body').innerText(), /Your test order has been received\./);
        await page.screenshot({ path: path.join(output, 'finish-1440.png'), fullPage: true });
        checks.push('one no-charge canonical order reaches the final success page');

        assert.deepEqual(errors, [], 'no browser page errors');
        checks.push('no browser page errors');

        const result = { status: 'pass', orderId, checks };
        fs.writeFileSync(path.join(output, 'result.json'), JSON.stringify(result, null, 2));
        console.log(JSON.stringify(result, null, 2));
    } catch (error) {
        fs.writeFileSync(path.join(output, 'result.json'), JSON.stringify({ status: 'fail', orderId, checks, error: error.message }, null, 2));
        throw error;
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
