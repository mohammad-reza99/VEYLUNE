# Veylune Phase 2.1G - Global-shell Cascade Migration

Date: 2026-09-19

Status: complete

## Outcome

The global shell now has four canonical SCSS owners: topbar, navigation, header, and footer. Sixteen historical marketplace and phase override partials were migrated into those owners and retired. The old visible bridge now owns homepage compatibility only; it no longer contains header, navigation, mobile drawer, mega-menu, sticky-shell, or footer selectors.

The live Wayfair reference was reviewed on the completion date. The retained structural target remains a compact service rail, dominant search, account/cart utilities, two-level category discovery, grouped overlays, and responsive menu containment. Veylune preserves its own identity and taxonomy.

## Migration result

- Active SCSS files reduced from 137 to 121.
- `base.scss` imports reduced from 135 to 119.
- Global shell owner imports: 4.
- Retired shell override partials: 16.
- Inline logo corrections moved from `base.scss` into the header owner.
- Marketplace bridge header and navigation rules moved to their canonical owners.
- Marketplace bridge footer rules moved to the canonical footer owner.
- The canonical navigation-before-header order preserves the approved cascade without emergency late imports.

## Evidence

- Storefront production build: pass.
- 5 public routes across 3 viewports: 15 standard captures.
- 6 interaction captures: 21 screenshots total.
- Phase 2.1F to Phase 2.1G exact screenshot parity: 21 of 21 SHA-256 matches.
- Public navigation link occurrences per home surface: 80.
- Unique public navigation routes tested per breakpoint: 26.
- Failed public navigation routes: 0.
- HTTP, runtime, overflow, route, and interaction issue captures: 0.
- Report: `reports/visual-baselines/phase-2-1g/shell-baseline.json`.
- Contract: `config/veylune-shell-cascade-contract.json`.
- Audit: `bin/veylune-shell-cascade-audit`.

## Exact next step

Phase 2.1H - Global Shell Exit Gate and Control Normalization: normalize remaining shared global controls, verify focus, target size, reduced motion, cookie UI, footer, and all route states, then close Phase 2 only after its complete exit matrix passes.
