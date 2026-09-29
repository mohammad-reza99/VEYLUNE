const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '..', '..');
const contract = JSON.parse(fs.readFileSync(path.join(root, 'config', 'veylune-route-unification-contract.json'), 'utf8'));
const baseUrl = process.env.VEYLUNE_BASE_URL || 'https://veylune-shopware.ddev.site';
const legacyLink = /(?:href|action)=["']\/(?:categories|rooms|collections|discover|selection|objects)(?:[/?"'])/i;

async function request(route, redirect = 'follow') {
    const response = await fetch(`${baseUrl}${route}`, { redirect });
    return {
        response,
        body: await response.text(),
    };
}

async function run() {
    const report = {
        schemaVersion: contract.schemaVersion,
        capturedAt: new Date().toISOString(),
        baseUrl,
        canonical: [],
        aliases: [],
        preview: null,
        status: 'pass',
    };

    for (const route of contract.canonicalRoutes) {
        const result = await request(route);
        const row = {
            route,
            status: result.response.status,
            finalUrl: result.response.url,
            legacyInternalLink: legacyLink.test(result.body),
        };
        report.canonical.push(row);
        assert.equal(row.status, 200, `${route} must return 200`);
        assert.equal(row.legacyInternalLink, false, `${route} must not emit legacy discovery links`);
    }

    for (const alias of contract.aliases) {
        const result = await request(alias.path, 'manual');
        const location = result.response.headers.get('location');
        const row = { path: alias.path, status: result.response.status, location };
        report.aliases.push(row);
        assert.equal(row.status, alias.status, `${alias.path} redirect status`);
        assert.equal(row.location, alias.location, `${alias.path} redirect destination`);
    }

    const preview = await request(contract.privatePreview.path, 'manual');
    report.preview = {
        path: contract.privatePreview.path,
        status: preview.response.status,
        cacheControl: preview.response.headers.get('cache-control'),
        robots: preview.response.headers.get('x-robots-tag'),
    };
    assert.equal(report.preview.status, contract.privatePreview.unauthorizedStatus, 'preview must deny missing token');
    for (const [header, fragment] of Object.entries(contract.privatePreview.requiredHeaders)) {
        assert.match(preview.response.headers.get(header) || '', new RegExp(fragment, 'i'), `preview ${header}`);
    }

    console.log(JSON.stringify(report, null, 2));
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
