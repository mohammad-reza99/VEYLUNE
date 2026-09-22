# Veylune Master Roadmap v2

Status date: 2026-09-20

This is the canonical 9-phase roadmap for the rest of the Veylune project. Older phase names remain useful as evidence, but no longer control execution order.

## Current evidence baseline

- Public audit: 47 routes and 94 desktop/mobile surfaces. All 94 have approved direct outcomes and successful followed destinations; zero mixed, legacy, broken, overflow, or runtime-failure surfaces remain.
- Public action audit: 168 visible same-origin destinations and 110 visible form occurrences across five unique form actions pass. Checkout is intentionally guarded and the unavailable public wishlist promise is retired.
- Private audit: 67 routes and 134 desktop/mobile surfaces. All 134 returned 200 with no document overflow, JavaScript errors, or failed responses.
- Private modernization: catalog home, legacy prototype retirement, and all 13 category/room/collection destinations are complete.
- Private remaining debt: all 50 PDPs still match legacy-preview markers and render no semantic product image elements; private account is still mixed.
- Media: all 50 draft products exist in Shopware, but they have 0 product-media associations and 0 Admin covers. Nineteen products have one CSS-mapped theme asset; 31 have no dedicated asset. Product media is not yet managed from Shopware Admin.
- Frontend debt: 138 SCSS files, about 31,061 SCSS lines, 135 imports in `base.scss`, 21 JavaScript source files, 99 Twig files, and 40 `.orig` files.
- Catalog governance: 50 draft identities exist, but the older readiness baseline records 0 of 10 launch-cohort products at Level 3 and 0 of 120 required supplier-evidence cells accepted.
- Release state: local release tooling and rollback documentation exist, but the repository has a large uncommitted working set and the old release-candidate evidence predates the latest private-preview work.
- Infrastructure: the domain exists, but no production hosting or VPS has been selected.

## Operating rules

1. Wayfair is the interaction, information-density, discovery, and commerce reference. Veylune keeps its own purple-neutral identity, content, assets, and brand expression.
2. Business content and product media live in Shopware Admin. Code holds only stable brand assets, icons, design tokens, and component behavior.
3. No fake supplier, stock, delivery, review, price authority, or product-rights claim may enter the public storefront.
4. No phase is complete until its exit gate passes on desktop, tablet, mobile, keyboard, and the relevant data state.
5. New polish must update the owning component. Do not create another late cascade layer for every visual correction.
6. Every phase ends with a saved regression report, a reviewed diff, a rollback point, and the exact next phase.
7. The 22-role council is a review gate. It does not replace measurable acceptance criteria.

## Phase 1 - Rebaseline, freeze, and architecture control

Current estimate: 100 percent complete.

Scope:

- Save and classify the current dirty working tree without losing user work.
- Create one canonical route, page, component, action, data, and media inventory.
- Reconcile public storefront, private preview, Living Index, Edition governance, and real Shopware commerce boundaries.
- Decide the expected behavior for every intentional redirect, disabled route, private route, empty-cart route, and account route.
- Quarantine or archive `.orig`, temporary patch, and obsolete prototype material only after a recoverable checkpoint exists.
- Replace stale roadmap claims with this roadmap and a single machine-readable status file.

Exit gate:

- Zero unknown diffs.
- One recoverable baseline commit or tag.
- Every route and action has an owner and expected status.
- Build, lint, and existing regression suites pass against the same commit.

## Phase 2 - Unified design system and global shell

Current estimate: 100 percent complete. Packages 2.1A through 2.1H are complete and the Phase 2 exit gate passes.

Scope:

- Freeze color, typography, spacing, radius, elevation, border, icon, motion, grid, and control tokens.
- Consolidate the 138-file SCSS cascade into component-owned layers and remove contradictory phase overrides.
- Normalize buttons, links, inputs, selects, accordions, tabs, dialogs, dropdowns, cards, loaders, empty states, and error states.
- Finish topbar, header, dominant search, mega menu, mobile drawer, account/cart utilities, cookie UI, footer, and sticky behavior.
- Close dropdown behavior for pointer leave, click-away, Escape, focus transfer, and viewport containment.
- Establish a repeatable Wayfair comparison capture for global shell behavior while preserving Veylune identity.

Exit gate:

- One source of truth per shared component.
- No global component requires a late emergency override.
- All global controls meet responsive, keyboard, focus, target-size, and reduced-motion requirements.
- Header/search/navigation pass the full route matrix without dead links.

## Phase 3 - Public information architecture and content surfaces

Current estimate: 100 percent complete. Packages 3.1A through 3.1H and the Phase 3 exit gate pass.

Scope:

- Finalize homepage, categories, rooms, collections, Discover, Selection, account entry, services, Trade Program, Contact Studio, About, legal pages, controlled 404, and zero-result search.
- Give `/editions`, `/journal`, and `/inspiration` distinct intentional outcomes and remove private-preview language from public pages.
- Decide and implement the correct empty-state behavior for `/checkout/confirm`.
- Either activate a real wishlist route or remove all public wishlist promises until it exists.
- Verify every navbar, mega-menu, card, footer, breadcrumb, CTA, form, and recovery link.
- Replace old CMS markup and old component structure wherever a page still inherits it.

Exit gate:

- All 47 audited public routes have an approved 200, redirect, authentication gate, or controlled error contract.
- Zero mixed or legacy-classified public surfaces.
- Zero unexplained 404 responses.
- Every clickable-looking control performs the documented action.

Completion evidence:

- 47 routes, two viewports, 94 audited surfaces, and 94 screenshots.
- 168 visible same-origin destinations with zero failed followed outcomes.
- 110 visible form occurrences across five actions with zero missing action, method, or control-name contracts.
- Exact checkout, wishlist, collection, and consultation redirect contracts pass.
- Desktop mega navigation and mobile drawer pass open, containment, pointer-leave, click-away, Escape, and focus-restoration checks.
- Zero-result search and the controlled noindex 404 surface are recoverable.

## Phase 4 - Admin catalog and media source of truth

Current estimate: 100 percent complete. Packages 4.1A through 4.1E and the Phase 4 exit gate are complete.

Completed evidence:

- All 50 manifest SKUs are present in the Shopware database.
- A read-only per-product JSON and CSV inventory records Admin associations, covers, localized alt coverage, theme assets, dimensions, hashes, CSS mappings, rights state, missing slots, and the next migration action.
- The baseline proves 0 Admin media associations, 0 Admin covers, 19 theme-only CSS assets, 31 products with no dedicated asset, and 0 launch-media-ready products. All 19 existing files are 1122 by 1402 pixels, below the governed 1600 px minimum long edge.
- The canonical gallery, metadata, rights, quality, semantic rendering, rollback, and migration contracts are machine-readable and regression-audited.
- All 19 existing assets have deterministic target names, unique idempotency keys, checksum-bound review inputs, and a repeatable Shopware Media collision check.
- The 4.1B dry run holds all 19 candidates because rights, quality, localized metadata, and private-intake approval remain incomplete. It produces 0 imports, 0 associations, 0 covers, and preserves every CSS fallback.
- Fifty original, visually reviewed product-cover candidates were generated under one governed Veylune product-cover prompt family. Every source is checksum-bound and records project-generated provenance.
- The idempotent importer validates all 50 sources before mutation, captures a rollback manifest, imports the files into Shopware Admin Media, creates deterministic product-media associations, and assigns all 50 covers while keeping every draft inactive.
- All 50 products have localized en-GB and de-DE alt text plus rights, quality, source, prompt-family, visual-review, batch, and checksum metadata.
- Private catalog cards, product detail galleries, lightboxes, Object Mode, cart, and checkout now consume semantic Shopware Media URLs. Existing saved selections are migrated to real covers, and the 19 record-specific CSS image mappings are retired.
- All 13 category, room, and collection destination heroes are imported as localized Shopware Admin Media and resolved by deterministic destination identity. The active template no longer names theme image files, so editorial media can be replaced without a code deployment.
- Phase 4 recovery, audit commands, manual verification routes, and the launch-gallery boundary are recorded in `docs/veylune-phase-4-admin-media-exit.md`.
- The launch gallery gate remains fail-closed: zero products are launch-approved, so a single draft cover cannot be misrepresented as the required five-image launch gallery.

Scope:

- Make Shopware Admin the canonical editor for products, categories, collections, properties, prices, status, copy, and media.
- Migrate the 19 existing product assets from theme-only mapping into Shopware Media and product media associations.
- Source or create governed media for the remaining 31 products.
- Require a minimum launch set per sellable product: cover, alternate angle, material/detail, and scale/context image; target six useful images where evidence allows.
- Store alt text, dimensions, focal behavior, rights/provenance, and locale metadata.
- Use responsive semantic images and thumbnails; remove product-content dependence on CSS backgrounds.
- Keep only logo, icon, decorative texture, and stable brand assets in code.

Exit gate:

- 50 of 50 draft products have an Admin-managed cover.
- Every launch-approved product has the minimum gallery and valid media rights.
- Category, room, and collection editorial media is editable without a code deployment.
- Zero hard-coded product image selectors remain in active storefront behavior.

## Phase 5 - Catalog discovery unification

Current estimate: 100 percent complete. Packages 5.1A through 5.1H and the Phase 5 exit gate pass.

Completed evidence absorbed here:

- Private A.1 catalog home modernization.
- Private A.2 legacy vision-prototype retirement.
- Private A.3 modernization of 6 categories, 5 rooms, and 2 collections.
- Phase 5.1A rebaselined 25 public and 14 private discovery routes across desktop and mobile: 78 of 78 surfaces return 200 with zero runtime issues, overflow, broken images, accessible-name gaps, or private-header failures.
- Eight representative interaction scenarios pass, 86 screenshots are retained, and the three current implementation families plus eight unification gaps are now machine-readable and regression-audited.
- Shared discovery cards and listings now govern public and private destinations through one semantic product contract.
- Filter, sort, URL state, popstate, load-more, pointer-leave, Escape, click-away, and mobile-panel behavior now converge on one client engine.
- All 13 registered editorial destinations resolve Shopware Admin media first and use explicit theme fallbacks only when Admin media is absent.
- Selection, saved-piece, cart, product, and context actions now expose distinct labels and state semantics.
- The final exit run covers 39 routes at desktop, tablet, and mobile widths: 117 of 117 surfaces return 200 with zero runtime, overflow, broken-image, or accessibility issues.
- Thirty-nine private destination scenarios, six search scenarios, and fourteen unauthorized access-boundary checks pass; 162 exit screenshots are retained.

Execution packages:

- 5.1A - Unified catalog discovery rebaseline: complete.
- 5.1B - Shared discovery card and data contract: complete.
- 5.1C - Public/private destination template convergence: complete.
- 5.1D - Filter, sort, pagination, and mobile-panel convergence: complete.
- 5.1E - Search, zero-result, loading, and transport-error convergence: complete.
- 5.1F - Selection, saved-piece, and cart-state semantics: complete.
- 5.1G - Admin editorial-media adoption on public discovery: complete.
- 5.1H - Phase 5 interaction and visual exit gate: complete.

Closure notes:

- Public product grids remain intentionally empty while no products satisfy the publication approval contract; this is a verified fail-closed state, not missing discovery UI.
- Preview token, noindex, no-store, and environment restrictions remain enforced.
- The phase evidence is stored in `reports/visual-baselines/phase-5-exit/phase-5-exit.json` and its 162 screenshots.

Exit gate:

- Passed: all 13 private destinations pass desktop, tablet, and mobile interaction regression.
- Passed: all public discovery destinations use the approved shared listing and product-card system.
- Passed: filters, sort, pagination/load-more, search, save, and governed navigation states work against available data.
- Passed: no contradictory public/private product-card or listing implementation remains.

## Phase 6 - PDP and customer-commerce journey

Current estimate: 100 percent complete. Packages 6.1A through 6.1H are complete.

This phase absorbs the previously planned Private A.4 and A.5.

Scope:

- Modernize all 50 private PDPs with semantic image galleries, product hierarchy, attributes, variants, price state, availability state, delivery/returns context, trust information, accordions, and related products.
- Replace generic product layouts with product-type-aware information while keeping one maintainable PDP system.
- Modernize private account, cart, and checkout so they match the same system.
- Connect approved products to real Shopware PDP, cart, off-canvas cart, quantity changes, promotion state, checkout, account, order history, and confirmation behavior.
- Use exactly one truthful primary CTA for each commerce state: buy, configure, inquire, or unavailable.
- Keep preview cart simulation visually and semantically distinct from a real order.

Completed evidence:

- Phase 6.1A inventories all 50 private PDPs, three private journey surfaces, eleven public commerce boundaries, and all 50 public product publication boundaries.
- Desktop, tablet, and mobile capture covers 192 surfaces with zero status, runtime, overflow, broken-image, or accessibility failures.
- All 50 private PDPs pass desktop and mobile gallery-keyboard, zoom, variant, quantity, delivery-validation, and preview-cart interaction scenarios: 100 of 100 pass.
- Private cart-to-checkout validation, quantity, remove/undo, promo, delivery, and non-order review behavior passes at all three viewports.
- Fifty draft products remain fail-closed on public PDP routes, and all 53 token-protected private commerce routes return 404 without authorization.
- One shared PDP presentation service now provides product-aware information for 30 product types across eight semantic families.
- All 150 private PDP viewport surfaces expose the semantic information contract and the truthful `Add to preview cart` / `non-binding-preview` state.
- Private cart, checkout, and account use one visible journey contract; saved-project state now consumes the shared v2 selection store.
- Three account-state scenarios and nine public account-validation scenarios pass across desktop, tablet, and mobile.
- The Phase 6 exit has zero unresolved implementation gaps and retains 192 current screenshots in `reports/visual-baselines/phase-6-exit/`.
- Four production activation inputs remain explicitly owned by Phase 7: accepted supplier evidence, multi-view launch media, payment/shipping/tax/legal configuration, and governed customer/order fixtures.

Execution packages:

- 6.1A - PDP and customer-commerce journey rebaseline: complete.
- 6.1B - Shared PDP semantic contract and product-type information: complete.
- 6.1C - Gallery, media, variant, and configuration convergence: complete.
- 6.1D - Truthful commerce posture and primary-action state machine: complete.
- 6.1E - Private cart, checkout, account, and request-history consolidation: complete.
- 6.1F - Native public Shopware cart and checkout boundary validation: complete; production activation remains Phase 7.
- 6.1G - Account, address, order-history, and confirmation state closure: complete for available preview/public entry states.
- 6.1H - Phase 6 interaction, authority, and visual exit gate: complete.

Exit gate:

- Passed: 50 PDPs pass desktop/mobile rendering, gallery, keyboard, token, and action tests.
- Passed: preview state is explicitly non-binding and no fake review, stock, delivery, payment, or checkout success is shown.
- Passed: native cart/account entry and checkout guards remain server-controlled while unapproved products remain fail-closed.
- Deferred to Phase 7 activation evidence: a supplier-approved Shopware product completing sandbox guest/account checkout with server-authoritative tax, shipping, stock, payment, and order state.

## Phase 7 - Commercial, supplier, localization, and operational readiness

Current estimate: 30 percent complete. Technical readiness controls are complete;
commercial activation remains blocked by external evidence.

Implemented technical foundation:

- The fixed launch-candidate cohort now uses ten canonical products from the
  current 50-product Admin catalog.
- Supplier intake, the handoff template, and the runtime exposure registry use
  the same product identities and canonical material vocabulary.
- Twelve operational evidence gates cover supplier, media, pricing,
  availability, shipping, tax, payment, email, returns, legal, localization,
  customer/order fixtures, and founder approval.
- A deterministic Phase 7 readiness audit is part of global governance and
  writes `reports/commercial/phase-7-readiness.json`.
- All candidates remain inactive, stock-zero, and without public visibility
  until every required authority is accepted.

Scope:

- Collect and accept supplier identity, supplier SKU, price authority, availability authority, specification packs, material evidence, media rights, source owner, and reviewer evidence.
- Move launch candidates through Level 1, Level 2, and Level 3 gates independently.
- Configure variants, stock, lead times, shipping rules, tax, currency, payment providers, transactional email, invoice/return behavior, and customer-service ownership.
- Complete English and German product/content localization where required.
- Finalize privacy, consent, terms, cancellation, payment/delivery, returns, imprint, and data-retention behavior for the operating entity.
- Keep all unapproved products inactive and invisible.

Exit gate:

- Minimum launch cohort: 10 real products at Level 3 with complete evidence and founder approval.
- Full catalog completion target: 50 of 50 products independently approved or explicitly retained as private drafts.
- Payment, shipping, tax, email, cancellation, return, and support workflows pass sandbox tests.

## Phase 8 - Quality, security, performance, and release candidate

Current estimate: 45 percent complete.

Scope:

- Merge prior public, private, component, action, interaction, accessibility, route, governance, performance, and release tests into one repeatable CI matrix.
- Test desktop, tablet, mobile, keyboard-only, zoom/reflow, reduced motion, Chrome/Edge, Firefox, Safari, iOS Safari, and Android Chrome.
- Verify accessibility, semantic HTML, focus management, errors, forms, image alternatives, color contrast, and assistive-technology behavior.
- Verify security headers, secrets, access boundaries, dependencies, CSRF, rate limits, preview isolation, sitemap, robots, structured data, canonical URLs, analytics, and consent.
- Pass production Core Web Vitals with real caching, CDN, media, and infrastructure.
- Remove dead code, obsolete imports, `.orig` files, temporary scripts, and stale generated theme residue after a recoverable review.
- Generate the final immutable release candidate and rollback manifest.

Exit gate:

- Zero critical or high-severity defects.
- Zero unexpected console errors, failed requests, overflow, dead links, or uncontrolled 404 responses.
- CI, accessibility, security, performance, SEO, governance, and full commerce E2E gates pass on one immutable commit.

## Phase 9 - EU staging, deployment, launch, and measured growth

Current estimate: 15 percent complete.

Scope:

- Select Germany or EU infrastructure suitable for Shopware, with SSH, workers, cron, database, cache, search, mail, backups, and enough CPU/RAM/storage.
- Create isolated staging and production environments.
- Configure CI/CD, immutable artifacts, environment secrets, database/media backup, restore testing, migrations, queue workers, cache, CDN, SSL, DNS, and mail deliverability.
- Connect the existing domain only after staging sign-off.
- Rehearse deployment and rollback, then run controlled smoke tests before opening traffic.
- Monitor application errors, queue failures, search, orders, payments, email, availability, performance, and conversion after launch.
- Establish a post-launch iteration backlog based on real behavior instead of visual guesswork.

Exit gate:

- Staging passes the same release candidate gate as production.
- Backup restore and rollback are proven.
- DNS and SSL are correct, monitoring is live, and a real sandbox-to-live order path is verified.
- Founder signs off before traffic opens.

## Mapping of older work into this roadmap

| Older work | Canonical destination |
| --- | --- |
| Original visual/header/home phases | Phases 2 and 3 |
| Product-card, PLP, slider, filter, and mobile work | Phase 5 |
| Account/cart/checkout visual closures | Phase 6 |
| Private A.1, A.2, A.3 | Phase 5, completed portions |
| Private A.4 and A.5 | Phase 6, complete |
| WP-07 catalog governance and supplier programs | Phases 4 and 7 |
| Existing audit, performance, RC, and rollback tooling | Phase 8 |
| Hosting/domain/deployment work | Phase 9 |

## Progress interpretation

- Frontend and experience foundation: approximately 78 percent.
- Real catalog, Admin media, and commercial readiness: approximately 20 percent.
- Production infrastructure and launch operations: approximately 15 percent.
- Weighted overall launch readiness: approximately 50 percent complete and 50 percent remaining.

The percentage is an evidence-based planning estimate, not a claim that all work units have equal size. The largest remaining risks are real product media, supplier evidence, source-of-truth migration, real checkout configuration, CSS consolidation, cross-browser QA, and infrastructure.

## Exact next step

Start Phase 7.1A by obtaining accepted supplier evidence for the launch cohort, then complete governed multi-view media and production payment, shipping, tax, legal, customer, and order fixtures before opening public product or checkout activation.
