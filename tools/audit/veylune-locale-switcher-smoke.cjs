// Local DDEV browser smoke for the canonical language and currency control.
const { chromium } = require(process.env.CODEX_PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');

const base = 'https://veylune-shopware.ddev.site';

(async () => {
    const browser = await chromium.launch({
        headless: true,
        executablePath: process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined,
    });
    const checks = [];

    try {
        const context = await browser.newContext({
            ignoreHTTPSErrors: true,
            viewport: { width: 1440, height: 1000 },
        });
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));

        await page.goto(base, { waitUntil: 'networkidle' });
        const trigger = page.locator('[data-veylune-locale-trigger]');
        const menu = page.locator('[data-veylune-locale-menu]');

        await expectText(trigger, 'EN / EUR');
        await trigger.click();
        await menu.waitFor({ state: 'visible' });
        assert.ok(await page.locator('[data-veylune-locale-language]').count() >= 2);
        assert.ok(await page.locator('[data-veylune-locale-currency]').count() >= 2);
        checks.push('combined switcher opens with language and currency choices');

        await page.keyboard.press('Escape');
        await menu.waitFor({ state: 'hidden' });
        assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
        checks.push('Escape closes the dropdown and synchronizes aria-expanded');

        await trigger.click();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.locator('[data-veylune-locale-currency="USD"]').click(),
        ]);
        await expectText(page.locator('[data-veylune-locale-trigger]'), 'EN / USD');
        checks.push('currency switch persists through the native Shopware configure route');

        await page.locator('[data-veylune-locale-trigger]').click();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.locator('[data-veylune-locale-language="de-DE"]').click(),
        ]);
        await expectText(page.locator('[data-veylune-locale-trigger]'), 'DE / USD');
        assert.match(await page.locator('html').getAttribute('lang'), /^de/i);
        checks.push('language switch persists through the native Shopware language route');

        assert.equal(
            await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1),
            false,
            'switcher must not create horizontal page overflow'
        );
        assert.deepEqual(errors, []);
        checks.push('no horizontal overflow or browser page errors');

        console.log(JSON.stringify({ status: 'pass', checks }, null, 2));
        await context.close();
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exitCode = 1;
});

async function expectText(locator, expected) {
    await locator.waitFor({ state: 'visible' });
    assert.equal((await locator.innerText()).trim(), expected);
}
