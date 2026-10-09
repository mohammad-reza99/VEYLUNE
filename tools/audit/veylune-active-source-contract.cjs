const fs = require('node:fs');
const path = require('node:path');

const root = process.cwd();
const themeRoot = path.join(root, 'custom/plugins/VeyluneTheme/src');
const viewRoot = path.join(themeRoot, 'Resources/views');
const storefrontRoot = path.join(themeRoot, 'Resources/app/storefront/src');
const componentRoot = path.join(storefrontRoot, 'scss/component');
const assetRoot = path.join(storefrontRoot, 'assets');
const imageAssetBudgetBytes = 512 * 1024;

const fail = (message) => {
    throw new Error(message);
};

const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');

const walk = (directory) => fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const absolute = path.join(directory, entry.name);
    return entry.isDirectory() ? walk(absolute) : [absolute];
});

const retiredActiveFiles = [
    'Resources/views/storefront/veylune/living-index-home.html.twig',
    'Resources/app/storefront/src/scss/component/_living-index-home-foundation.scss',
    'Resources/app/storefront/src/scss/component/_living-index-storefront.scss',
    'Resources/views/storefront/veylune/discovery-destination.html.twig',
    'Resources/views/storefront/veylune/living-index-discovery.html.twig',
    'Resources/views/storefront/veylune/living-index-search.html.twig',
    'Resources/views/storefront/veylune/living-index-search-results.html.twig',
    'Resources/views/storefront/veylune/living-index-selection.html.twig',
    'Resources/app/storefront/src/scss/component/_living-index-discovery.scss',
    'Resources/app/storefront/src/scss/component/_living-index-search-selection.scss',
    'Resources/app/storefront/src/scss/component/_living-index-wayfair-parity.scss',
];

retiredActiveFiles.forEach((relativePath) => {
    if (fs.existsSync(path.join(themeRoot, relativePath))) {
        fail(`Retired source is still active: ${relativePath}`);
    }
});

const activeFiles = walk(themeRoot);
const forbiddenArtifacts = activeFiles.filter((file) => /(?:\.orig|\.rej|\.tmp|~)$/i.test(file));
if (forbiddenArtifacts.length > 0) {
    fail(`Temporary artifacts remain active: ${forbiddenArtifacts.join(', ')}`);
}

const imageAssets = walk(assetRoot).filter((file) => /\.(?:avif|jpe?g|png|webp)$/i.test(file));
const oversizedImageAssets = imageAssets.filter((file) => fs.statSync(file).size > imageAssetBudgetBytes);
if (oversizedImageAssets.length > 0) {
    fail(`Image assets exceed the 512 KiB source budget: ${oversizedImageAssets.map((file) => path.relative(root, file)).join(', ')}`);
}

const activeTwig = walk(viewRoot).filter((file) => file.endsWith('.twig'));
const forbiddenRouteReferences = [
    'frontend.veylune.discovery.category',
    'frontend.veylune.discovery.room',
    'frontend.veylune.discovery.collection',
    'frontend.veylune.living_index.search',
    'frontend.veylune.living_index.selection',
];

for (const file of activeTwig) {
    const source = fs.readFileSync(file, 'utf8');
    for (const routeName of forbiddenRouteReferences) {
        if (source.includes(routeName)) {
            fail(`Legacy route reference ${routeName} remains in ${path.relative(root, file)}`);
        }
    }
}

const baseScss = read('custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/scss/base.scss');
const retiredImports = [
    'component/living-index-home-foundation',
    'component/living-index-storefront',
    'component/living-index-discovery',
    'component/living-index-search-selection',
    'component/living-index-wayfair-parity',
];
retiredImports.forEach((importName) => {
    if (baseScss.includes(importName)) fail(`Retired SCSS import remains: ${importName}`);
});

const imports = [...baseScss.matchAll(/@import\s+"([^"]+)";/g)].map((match) => match[1]);
const missingImports = imports.filter((importName) => {
    const parsed = path.parse(importName);
    const partial = path.join(storefrontRoot, 'scss', parsed.dir, `_${parsed.base}.scss`);
    return !fs.existsSync(partial);
});
if (missingImports.length > 0) fail(`SCSS imports do not resolve: ${missingImports.join(', ')}`);

const mainJs = read('custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/main.js');
const jsImports = [...mainJs.matchAll(/import\s+'\.\/js\/([^']+)'/g)].map((match) => match[1]);
if (new Set(jsImports).size !== jsImports.length) fail('Duplicate storefront JavaScript imports found.');
const missingJs = jsImports.filter((moduleName) => !fs.existsSync(path.join(storefrontRoot, 'js', `${moduleName}.js`)));
if (missingJs.length > 0) fail(`JavaScript imports do not resolve: ${missingJs.join(', ')}`);

const compatibilityControllers = [
    'custom/plugins/VeyluneTheme/src/Controller/DiscoveryDestinationController.php',
    'custom/plugins/VeyluneTheme/src/Controller/LivingIndexUtilityController.php',
];
compatibilityControllers.forEach((relativePath) => {
    const source = read(relativePath);
    if (source.includes('renderStorefront(')) fail(`Compatibility controller renders a competing UI: ${relativePath}`);
    if (!source.includes('redirectToRoute(')) fail(`Compatibility controller has no redirect boundary: ${relativePath}`);
});

const header = read('custom/plugins/VeyluneTheme/src/Resources/views/storefront/layout/header/header.html.twig');
if (!header.includes("veyluneHeaderSearchRoute = 'frontend.veylune.catalog.search'")) {
    fail('Header search is not owned by the canonical catalog route.');
}
const homepage = read('custom/plugins/VeyluneTheme/src/Resources/views/storefront/page/content/index.html.twig');
if (homepage.includes('living-index-home.html.twig') || homepage.includes('veyluneUseLivingIndexHome')) {
    fail('Homepage still contains the retired Living Index render path.');
}
if (!homepage.includes('catalog-marketplace-content.html.twig')) fail('Canonical marketplace homepage include is missing.');

const requiredArchiveFiles = retiredActiveFiles.map((relativePath) => path.basename(relativePath));
const archiveRoot = path.join(root, 'docs/archive/wave2-retired');
requiredArchiveFiles.forEach((fileName) => {
    if (!fs.existsSync(path.join(archiveRoot, fileName))) fail(`Retired source archive is incomplete: ${fileName}`);
});

const result = {
    status: 'pass',
    activeTwigFiles: activeTwig.length,
    scssImports: imports.length,
    javascriptModules: jsImports.length,
    retiredActiveFiles: 0,
    forbiddenLegacyRouteReferences: 0,
    imageAssets: imageAssets.length,
    oversizedImageAssets: 0,
    archivedRetiredFiles: requiredArchiveFiles.length,
};

process.stdout.write(`${JSON.stringify(result, null, 2)}\n`);
