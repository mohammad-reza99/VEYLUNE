// Local-only smoke test. Mutates an anonymous test cart, never submits an order.
const { chromium } = require(process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const base = 'https://veylune-shopware.ddev.site/__commerce-test';
const output = process.env.VEYLUNE_TEST_OUTPUT || path.resolve('var/local-commerce-smoke');
const id = i => crypto.createHash('md5').update('veylune-local-commerce-product-' + i).digest('hex');
const results = [];
(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true, executablePath: process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined });
  try {
    const ctx = await browser.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 1000 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    const visit = async route => {
      const r = await page.goto(base + route, { waitUntil: 'networkidle' });
      assert.equal(r.status(), 200, route);
      assert.match(r.headers()['x-robots-tag'], /noindex/);
      assert.match(r.headers()['cache-control'], /no-store/);
    };
    await visit('/test-products');
    assert.equal(await page.locator('h3 a').count(), 10);
    results.push('catalog: 10 test products; noindex and no-store');
    const consent = page.getByRole('button', { name: 'Only technically required', exact: true });
    if (await consent.isVisible()) await consent.click();
    await visit('/detail/' + id(0));
    await page.locator('.btn-buy').first().click();
    await page.waitForTimeout(800);
    const miniCartCheckout = page.locator('.offcanvas .begin-checkout-btn');
    await miniCartCheckout.waitFor({ state: 'visible' });
    assert.match(await miniCartCheckout.getAttribute('href'), /\/checkout\//);
    assert.doesNotMatch(await miniCartCheckout.getAttribute('href'), /consultation/);
    await page.screenshot({ path: path.join(output, 'mini-cart.png'), fullPage: true });
    results.push('mini-cart checkout points to native checkout, not consultation');
    await visit('/checkout/cart');
    assert.equal(await page.locator('input[name=quantity]').inputValue(), '1');
    assert.match(await page.locator('[data-cart-widget]').innerText(), /Cart\s+1/);
    results.push('native add-to-cart and header counter');
    await page.locator('input[name=quantity]').fill('2');
    await page.locator('input[name=quantity]').press('Tab');
    await page.waitForTimeout(1500);
    await visit('/checkout/cart');
    assert.equal(await page.locator('input[name=quantity]').inputValue(), '2');
    assert.match(await page.locator('body').innerText(), /9,809.90/);
    results.push('quantity persists; two units plus shipping total EUR 9809.90');
    for (const width of [390, 768, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      await page.screenshot({ path: path.join(output, 'cart-' + width + '.png'), fullPage: true });
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1);
      assert.equal(overflow, false, 'cart horizontal overflow at ' + width);
      const quantityButtons = page.locator('.quantity-selector-group button');
      if (await quantityButtons.count() === 2) {
        const a = await quantityButtons.first().boundingBox(), b = await quantityButtons.last().boundingBox();
        assert.ok(Math.abs(a.y - b.y) < 2, 'quantity buttons stay on one row');
      }
    }
    results.push('cart no horizontal overflow at 390/768/1440');
    await page.locator('form[action*="/line-item/delete/"] button[type=submit]').click();
    await page.waitForTimeout(1000);
    await visit('/checkout/cart');
    assert.equal(await page.locator('input[name=quantity]').count(), 0);
    assert.match(await page.locator('[data-cart-widget]').innerText(), /Cart\s+0/);
    results.push('remove item; empty cart and counter reset');
    await visit('/detail/' + id(9));
    assert.equal(await page.locator('.btn-buy:enabled').count(), 0);
    results.push('out-of-stock product has no enabled buy button');
    const publicResponse = await ctx.request.get('https://veylune-shopware.ddev.site/test-products');
    assert.equal(publicResponse.status(), 404);
    results.push('test catalog denied outside test channel');
    assert.deepEqual(errors, []);
    fs.writeFileSync(path.join(output, 'result.json'), JSON.stringify({ status: 'pass', results }, null, 2));
    console.log(JSON.stringify({ status: 'pass', results }, null, 2));
  } catch (error) {
    fs.writeFileSync(path.join(output, 'result.json'), JSON.stringify({ status: 'fail', results, error: error.message }, null, 2));
    throw error;
  } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
