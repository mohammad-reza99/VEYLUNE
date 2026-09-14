# Veylune Master Roadmap v2

Status date: 2026-09-13

This is the canonical 9-phase roadmap for the rest of the Veylune project. Older phase names remain useful as evidence, but no longer control execution order.

## Current evidence baseline

- Public audit: 47 routes and 94 desktop/mobile surfaces. 90 returned 200. Desktop classification was 42 current, 3 mixed, 0 legacy, and 2 broken.
- Public exceptions: `/editions`, `/journal`, and `/inspiration` are mixed; `/checkout/confirm` and `/wishlist` return 404 and need an explicit product decision.
- Private audit: 67 routes and 134 desktop/mobile surfaces. All 134 returned 200 with no document overflow, JavaScript errors, or failed responses.
- Private modernization: catalog home, legacy prototype retirement, and all 13 category/room/collection destinations are complete.
- Private remaining debt: all 50 PDPs still match legacy-preview markers and render no semantic product image elements; private account is still mixed.
- Media: 19 of 50 products have dedicated product assets in theme code. 31 products have no dedicated product asset. Product media is not yet managed from Shopware Admin.
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

Current estimate: 60 percent complete. Package 2.1A is complete; packages 2.1B through 2.1H remain.

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

Current estimate: 70 percent complete.

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

## Phase 4 - Admin catalog and media source of truth

Current estimate: 30 percent complete.

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

Current estimate: 70 percent complete.

Completed evidence absorbed here:

- Private A.1 catalog home modernization.
- Private A.2 legacy vision-prototype retirement.
- Private A.3 modernization of 6 categories, 5 rooms, and 2 collections.

Remaining scope:

- Unify public and private card, filter, sort, pagination, search, breadcrumb, and merchandising behavior around shared components.
- Feed cards from Admin media and governed product data.
- Preserve preview token, noindex, no-store, and environment restrictions.
- Make filters truthful to available data and keep all mobile panels inside the viewport.
- Separate wishlist, inspiration Selection, and cart concepts so the user always knows what an action does.
- Add deliberate empty, loading, error, and no-result states.

Exit gate:

- All 13 private destinations pass desktop/mobile interaction regression.
- All public discovery destinations use the approved component system.
- Filters, sort, pagination/load-more, search, save, and product navigation work with real data.
- No duplicate or contradictory public/private UI implementation remains without a documented reason.

## Phase 6 - PDP and customer-commerce journey

Current estimate: 30 percent complete.

This phase absorbs the previously planned Private A.4 and A.5.

Scope:

- Modernize all 50 private PDPs with semantic image galleries, product hierarchy, attributes, variants, price state, availability state, delivery/returns context, trust information, accordions, and related products.
- Replace generic product layouts with product-type-aware information while keeping one maintainable PDP system.
- Modernize private account, cart, and checkout so they match the same system.
- Connect approved products to real Shopware PDP, cart, off-canvas cart, quantity changes, promotion state, checkout, account, order history, and confirmation behavior.
- Use exactly one truthful primary CTA for each commerce state: buy, configure, inquire, or unavailable.
- Keep preview cart simulation visually and semantically distinct from a real order.

Exit gate:

- 50 PDPs pass desktop/mobile rendering, gallery, keyboard, token, and action tests.
- A real approved Shopware product can complete a sandbox guest and account checkout.
- Cart totals, tax, shipping, stock, quantity, and order state are server-authoritative.
- No fake review, stock, delivery, or checkout success is shown.

## Phase 7 - Commercial, supplier, localization, and operational readiness

Current estimate: 10 percent complete.

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
| Private A.4 and A.5 | Phase 6, pending |
| WP-07 catalog governance and supplier programs | Phases 4 and 7 |
| Existing audit, performance, RC, and rollback tooling | Phase 8 |
| Hosting/domain/deployment work | Phase 9 |

## Progress interpretation

- Frontend and experience foundation: approximately 65 percent.
- Real catalog, Admin media, and commercial readiness: approximately 20 percent.
- Production infrastructure and launch operations: approximately 15 percent.
- Weighted overall launch readiness: approximately 42 percent complete and 58 percent remaining.

The percentage is an evidence-based planning estimate, not a claim that all work units have equal size. The largest remaining risks are real product media, supplier evidence, source-of-truth migration, real checkout configuration, CSS consolidation, cross-browser QA, and infrastructure.

## Exact next step

Start Phase 2.1B Token Freeze. Phase 2.1A captured and gated the current Wayfair reference and Veylune global-shell baseline across five public routes and three viewports. Next, declare and govern color, typography, spacing, radius, elevation, border, icon, motion, grid, and control tokens before changing the shell.
