# Veylune Phase 2.1F - Mobile Drawer and Sticky Shell

Date: 2026-09-15

Status: complete

## Outcome

Veylune now has one canonical mobile discovery drawer instead of parallel active and inactive templates. The drawer provides live search, grouped discovery, real destinations, single-open accordion behavior, keyboard containment, Escape and backdrop closing, and focus restoration.

The live Wayfair reference was reviewed on the completion date. Its useful mobile commerce pattern is retained: search and account/cart actions appear first, followed by compact grouped discovery. Veylune keeps its own taxonomy, visual identity, content, and routes.

## Implemented behavior

- The header includes one canonical `mobile-navigation.html.twig`; the orphan `marketplace-mobile-drawer.html.twig` was retired.
- Mobile search submits `q` to the public Living Index search route and is no longer disabled or marked pending.
- Featured, Departments, Shop by Room, and Services & Inspiration use four ARIA-linked accordions.
- Opening one group closes its siblings and synchronizes every summary's `aria-expanded` value.
- Closed accordion panels are removed from layout and keyboard order.
- Tab focus wraps inside the open drawer; Escape and backdrop close it and restore focus to the menu trigger.
- The tablet and mobile shell stays sticky at top zero and remains inside the viewport.
- Desktop mega-menu and global search behavior remained green.

## Evidence

- 5 public routes across 3 viewports: 15 standard captures.
- 6 interaction captures: 21 screenshots total.
- Public navigation link occurrences per home surface: 80.
- Unique public navigation routes tested per breakpoint: 26.
- Failed public navigation routes: 0.
- Mobile accordions: 4; only one can remain open.
- HTTP, runtime, overflow, route, and interaction issue captures: 0.
- Report: `reports/visual-baselines/phase-2-1f/shell-baseline.json`.
- Contract: `config/veylune-mobile-shell-contract.json`.
- Audit: `bin/veylune-mobile-shell-contract-audit`.

## Exact next step

Phase 2.1G - Global-shell Cascade Migration: move approved header, navigation, mobile drawer, and sticky-shell declarations into their canonical owners, remove redundant late overrides, and prove pixel and behavior parity after the source-order migration.
