'use strict';

const { readdirSync, statSync } = require('node:fs');
const { join, relative } = require('node:path');
const { spawnSync } = require('node:child_process');

const root = process.cwd();
const targets = [
    join(root, 'custom/plugins/VeyluneTheme/src/Resources/app/storefront/src'),
    join(root, 'tools/audit'),
    join(root, 'tools/quality'),
    join(root, 'tests/e2e'),
];

function sourceFiles(directory) {
    const files = [];
    for (const entry of readdirSync(directory)) {
        const path = join(directory, entry);
        if (statSync(path).isDirectory()) {
            files.push(...sourceFiles(path));
        } else if (/\.(?:c?js)$/.test(entry)) {
            files.push(path);
        }
    }

    return files;
}

const files = targets.flatMap(sourceFiles).sort();
for (const file of files) {
    const result = spawnSync(process.execPath, ['--check', file], { stdio: 'inherit' });
    if (result.status !== 0) {
        process.exit(result.status ?? 1);
    }
}

console.log(`JavaScript syntax PASS (${files.length} files).`);
