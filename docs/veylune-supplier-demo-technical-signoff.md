# Veylune supplier-demo technical sign-off

Decision date: 2026-09-28

Decision: APPROVED for local supplier demonstration and supplier conversations.

## Approved scope

- Isolated native Shopware catalog, variants, product detail, cart, checkout,
  account and order-history paths.
- Responsive acceptance at 390, 768 and 1440 CSS pixels.
- Success, decline/recovery, wrong-password, unchecked-terms, stock-boundary,
  repeat-order, cancellation, local-refund and concurrency scenarios.
- Private/no-store and noindex isolation, security headers and dependency audit.
- Theme build, Twig/container validation, performance budgets, production
  readiness baseline and full repository governance.
- Visual integration follows Veylune tokens while using the current reference
  commerce patterns for discovery, variants, price, availability and cart.

## Final evidence

- `reports/local-commerce/final-signoff/acceptance-matrix.json`
- `reports/local-commerce/final-signoff/surface-matrix.json`
- `reports/local-commerce/final-signoff/cart.json`
- `reports/local-commerce/final-signoff/checkout.json`
- `reports/local-commerce/final-signoff/canonical-checkout.json`
- `reports/local-commerce/final-signoff/account.json`
- `reports/local-commerce/final-signoff/account-lifecycle.json`
- `reports/local-commerce/final-signoff/variants.json`
- `reports/local-commerce/final-signoff/stock-race.json`
- `reports/local-commerce/final-signoff/refund.json`

## Final regression closure

The 2026-09-28 acceptance rerun found and fixed one real variant-cart defect:
the custom line-item link mapper treated `VLT-TEST-V-SAND` as a public catalog
record and caused offcanvas rendering to return HTTP 500. The mapper now creates
catalog links only for canonical three-character record IDs such as `F04`;
variant line items keep their native Shopware product links.

After the fix, variant add-to-cart, quantity capping, out-of-stock behavior,
single-unit concurrency, canonical checkout, account lifecycle, local refund,
the 21-surface responsive matrix and all 41 public routes passed again.

## Explicit exclusions

This decision is not commercial activation or production approval. Real
supplier contracts and product authority, media rights, supplier-approved
price/stock/availability, external payment-provider certification, EU hosting,
domain cutover and production launch are excluded by the owner and remain
fail-closed. No excluded item is represented as complete.

## Closure

There is no remaining engineering phase inside the selected local-test scope.
The owner may now use the demo to begin supplier conversations. Commercial or
public activation requires a later, separately evidenced decision.
