# Phase 3: High-density Homepage Merchandising

## Status

Implementation complete for the governed storefront shell.

Product-populated acceptance remains dependent on products independently passing
the existing public exposure and Level 3 release gates. Phase 3 does not authorize
synthetic products, inactive drafts, or private-preview records on public routes.

## Delivered

- One bounded commercial hero with a governed primary action.
- Compact department discovery with six category states.
- A merchandising banner with collection and consultation paths.
- A five-destination room carousel.
- New Arrivals and Founder Selection product rails.
- Reusable slider controls with keyboard, reduced-motion and ARIA state support.
- Per-surface availability gating for category, room and collection links.
- Non-clickable preview states for unavailable destinations.
- Trust and service architecture below discovery.
- A restrained consultation module and closing capture.

## Public-state behavior

The current database exposes no products that satisfy every public eligibility
condition. The homepage therefore:

1. keeps product rails suppressed;
2. renders unavailable discovery tiles as non-links;
3. provides Journal, Consultation and legal continuity;
4. exposes no public link to a fail-closed destination;
5. keeps private preview routes isolated and noindex.

This is the required credible empty-assortment state. Product density must increase
only through governed product approval, not template fallback data.

## Acceptance contract

Phase 3 remains accepted only while automated governance proves:

- homepage HTTP 200 and exactly one main landmark;
- hero, category, promo, room, trust and consultation modules render;
- product rails are wired to ProductExposureService;
- blocked destinations are not linked;
- slider controls retain previous, next and keyboard contracts;
- mobile and reduced-motion containment remains active;
- customer-facing output contains no internal governance or demo residue.

## Next phase

Phase 4 defines and closes the public listing/discovery experience: category
hierarchy, filter and sort behavior, product-card comparison density, responsive
controls, and governed empty-state continuity.
