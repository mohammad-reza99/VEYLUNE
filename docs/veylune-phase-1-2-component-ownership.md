# Veylune Phase 1.2 - Component Ownership and Cascade Reduction Plan

Captured: 2026-09-14
Baseline checkpoint: `veylune-phase-1.1-baseline-20260914`

## Outcome

Veylune now has one declared engineering owner for every imported SCSS partial and every storefront JavaScript source file. This phase deliberately does not merge CSS or change presentation. It freezes responsibility first so later consolidation can be measured against the Phase 1.1 visual and interaction baseline.

The machine-readable contract is `config/veylune-component-ownership.json`. The deterministic gate is `bin/veylune-component-ownership-audit`.

## Evidence baseline

| Metric | Baseline | Meaning |
| --- | ---: | --- |
| SCSS files | 138 | Total storefront SCSS surface |
| `base.scss` imports | 135 | Source-order dependencies that currently participate in the cascade |
| Logical SCSS lines | 31,063 | PHP logical-line metric; newline count is 31,061 |
| `!important` declarations | 126 | Specificity and source-order debt indicator |
| Debt-named files | 39 | Files containing phase, wave, closure, refinement, precision, polish, legacy, or bridge naming |
| Debt-named lines | 5,539 | Lines that require an explicit retirement destination |
| Files touching header selectors | 31 | Highest shared-component collision area |
| Files touching shared controls | 29 | Button, form, dropdown, modal, offcanvas, accordion, or pagination collision area |
| JavaScript source files | 21 | All are assigned exactly once |
| Twig templates | 99 | Canonical shared template families are declared in the manifest |

## Canonical ownership map

| Domain | Canonical SCSS owner | Canonical Twig family | JavaScript owner |
| --- | --- | --- | --- |
| Foundation and controls | tokens, foundation, ui, buttons, forms, utilities | Shopware component contracts | none |
| Global shell | topbar, header, navigation, footer | `layout/header*`, mega panel, mobile drawer, footer | `veylune-header.js` |
| Home merchandising | homepage and named home sections | marketplace category, promo, room, trust, closing blocks | home, product slider, marketplace motion |
| Content and editorial | CMS pages, CMS blocks, editorial, editions | block, element, page/content, editions | ecosystem, legacy, polish during retirement |
| Catalog listing | collection, filters, pagination, product-card | listing and standard product card | PLP v2 and wishlist |
| Product detail | product-detail, gallery, buy-widget, tabs | public PDP description and story | PDP preview behavior until public migration |
| Cart | cart, offcanvas-cart, cart-summary | Shopware cart and offcanvas | preview cart, namespaced only |
| Checkout | checkout family | address, confirm, finish | preview checkout, namespaced only |
| Account | account family | component/account and page/account | preview account, namespaced only |
| Services and forms | inquiry-forms | consultation, contact, partnership, professional pages | inquiry lifecycle |
| Private preview | catalog-preview plus named preview partials | preview home, destination, card | preview card, selection bridge, selection store |
| Living Index | named Living Index surface partials | Living Index home, discovery, object, axes, search, selection | state, search UI, project brief |
| Cross-cutting release | no permanent visual owner | privacy, skip link, cookie UI | none |

The cross-cutting release domain is a temporary retirement queue. Its CSS must be distributed to the owning component rather than survive as a permanent late override layer.

## Highest-risk collisions

### Global shell

Thirty-one files touch header or navigation selectors. The permanent owners are `topbar`, `header`, `navigation`, and `footer`. Marketplace header, silhouette, proportion, type-rhythm, mega precision, sticky, and phase-9 header files are migration inputs, not independent owners.

`veylune-header.js` owns search, mega menu, mobile drawer, Escape behavior, click-away behavior, pointer-leave closure, focus movement, and page-transition exclusions. No second global header listener may be introduced.

### Shared controls

Twenty-nine files touch shared control selectors. Global shape, typography, focus, disabled, loading, and validation behavior belong to `ui`, `buttons`, and `forms`. Page owners may use a component modifier but may not redefine generic `.btn`, `.form-control`, `.form-select`, dropdown, modal, offcanvas, accordion, or pagination contracts.

### PLP

`veylune-plp-v2.js` is the active listing controller. `veylune-plp.js` is dormant debt and cannot become a second active controller. Its removal is gated by behavior comparison, not assumption. Product-card, filters, and pagination receive declarations currently held in marketplace PLP and phase closure layers.

### Public versus preview commerce

Native Shopware cart, checkout, account, and PDP templates own public commerce. Preview modules remain namespaced simulations and may not become public data or transaction owners. Shared visual primitives may converge; state and submission behavior must remain separated until Admin-backed public migration is complete.

## DOM ownership contracts

| DOM root | Sole behavior owner | Prohibited overlap |
| --- | --- | --- |
| `[data-veylune-header]` | `veylune-header.js` | page-specific header listeners |
| `[data-veylune-home]` | home and slider modules by nested root | global selector mutation outside the home root |
| `[data-veylune-plp]` | `veylune-plp-v2.js` | activating legacy `veylune-plp.js` |
| `[data-veylune-pdp-preview]` | `veylune-pdp-preview.js` | public cart authority |
| Preview account, cart, checkout roots | matching preview module | unnamespaced public state writes |
| `[data-vli-*]` roots | Living Index state, search UI, project brief | generic PLP or header ownership |
| Inquiry form under project brief | `veylune-inquiry-lifecycle.js` | simulated success replacing server evidence |

## Cascade reduction sequence

### 1.2A - Freeze and enforcement

- Keep source order unchanged.
- Reject an unowned import or JavaScript file.
- Reject duplicate ownership.
- Add no new phase, wave, closure, refinement, precision, polish, legacy, or bridge file.
- Add no new `!important` declaration without a documented browser or third-party constraint.

### 1.2B - Global shell consolidation

- Capture desktop, tablet, and mobile baselines first.
- Move declarations in source order into topbar, header, navigation, and footer owners.
- Remove an input layer only after computed-style and interaction parity pass.
- Target: reduce the 31 header-touching files to the four canonical owners plus explicitly scoped consumers.

### 1.2C - Shared controls and commerce consolidation

- Fold generic controls into ui, buttons, and forms.
- Move PLP rules into product-card, collection, filters, and pagination.
- Move PDP rules into gallery, information, tabs, and buy-widget.
- Keep public and preview commerce behavior separate while eliminating duplicated visual primitives.

### 1.2D - Content, preview, and Living Index consolidation

- Replace wave, polish, parity, and closure ownership with named surface owners.
- Preserve strict preview namespaces.
- Move Living Index parity corrections into the specific home, discovery, object, axes, search, selection, or brief owner.

### 1.2E - Physical import reduction

- Run only with visual snapshots and interaction audits attached to each batch.
- Remove imports in small reversible batches.
- Target at most 55 base imports.
- Target zero unexplained selector ownership and zero permanent release-overlay partials.
- Reducing the number is not success if specificity, accessibility, or behavior regresses.

## Change protocol

For every consolidation batch:

1. Name the owning domain and exact source partials.
2. Capture desktop, tablet, mobile, keyboard, and relevant state evidence before editing.
3. Move declarations without semantic redesign.
4. Run component ownership, baseline, governance, build, interaction, and diff gates.
5. Compare computed styles and screenshots on affected surfaces.
6. Commit a reversible batch before starting another domain.

## Phase 1.2 exit gate

- All 135 SCSS imports are owned exactly once: PASS.
- All 21 JavaScript files are owned exactly once: PASS.
- Every canonical SCSS, Twig, and JavaScript owner resolves on disk: PASS.
- No storefront Twig, SCSS, or JavaScript presentation file changed in Phase 1.2: PASS.
- Phase 1.1 governance and baseline gates remain green: required before checkpoint.
- A recoverable Phase 1.2 checkpoint is required before Phase 1.3.

## Exact next step

Phase 1.3 - Legacy Artifact Quarantine and Architecture Enforcement.

Phase 1.3 will quarantine `.orig` and temporary audit residue, identify dormant entrypoints, enforce the no-new-debt naming policy, and resolve the remaining route-ownership contract conflict before Phase 2 begins physical design-system consolidation.
