# Veylune Phase 6.1A Commerce Rebaseline

Captured: 2026-09-21

## Outcome

Phase 6.1A establishes the governed baseline for the PDP and customer-commerce journey before consolidation. It does not claim that native public commerce is launch-ready. It proves the current private experience, records the protected and fail-closed boundaries, and names the remaining implementation gaps.

## Audited inventory

- 50 private product-detail routes.
- Private cart, checkout, and account routes.
- Eleven native public cart, checkout, authentication, registration, recovery, and authenticated-account boundaries.
- 50 native public product routes for the inactive draft cohort.
- Desktop, tablet, and mobile viewports.

## Evidence

- 192 rendered surfaces and 192 screenshots.
- 150 private PDP surfaces.
- 9 private cart, checkout, and account surfaces.
- 33 public commerce-boundary surfaces.
- Zero status-expectation, runtime, overflow, broken-image, or accessibility failures.
- 100 of 100 private PDP interaction scenarios pass across desktop and mobile.
- 3 of 3 private cart-to-checkout journey scenarios pass.
- 50 of 50 inactive public product routes remain unavailable.
- 53 of 53 private commerce routes return 404 without the preview token.
- All 50 draft products retain governed Shopware Admin cover media.
- Zero products are represented as launch-approved.

The PDP interaction harness exercises gallery keyboard movement, zoom open and Escape close, material variant state and URL synchronization, quantity changes, invalid and valid postal-code handling, add-to-preview-cart behavior, dialog state, and persisted private selection state.

The private journey harness exercises cart hydration, quantity authority inside the preview state, invalid and valid delivery input, non-authoritative promo messaging, remove and undo, checkout field validation, review-dialog creation, and the explicit guard that no order is submitted.

## Registered gaps

1. No launch-approved product exists, so a real public PDP and add-to-cart path cannot yet be exercised.
2. Every product has a governed cover, but launch-grade multi-view gallery approval remains pending.
3. Server-authoritative guest and account checkout cannot complete without a sellable approved product.
4. Authenticated dashboard, address, order-history, and profile coverage needs governed test-customer fixtures.
5. Private simulated commerce and native Shopware commerce remain separate implementation families.
6. The private PDP still uses one generic information hierarchy instead of product-type-aware specifications.
7. Preview price and delivery totals remain explicitly non-binding while native tax, shipping, and order authority are activation-pending.

## Artifacts

- Contract: `config/veylune-phase-6-commerce-baseline-contract.json`
- Browser runner: `tools/audit/veylune-phase-6-commerce-baseline.cjs`
- Machine audit: `bin/veylune-phase-6-commerce-baseline-audit`
- Report: `reports/visual-baselines/phase-6-1a-commerce/commerce-baseline.json`
- Screenshots: `reports/visual-baselines/phase-6-1a-commerce/*.png`

## Next package

Phase 6.1B defines one governed PDP semantic contract and introduces product-type-aware information without collapsing private preview simulation into native Shopware commerce.
