# Veylune Phase 5.1A - Unified Catalog Discovery Rebaseline

Captured on 2026-09-21 against the local DDEV storefront.

## Outcome

Phase 5.1A is complete. It establishes a repeatable discovery baseline before public and private catalog implementations are unified. It does not claim that Phase 5 is complete.

The governed matrix covers:

- 25 public discovery routes: 10 category destinations, 6 room destinations, 6 collections, Discover home, a result query, and a zero-result query.
- 14 token-protected private routes: catalog home, 6 categories, 5 rooms, and 2 collections.
- Desktop at 1440 by 1000 and mobile at 390 by 844.
- 39 routes, 78 route/viewport surfaces, 78 status-200 responses, and 86 saved screenshots.
- 8 representative interaction scenarios covering private filtering, sorting, load-more, save, cart, and product navigation; public fail-closed listing behavior; search facets, sorting, and Selection; and zero-result recovery.

All 78 surfaces pass the baseline checks:

| Check | Result |
| --- | ---: |
| HTTP 200 | 78 of 78 |
| Runtime issue surfaces | 0 |
| Horizontal overflow surfaces | 0 |
| Broken-image surfaces | 0 |
| Missing accessible-name surfaces | 0 |
| Private cache/noindex header failures | 0 |
| Failed interaction scenarios | 0 of 8 |

## Current implementation truth

Discovery currently has three maintained implementation families, not one:

1. Private catalog destinations use `catalog-preview-destination.html.twig`, `catalog-preview-card.html.twig`, `veylune-plp-v2.js`, and `DraftCatalogPreviewService`.
2. Public category, room, and collection destinations use `living-index-discovery.html.twig`, Shopware `box-standard.html.twig`, server-side GET state, and `ProductExposureService`.
3. Public Discover uses `living-index-search.html.twig`, `living-index-search-results.html.twig`, server-side axis/sort state, and `LivingIndexUtilityController`.

The public destination product grids are intentionally empty at this baseline. The 50 draft products remain inactive and fail closed until supplier and publication gates approve them. The empty state is therefore a truthful release boundary, not a 404 or rendering defect. Public Discover still returns governed context cards and has a separate zero-result recovery state.

Private routes retain token access plus `private`, `no-store`, and `noindex` response contracts. The saved-piece state, public inspiration Selection, preview cart simulation, and future real cart remain distinct concepts.

## Registered unification gaps

The baseline registers eight gaps that must be resolved by the remaining Phase 5 packages:

1. Private and public destination cards use different templates.
2. Private listings filter and sort in the browser while public listings use server-side GET state.
3. Private load-more has no public equivalent.
4. Public products correctly fail closed until supplier and publication approval exists.
5. Search results are context cards rather than product cards.
6. Private saved-piece, public Selection, and cart states need explicit labels.
7. Public discovery scene media still uses theme assets rather than the completed Admin editorial-media registry.
8. Loading and transport-error states are not shared.

These are now explicit contract items rather than hidden implementation drift.

## Evidence and repeatability

- Contract: `config/veylune-phase-5-discovery-contract.json`
- Browser runner: `tools/audit/veylune-phase-5-discovery-baseline.cjs`
- Generated report: `reports/visual-baselines/phase-5-1a-discovery/discovery-baseline.json`
- Screenshot directory: `reports/visual-baselines/phase-5-1a-discovery`
- Regression gate: `php bin/veylune-phase-5-discovery-baseline-audit`
- Full repository gate: `ddev exec env VEYLUNE_INSIDE_DDEV=1 bash bin/veylune-governance-check`

The audit verifies the full matrix, unique routes and captures, semantic page structure, scope roots, runtime errors, overflow, images, accessible names, private response headers, all interactions, screenshot count, implementation-family count, gap count, and the source markers behind the three current families.

## Exact next step

Phase 5.1B is `Shared discovery card and data contract`.

It will define one governed product-card view model and one semantic card contract for public and private product discovery. It must consume Shopware Admin media, preserve the fail-closed publication boundary, and keep product, context, Selection, saved-piece, and cart states truthful before templates are consolidated.
