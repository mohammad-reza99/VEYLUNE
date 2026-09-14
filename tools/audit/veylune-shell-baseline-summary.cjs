const fs = require('node:fs');

const report = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
for (const capture of report.captures) {
    const elements = capture.computed.elements;
    const errorCount = capture.errors.consoleErrors.length + capture.errors.pageErrors.length + capture.errors.failedResponses.length;
    const value = (selector, field) => elements[selector]?.rect?.[field] ?? 0;
    console.log([
        capture.viewport,
        capture.route,
        `status=${capture.status}`,
        `overflow=${capture.computed.document.horizontalOverflow}`,
        `errors=${errorCount}`,
        `headerH=${value('[data-veylune-header]', 'height')}`,
        `utilityH=${value('.veylune-marketplace-utility', 'height')}`,
        `barH=${value('.veylune-header__bar', 'height')}`,
        `searchW=${value('.veylune-marketplace-search', 'width')}`,
        `railH=${value('.veylune-marketplace-department-rail', 'height')}`,
    ].join('\t'));
}
console.log('--- HOME INTERACTIONS ---');
for (const capture of report.captures.filter((entry) => entry.route === 'home')) {
    console.log(JSON.stringify({ viewport: capture.viewport, interactions: capture.interactions }));
}
console.log('--- HOME TYPOGRAPHY ---');
for (const capture of report.captures.filter((entry) => entry.route === 'home')) {
    const elements = capture.computed.elements;
    const pick = (selector) => {
        const item = elements[selector];
        if (!item) return null;
        return {
            selector,
            display: item.display,
            fontFamily: item.fontFamily,
            fontSize: item.fontSize,
            fontWeight: item.fontWeight,
            lineHeight: item.lineHeight,
            color: item.color,
            backgroundColor: item.backgroundColor,
            borderRadius: item.borderRadius,
        };
    };
    console.log(JSON.stringify({
        viewport: capture.viewport,
        utility: pick('.veylune-marketplace-utility'),
        searchInput: pick('.veylune-marketplace-search__input'),
        searchSubmit: pick('.veylune-marketplace-search__submit'),
    }));
}
