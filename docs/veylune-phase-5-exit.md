# Veylune Phase 5 Exit - Catalog Discovery Unification

Phase 5 is complete as of 2026-09-21.

## Implemented system

- Public and private product discovery use one semantic product-card partial: `discovery-product-card.html.twig`.
- The Shopware public card and private preview card are thin adapters into the same flat contract.
- Public and private destinations use one listing partial and one browser behavior module for filters, sorting, load-more, active filters, URL state, back/forward state, accessible announcements, and dropdown behavior.
- Filter and sort panels close by click-away, focus transfer, Escape, and pointer leave. Mobile and tablet panels remain inside the viewport.
- Search cards explicitly identify themselves as context cards. They save inspiration paths to Selection and do not imitate product cart actions.
- Private saved pieces, public Selection, private preview cart, and the real Shopware cart have distinct labels and storage boundaries.
- Public discovery resolves all 13 registered category, room, and collection editorial images from Shopware Admin Media. Routes without a registered Admin editorial record use an explicit theme fallback.
- Public product cards accept Shopware Admin product media and public Selection accepts both `/media/` and stable theme asset URLs.
- Public products remain fail-closed. The exit report contains zero public product surfaces because no product has passed the supplier and publication gates; no product was activated to manufacture a visual pass.

## Package closure

| Package | Result |
| --- | --- |
| 5.1A Unified catalog discovery rebaseline | Complete |
| 5.1B Shared discovery card and data contract | Complete |
| 5.1C Public/private destination template convergence | Complete |
| 5.1D Filter, sort, pagination, and mobile-panel convergence | Complete |
| 5.1E Search, zero-result, loading, and error-state convergence | Complete |
| 5.1F Selection, saved-piece, and cart-state semantics | Complete |
| 5.1G Admin editorial-media adoption on public discovery | Complete |
| 5.1H Phase 5 interaction and visual exit gate | Complete |

Search navigation is server-rendered, so its loading and transport failure behavior is the standard document navigation contract. The shared product listing is local over an already-rendered governed product set, so `aria-busy` and an updating state cover work without inventing an asynchronous transport layer.

## Exit evidence

The final browser matrix covers 39 routes at desktop, tablet, and mobile sizes:

- 117 of 117 route surfaces returned HTTP 200.
- 0 runtime issue surfaces.
- 0 horizontal overflow surfaces.
- 0 broken-image surfaces after deterministic lazy-image completion.
- 0 accessibility issue surfaces in the governed structural and accessible-name checks.
- 39 of 39 private destination scenarios passed. Every one of the 13 destinations was exercised at all three viewports.
- Sort order, filter results, clear behavior, load-more growth, pointer-leave dismissal, Escape dismissal, and mobile/tablet panel containment passed.
- 6 of 6 search and zero-result scenarios passed, including real title sorting, facets, Selection save state, and recovery links.
- 14 of 14 private routes returned 404 without the preview token.
- All authorized private captures retained `private`, `no-store`, and `noindex` boundaries.
- All 22 public category, room, and collection routes rendered the shared listing contract.
- All 13 registered editorial destinations rendered Shopware Admin Media.
- 162 screenshots were saved.

## Reproduction

- Contract: `config/veylune-phase-5-exit-contract.json`
- Browser runner: `tools/audit/veylune-phase-5-exit.cjs`
- Browser report: `reports/visual-baselines/phase-5-exit/phase-5-exit.json`
- Static and report gate: `php bin/veylune-phase-5-exit-audit`
- Full repository gate: `ddev exec env VEYLUNE_INSIDE_DDEV=1 bash bin/veylune-governance-check`

## Exact next step

Phase 6.1A is the PDP and customer-commerce journey rebaseline. It will re-audit all 50 private PDPs plus the public product, cart, checkout, and account boundaries before those surfaces are consolidated.
