'use strict';

const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');

const root = process.cwd();
const trackedFiles = execFileSync('git', ['ls-files', '-z'], { cwd: root })
    .toString('utf8')
    .split('\0')
    .filter(Boolean);
const signatures = [
    ['private key', /-----BEGIN (?:EC |OPENSSH |RSA |DSA )?PRIVATE KEY-----/],
    ['AWS access key', /\bAKIA[0-9A-Z]{16}\b/],
    ['GitHub token', /\bgh[pousr]_[A-Za-z0-9]{36,255}\b/],
    ['GitLab token', /\bglpat-[A-Za-z0-9_-]{20,}\b/],
    ['Stripe live secret', /\bsk_live_[A-Za-z0-9]{20,}\b/],
    ['Slack token', /\bxox[baprs]-[A-Za-z0-9-]{20,}\b/],
    ['credential in URL', /https?:\/\/[^\s/:]+:[^\s/@]+@[^\s/]+/],
];
const findings = [];

for (const relativePath of trackedFiles) {
    const absolutePath = path.join(root, relativePath);
    if (!fs.existsSync(absolutePath) || fs.statSync(absolutePath).size > 5 * 1024 * 1024) continue;

    const source = fs.readFileSync(absolutePath);
    if (source.includes(0)) continue;
    const text = source.toString('utf8');

    for (const [name, pattern] of signatures) {
        if (pattern.test(text)) findings.push(`${relativePath} (${name})`);
    }
}

if (findings.length > 0) {
    process.stderr.write(`Potential secrets found in tracked files:\n${findings.map((item) => `- ${item}`).join('\n')}\n`);
    process.exitCode = 1;
} else {
    process.stdout.write(`Secret scan PASS (${trackedFiles.length} tracked files, high-confidence signatures).\n`);
}
