# Veylune Phase 2.1D - Primary Header, Search and Utilities

Date: 2026-09-15

Status: complete

## Outcome

The public header now has one behavior owner. The duplicate Living Index search module was removed after its suggestion, query-link, click-away, and Escape behavior was consolidated into `veylune-header.js`.

The header preserves Veylune's visual identity while following the useful Wayfair shell pattern: a dominant search field, direct account and cart utilities, and responsive navigation. This phase does not reproduce Wayfair branding or proprietary assets.

## Verified behavior

- Search suggestions open on focus and input at desktop, tablet, and mobile widths.
- Escape closes suggestions without removing focus from the search input.
- A pointer interaction outside the search area closes suggestions.
- The typed query updates the discover URL.
- Account resolves to `/account`; cart resolves to `/checkout/cart`.
- Desktop mega-menu, tablet navigation, and mobile drawer regression checks remain green.

## Evidence

- 5 public routes across 3 viewports: 15 captures.
- HTTP failures: 0.
- JavaScript/page/runtime failures: 0.
- Horizontal overflow captures: 0.
- Interaction issue captures: 0.
- Search width ratios: desktop 0.6851, tablet 0.5455, mobile 1.0000.
- Report: `reports/visual-baselines/phase-2-1d/shell-baseline.json`.
- Contract: `config/veylune-primary-header-contract.json`.
- Audit: `bin/veylune-primary-header-contract-audit`.

## Exact next step

Phase 2.1E - Category Navigation and Mega Menu: consolidate navigation behavior and structure, verify pointer-leave and click-away closure, and validate category destinations against the live storefront.
