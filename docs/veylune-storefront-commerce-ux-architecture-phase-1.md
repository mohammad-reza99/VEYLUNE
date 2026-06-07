# VEYLUNE STUDIO Storefront Commerce Redesign

## Phase 1: Commerce UX Architecture

Architecture and audit only. This report authorizes no implementation, route
activation, template change, styling change, JavaScript change, database write,
import, or Shopware configuration change.

## Executive Decision

Veylune should become a **curated multi-supplier commerce platform**, not an
editorial site with commerce attached and not a marketplace grid with premium
styling.

The target experience combines:

- **40% Westwing:** assortment breadth, category depth, room-led discovery,
  filtering, complementary merchandising, editorial commerce, and design
  service.
- **40% Apple:** clarity, progressive decision-making, controlled visual
  hierarchy, comparison, explicit fulfillment, focused conversion, support,
  and post-purchase confidence.
- **20% Veylune:** material authority, Founder curation, quiet spatial identity,
  evidence-led attribution, and consultation when product complexity requires
  it.

The principal architecture rule is:

```text
Inspire broadly
→ narrow confidently
→ explain the product precisely
→ make fulfillment predictable
→ convert without pressure
→ support the customer after purchase
```

## Current-State Audit

### Existing Strengths

- Canonical public storefront ownership is defined.
- Stable departments and controlled product attributes exist.
- Room, category, collection, and material discovery concepts exist.
- Product readiness, publication, sellability, and exposure are governed
  independently.
- Product story fields already support materials, care, lead time,
  craftsmanship, consultation, and collectible identity.
- Shopware provides native catalog, listing, cart, checkout, account, order,
  payment, and shipping foundations.
- Consultation is already treated as a support layer.

### Current Structural Constraints

Under the current WP-06 policy:

- Homepage and Consultation are public.
- Editions are governed public.
- Products, Categories, Collections, Search, Cart, Checkout, Account, Wishlist,
  and Trade remain `activation_pending`.

Future implementation must therefore treat each commerce surface as a governed
activation program. A redesign cannot itself authorize a route.

### Architecture Gaps

- No unified commerce information architecture for 1000+ products.
- Category, room, collection, material, style, and editorial concepts do not
  yet have a clear customer-facing priority.
- No defined mega-menu or scalable mobile navigation model.
- No canonical category page, collection page, search, PDP, cart, checkout, or
  post-purchase hierarchy.
- No shared rule for editorial content inside transactional journeys.
- No formal cross-sell, upsell, basket, or trust architecture.
- No multi-supplier fulfillment presentation model.

# A. Customer Journey Architecture

## End-to-End Map

```text
Homepage
├── Shop by Category
├── Shop by Room
├── Shop by Collection
├── Shop by Material
├── New Arrivals / Best Sellers
└── Search
        ↓
Discovery Destination
├── Room
├── Style
├── Collection
└── Material
        ↓
Category / Search Results
├── Refine
├── Sort
├── Compare
├── Save
└── Open Product
        ↓
Product Detail
├── Understand
├── Configure
├── Verify fit
├── Verify delivery
├── Consult if required
└── Add to cart
        ↓
Cart
├── Validate items and variants
├── Understand supplier shipments
├── Add governed complements
└── Proceed
        ↓
Checkout
├── Identity / guest
├── Delivery and access
├── Payment
├── Review
└── Place order
        ↓
Post Purchase
├── Confirmation
├── Supplier-aware order status
├── Delivery preparation
├── Care and assembly
├── Support / returns
└── Re-engagement
```

## Major Decision Points

| Stage | Customer question | Required answer |
| --- | --- | --- |
| Homepage | Where should I begin? | Clear category, room, collection, material, and search entry points |
| Discovery | What belongs in my context? | Governed thematic selection with transparent path into products |
| Category | Which products meet my needs? | Relevant facets, trustworthy sorting, comparable cards |
| Product | Is this right for my room, budget, timing, and care needs? | Complete facts, media, dimensions, material evidence, delivery, consultation |
| Cart | Is this selection coherent and deliverable? | Correct variants, shipment grouping, totals, lead times, restrained complements |
| Checkout | Can I complete this safely and predictably? | Minimal steps, guest option, payment trust, delivery clarity |
| Post purchase | What happens now? | Order stages, supplier-aware shipment status, delivery preparation, support |

## Journey Principles

1. Category navigation answers **what**.
2. Room discovery answers **where**.
3. Material discovery answers **what it is made of**.
4. Collection discovery answers **why these products belong together**.
5. Style discovery answers **which visual language** and remains subordinate to
   factual classification.
6. Search provides the fastest direct path and understands all five dimensions.
7. Product pages resolve uncertainty; they do not merely repeat editorial copy.
8. Consultation appears at points of objective complexity, not as a blanket
   conversion detour.

# B. Homepage Experience Architecture

## Homepage Purpose

The homepage has four jobs:

1. establish the current Veylune point of view;
2. provide immediate shopping entry;
3. expose commercially useful assortment;
4. build enough trust to continue.

It must not attempt to display the entire taxonomy.

## Homepage Wireframe Hierarchy

```text
1. Utility / service strip
   Delivery proposition | Returns | Consultation | Locale

2. Global header
   Logo | Shop | Rooms | Collections | Journal | Search | Account | Saved | Bag

3. Primary hero
   One current commercial story
   Primary CTA: Shop the selection
   Secondary CTA: Explore the room / material

4. Shop by Category
   Furniture | Lighting | Decor & Objects
   Textiles & Rugs | Dining & Kitchen | Outdoor

5. New Arrivals
   Governed date window
   6-10 products

6. Shop by Room
   Living Room | Dining Room | Bedroom
   Workspace | Hallway | Outdoor

7. Founder Selection
   Small, highly edited set
   Founder decision remains distinct from New Arrivals

8. Best Sellers / Most Considered
   Data-governed products only
   Suppress until minimum order and coverage thresholds exist

9. Material Focus
   3-5 current material paths
   Each path requires adequate approved product depth

10. Commercial collection feature
    Stable collection with direct product access

11. Editorial commerce story
    Room, material, atelier, or care context
    Products remain visibly shoppable

12. Consultation
    Quiet support module for sizing, configuration, material, delivery, or project fit

13. Trust architecture
    Secure payment | Delivery clarity | Returns | Material evidence | Customer care

14. Footer
    Shopping | Service | Company | Professional | Legal | Locale
```

## Hierarchy Rules

- One hero, not a rotating carousel.
- Category access appears before editorial depth.
- New Arrivals precede Founder Selection because recency is a broad commercial
  path; Founder Selection is intentionally narrow.
- Best Sellers must be evidence-based. Before sufficient order volume exists,
  use `Most Considered` only if based on valid engagement data, otherwise omit.
- Material modules appear only when a material path has enough Level 3 products
  to avoid a thin destination.
- Consultation is visible but does not interrupt standard shopping.
- Promotions may appear in the service strip or a bounded commercial module;
  the homepage must not become campaign-dependent.

# C. Discovery Architecture

## Priority

```text
1. Shop by Category
2. Shop by Room
3. Shop by Collection
4. Shop by Material
5. Shop by Style
```

Category remains the stable commerce backbone. The four requested discovery
modes operate as overlays and entry paths, not parallel taxonomies.

## Shop by Room

Primary destinations:

- Living Room
- Dining Room
- Bedroom
- Workspace
- Hallway
- Outdoor

Room page hierarchy:

1. room title and short functional definition;
2. key product categories for that room;
3. curated room anchors;
4. all eligible room products;
5. optional room collection or editorial guidance;
6. consultation for scale, configuration, or project uncertainty.

Room assignment remains evidence-based. Photography location or SEO demand
cannot establish room relevance.

## Shop by Style

Controlled initial styles:

- Contemporary
- Minimal
- Modern Classic
- Organic
- Sculptural

Style is a discovery aid, not a product fact stronger than category, room, or
material. Public style destinations require:

- explicit controlled attribution;
- adequate product count;
- visually coherent but commercially varied results;
- no supplier-created styles;
- no trend-term proliferation.

Style should initially exist inside search, filters, and curated landing pages,
not as a top-level navigation pillar.

## Shop by Collection

Priority:

1. Founder Selection
2. New Arrivals
3. Permanent Collections
4. Editorial Collections

Collections explain selection logic. They never replace product categories.

## Shop by Material

Initial paths:

- Travertine
- Marble
- Stone
- Wood
- Metal
- Glass
- Ceramic
- Upholstery Fabric
- Wool
- Leather

Material destination hierarchy:

1. factual material definition;
2. care and variation summary;
3. applicable categories;
4. eligible products;
5. related specific or family materials;
6. consultation only where variation, specification, or care requires it.

Material pages should not be long decorative essays above the product grid.

# D. Navigation Architecture

## Desktop Global Navigation

```text
Shop
Rooms
Collections
Journal
Consultation
```

Actions:

```text
Search
Account
Saved
Bag
```

`Materials` is discoverable inside Shop and contextual modules. It should not
compete with the five primary navigation items until product depth justifies
top-level status.

## Shop Mega Menu

```text
SHOP
├── Furniture
│   ├── Sofas
│   ├── Lounge Chairs
│   ├── Dining Chairs
│   ├── Benches & Stools
│   ├── Dining Tables
│   ├── Coffee Tables
│   ├── Side Tables
│   ├── Consoles
│   ├── Desks
│   ├── Beds
│   └── Storage
├── Lighting
│   ├── Floor Lamps
│   ├── Table Lamps
│   ├── Pendant Lights
│   └── Wall Lighting
├── Decor & Objects
│   ├── Vessels
│   ├── Sculptural Objects
│   ├── Mirrors
│   ├── Trays
│   └── Decorative Objects
├── Textiles & Rugs
│   ├── Rugs
│   ├── Throws
│   └── Cushions
├── Dining & Kitchen
│   ├── Dining Furniture
│   ├── Tableware
│   ├── Serveware
│   └── Kitchen Objects
├── Outdoor
│   ├── Outdoor Seating
│   ├── Outdoor Tables
│   └── Planters & Objects
└── Featured
    ├── New Arrivals
    ├── Founder Selection
    ├── Best Sellers
    └── View All
```

Only published categories with adequate assortment appear. Empty or sparse
branches remain hidden.

## Rooms Mega Menu

```text
Living Room
Dining Room
Bedroom
Workspace
Hallway
Outdoor
```

Each may include two to four category shortcuts, but no arbitrary product links.

## Collections Mega Menu

```text
Founder Selection
New Arrivals
Permanent Collections
Editorial Collections
```

Current collection children appear only when published and sufficiently deep.

## Journal Hierarchy

```text
Rooms
Materials
Care
Ateliers
Guides
```

Journal content is editorial. It links to governed products and collections but
does not create hidden category structures.

## Supplier Visibility

Veylune is a multi-supplier storefront, not a supplier directory.

- Supplier/manufacturer may be shown on PDPs when meaningful.
- Brand filtering may be introduced at sufficient assortment depth.
- Supplier names do not become primary navigation.
- Supplier landing pages require a future explicit business and SEO decision.

# E. Category Experience Architecture

## Category Page Hierarchy

```text
1. Breadcrumb
2. Category title
3. One-sentence purpose / product count
4. Subcategory shortcuts
5. Optional compact commercial or editorial feature
6. Filter + sort controls
7. Product grid
8. Contextual merchandising insertion
9. Pagination / load control
10. Supporting SEO copy below results
11. Related rooms, materials, and collections
```

Editorial content must not push the first product row below the primary desktop
viewport without a strong commercial reason.

## Filter Strategy

### Global Candidates

- Category / subcategory
- Price
- Availability
- Material
- Color
- Style
- Room
- Collection
- Brand/manufacturer when coverage supports it
- New Arrivals

### Category-Specific Candidates

- Furniture: dimensions, seating capacity, configuration, assembly, delivery
  class.
- Lighting: type, installation, dimmability, bulb/light source, dimensions.
- Rugs/textiles: size, composition, pile/construction.
- Outdoor: weather suitability, cover/storage requirement, material.

### Facet Rules

- Facets come only from published controlled dictionaries.
- Show a facet only when relevant to the current category.
- Hide values with no results.
- Preserve selected filters through sort and pagination.
- Display applied filters as removable chips.
- Offer `Clear all`.
- Mobile filters use a full-height sheet with result count and persistent apply
  action.
- Do not expose dozens of sparse supplier values.

## Sort Strategy

Default: **Recommended**.

Available:

- Recommended
- Best Selling
- Newest
- Price: Low to High
- Price: High to Low
- Delivery: Earliest

`Recommended` must be a documented merchandising algorithm using eligibility,
availability, commercial relevance, quality, and bounded curation. Supplier
priority alone cannot determine ranking.

Best Selling remains hidden until statistically credible. No fabricated
popularity labels.

## Product Grid Behavior

- Desktop: 3-4 columns depending on viewport and image ratio.
- Tablet: 2-3 columns.
- Mobile: 2 columns by default; one-column rich view may be optional later.
- Stable card height and reserved image space prevent layout shift.
- Card shows image, name, supplier/manufacturer when relevant, price, variant
  summary, delivery signal, and saved action.
- Use badges sparingly: New, Founder Selection, Limited, Made to Order.
- Do not place long editorial copy in cards.
- Quick add is appropriate only for fixed, simple products.
- Configurable, consultation-required, or high-consideration products open the
  PDP.

## Category Integration

- Commercial collections may appear as bounded cards between product rows.
- Editorial modules appear no more than once in the first result set.
- Collection or editorial insertions cannot distort filter counts.
- Products shown inside an insertion must still pass the exact exposure gate.

# F. Collection Experience Architecture

## Commercial Collections

Examples:

- Founder Selection
- New Arrivals
- Permanent material or room collections
- Seasonal outdoor collection

Purpose:

- increase product consideration;
- support launches and stable merchandising;
- create coherent cross-category baskets.

Structure:

1. title and concise membership rationale;
2. primary collection visual;
3. product grid immediately accessible;
4. optional collection groups;
5. supporting context;
6. related room/material paths.

## Editorial Collections

Examples:

- a room study;
- an atelier release;
- a material study;
- a care or provenance story.

Purpose:

- establish context and authority;
- introduce products through a coherent argument;
- support qualified organic discovery.

Editorial collections must:

- disclose their editorial logic;
- use governed product relationships;
- retain direct product access;
- expire or be reviewed when facts, rights, or assortment change.

## SEO Role

- Category pages target stable product intent.
- Room pages target stable spatial intent.
- Material pages target factual material intent.
- Permanent collections target durable curated intent.
- Editorial collections target time-bound or narrative intent.
- New Arrivals and filter combinations should not create uncontrolled indexable
  URL proliferation.
- Canonicals, pagination, retired collection handling, and facet indexing
  require explicit SEO governance before activation.

# G. Product Detail Page Architecture

## Desktop Hierarchy

```text
1. Breadcrumb

2. Primary purchase zone
   Left: gallery
   Right: product identity and purchase panel

3. Product identity
   Name
   Manufacturer / atelier where relevant
   Price and tax
   Variant / finish / size selection
   Availability and delivery estimate
   Consultation state
   Primary CTA
   Save action

4. Immediate confidence facts
   Material
   Dimensions
   Lead time
   Delivery class
   Returns

5. Product overview
   Concise factual description
   Signature detail

6. Configuration and fit
   Dimensions diagram
   Room-scale guidance
   Assembly / installation
   Access requirements

7. Material authority
   Primary and secondary materials
   Finish
   Variation
   Care
   Evidence-backed sustainability/compliance only

8. Delivery and service
   Supplier-specific dispatch
   Delivery method
   Room-of-choice / assembly where available
   Returns
   Damage/support path

9. Product story
   Craftsmanship
   Spatial presence
   Illumination character where relevant
   Collectible identity where approved

10. Collection and room context
    Governed memberships only

11. Complementary products
    Complete the room / works with

12. Similar alternatives
    Same category and customer intent

13. Consultation module
    Trigger-specific support

14. Trust and policy references
```

## Gallery

Minimum sequence:

1. primary product image;
2. alternate angle;
3. material/detail;
4. scale or dimensions;
5. context image.

Additional media:

- configuration views;
- finish variants;
- assembly/installation;
- video or 3D only when it materially improves understanding.

The gallery must support zoom and preserve image context. Mobile uses swipe,
visible position count, and full-screen viewing.

## Purchase Panel

The panel answers, before the CTA:

- what is selected;
- what it costs;
- whether it is available;
- when and how it will arrive;
- whether consultation or configuration is required;
- what the customer can change.

### CTA Model

- `Add to bag`: fixed sellable product.
- `Select options`: required variant selection.
- `Configure`: governed configuration flow.
- `Request consultation`: consultation-required product.
- `Notify me`: approved unavailable-product workflow.
- No active CTA for non-sellable or unpublished products.

## Multi-Supplier Fulfillment

The PDP shows customer-facing fulfillment facts, not internal supplier
governance:

- sold by Veylune;
- dispatched timeframe;
- delivery provider/class;
- package count or two-person delivery where useful;
- assembly/installation availability;
- return conditions;
- split-shipment expectation where relevant.

## Related Product Logic

Priority:

1. required or strongly compatible accessories;
2. same collection;
3. complete the room;
4. material complement;
5. similar alternative.

Every relationship requires compatibility or attribution logic. Avoid generic
“you may also like” carousels driven only by views.

# H. Search Experience Architecture

## Search Entry

- Persistent search action in global header.
- Desktop opens a focused overlay or expanded search field.
- Mobile opens a dedicated full-screen search state.
- Search remains accessible from empty states, category pages, and the bag.

## Autocomplete Hierarchy

After 2-3 characters:

1. query completion;
2. exact products;
3. categories;
4. rooms;
5. collections;
6. materials;
7. editorial guides.

Autocomplete should show product image, name, price, and availability signal for
product results. It must not expose unpublished or ineligible products.

## Query Understanding

Search should resolve:

- product names and SKUs;
- synonyms and controlled aliases;
- categories and product types;
- rooms;
- materials and finishes;
- styles;
- collections;
- manufacturers;
- common dimensions and use intent.

Examples:

```text
"travertine side table"
→ material + product type

"bedroom lamp"
→ room + category

"founder selection"
→ collection

"oak desk under 1500"
→ material + category + price
```

## Search Results

1. query and result count;
2. corrected-query or no-result guidance;
3. category/room/material shortcuts where useful;
4. standard filter and sort architecture;
5. product grid;
6. relevant collection/editorial result after products.

## No-Result Strategy

- correct spelling without silently changing meaning;
- show related categories, materials, and rooms;
- preserve the original query;
- offer consultation only after useful shopping alternatives;
- log unresolved demand for taxonomy, synonym, and assortment review.

# I. Mobile Commerce Architecture

## Mobile Global Structure

```text
Header:
Menu | Logo | Search | Bag

Secondary actions inside menu:
Account | Saved | Consultation | Locale
```

Avoid crowding the mobile header with all desktop actions.

## Mobile Navigation

Level 1:

- Shop
- Rooms
- Collections
- Journal
- Consultation

Shop drill-down:

```text
Department
→ Category
→ Subcategory
```

Requirements:

- preserve back context;
- show `View all` at every level;
- display only published populated nodes;
- keep search available;
- do not use hover-dependent behavior.

## Mobile Homepage

Order remains the same as desktop, but:

- category access becomes a compact two-column grid;
- product modules use horizontal browsing only when the next item is visibly
  discoverable;
- room imagery does not hide labels;
- consultation and trust modules remain concise.

## Mobile Category

- Sticky row: `Filter` and `Sort`.
- Applied filter chips scroll horizontally.
- Product count remains visible.
- Filters open in a full-height sheet.
- Preserve scroll position after PDP return.
- Product cards prioritize image, name, price, delivery, and saved state.

## Mobile PDP

```text
Gallery
Name / price / delivery
Selections
Primary CTA
Immediate facts
Description
Dimensions and fit
Materials and care
Delivery and returns
Story
Related products
Consultation
```

- Sticky bottom CTA appears only after the primary purchase action leaves view.
- Sticky CTA reflects selection and availability state.
- Accordion sections are acceptable for secondary facts, but material,
  dimensions, delivery, and returns must remain easy to find.
- Variant selectors use legible labels, not color dots alone.
- Consultation-required products must not show a misleading active Add to Bag
  action.

## Mobile Cart and Checkout

- Bag summary before recommendations.
- Recommendations are limited and appear after totals.
- Checkout uses one primary action per step.
- Address, delivery, payment, and review states expose progress.
- Order summary remains expandable and accessible.
- Error messages appear beside the affected field and in an accessible summary.

# J. Revenue Optimization Architecture

## Cross-Sell

| Context | Opportunity | Rule |
| --- | --- | --- |
| PDP | Compatible object, textile, light, or care item | Verified compatibility or governed room relationship |
| Cart | Small number of complementary products | Do not interrupt checkout; exclude consultation-required complexity |
| Post purchase | Care, accessory, or room complement | Respect delivery state and avoid immediate high-pressure resale |

## Upsell

- larger size where fit is clear;
- higher-authority material or finish where genuinely comparable;
- upgraded configuration;
- delivery/assembly service;
- extended service only where available and valuable.

Upsell must explain the difference. Do not rank by margin alone.

## Basket Expansion

Architecture:

- `Complete the room` on PDP and cart;
- collection-based bundles without forced discounting;
- material-compatible combinations;
- saved room lists;
- free-shipping progress only when economically and legally appropriate;
- consultation outcome may produce a governed saved selection.

## Trust-Building

### Product Trust

- material composition and confidence;
- exact dimensions;
- care;
- compliance;
- variant clarity;
- real availability and lead time.

### Transaction Trust

- tax and shipping transparency;
- secure payment;
- guest checkout;
- returns;
- supplier-aware shipments;
- no late surprise fees.

### Service Trust

- consultation trigger and scope;
- delivery preparation;
- order status;
- damage/return support;
- care and assembly records.

## Revenue Guardrails

- No fake scarcity.
- No false countdowns.
- No fabricated popularity.
- No hidden delivery cost.
- No preselected paid option without explicit consent.
- No cross-sell that contradicts dimensions, materials, installation, or room
  relevance.
- No Founder badge without a current founder decision.

# K. Competitive Mapping

## Westwing Influence: 40%

Adopt:

- broad category and subcategory access;
- room and inspiration-led entry;
- rich PLP filters and commercial sorting;
- visible variants on product cards where useful;
- delivery, dimensions, material, returns, and service detail;
- complementary products and room completion;
- design-service integration;
- trust signals around payment, delivery, returns, and warranty.

Westwing currently exposes product-led navigation, Inspiration, New, Outdoor,
Deals, and Design Service; its large furniture listing uses subcategory access,
sorts, extensive material/color/style/brand filters, and product variants. Its
PDP combines a deep gallery, variants, dimensions, product details, material,
delivery, returns, warranty, complementary products, and “Shop the Look.”

Do not adopt:

- promotion-heavy hierarchy as the permanent brand system;
- excessive facet values;
- repetitive merchandising modules;
- urgency patterns that weaken Veylune restraint.

## Apple Influence: 40%

Adopt:

- few primary decisions at a time;
- strong hierarchy and whitespace;
- progressive configuration;
- explicit selected-state feedback;
- delivery and support before purchase;
- focused CTAs;
- guest and account continuity;
- post-purchase status, tracking, edits, returns, and support;
- service as part of the product value.

Apple’s store highlights specialist help, setup, payment options, delivery or
pickup, accessories, and connected services. Its product purchase flow makes
variant and configuration decisions explicit. Apple’s shopping help describes
bag review, guest checkout, delivery estimates, order-status stages, shipment
tracking, changes, cancellation, and returns.

Do not adopt:

- device-style configuration for products that do not require it;
- ecosystem bundling that obscures standalone product value;
- excessive product-page length without furniture-specific fit information.

## Veylune Influence: 20%

Own:

- material evidence as a commerce tool;
- Founder Selection as a narrow authority;
- room relevance as governed attribution;
- quiet, architectural art direction;
- consultation based on objective triggers;
- clear distinction between factual product data and editorial context;
- restrained badges, promotions, and recommendations;
- multi-supplier presentation under one Veylune service standard.

## Combined Expression

| Dimension | Westwing | Apple | Veylune result |
| --- | --- | --- | --- |
| Assortment | Broad and inspirational | Narrow and structured | Broad but governed |
| Navigation | Category and campaign rich | Minimal top level | Simple top level, deep mega menu |
| PLP | Dense filters and variants | Focused comparison | Controlled facets and clear cards |
| PDP | Content-rich, cross-sell heavy | Guided, precise, service-led | Material-rich, fit-led, restrained conversion |
| Service | Design service and delivery | Specialist support and setup | Consultation and fulfillment confidence |
| Brand | Lifestyle commerce | Product system | Quiet material authority |

# L. Executive Deliverables

## 1. Complete UX Architecture Map

```text
Global Commerce
├── Homepage
├── Shop
│   ├── Departments
│   ├── Categories
│   └── Product Detail
├── Discovery
│   ├── Rooms
│   ├── Collections
│   ├── Materials
│   └── Styles
├── Search
├── Journal
├── Consultation
├── Saved / Wishlist
├── Bag
├── Checkout
├── Account
└── Post Purchase
```

## 2. Homepage Wireframe Hierarchy

```text
Service strip
Header
Hero
Categories
New Arrivals
Rooms
Founder Selection
Best Sellers / Most Considered
Material Focus
Commercial Collection
Editorial Commerce
Consultation
Trust
Footer
```

## 3. Navigation Hierarchy

Primary:

```text
Shop | Rooms | Collections | Journal | Consultation
```

Actions:

```text
Search | Account | Saved | Bag
```

## 4. Category Hierarchy

```text
Furniture
Lighting
Decor & Objects
Textiles & Rugs
Dining & Kitchen
Outdoor
```

Depth grows below these stable departments through governed categories and
subcategories.

## 5. PDP Hierarchy

```text
Breadcrumb
Gallery + Purchase panel
Immediate facts
Overview
Dimensions and fit
Materials and care
Delivery and service
Product story
Collection / room context
Complements
Alternatives
Consultation
Trust
```

## 6. Mobile Hierarchy

```text
Menu / Logo / Search / Bag
Dedicated discovery navigation
Compact homepage modules
Sticky PLP filter and sort
Gallery-first PDP
Selection-aware sticky CTA
Single-action checkout steps
Accessible order status
```

## 7. Revenue Architecture

```text
Qualified traffic
→ relevant discovery
→ controlled refinement
→ complete product confidence
→ compatible basket expansion
→ transparent checkout
→ supplier-aware fulfillment
→ care, support, and repeat consideration
```

## 8. Recommended Implementation Order

This sequence is future planning only.

### Stage 0: Activation Prerequisites

- authorize route-family policy changes;
- complete product/category/collection publication enforcement;
- complete sellability, payment, shipping, tax, and returns readiness;
- define search indexing governance;
- define account, wishlist, and post-purchase policies;
- verify analytics and consent architecture.

### Stage 1: Commerce Information Architecture

- finalize category tree and URL model;
- approve mega-menu and mobile navigation;
- approve room, material, collection, and style destination rules;
- define facet and sort publication.

### Stage 2: Product and Listing Foundations

- product card contract;
- category/listing contract;
- filter/sort behavior;
- PDP information and CTA state contract;
- accessibility and performance budgets.

### Stage 3: Cart and Checkout

- cart and mini-cart hierarchy;
- multi-supplier shipment grouping;
- guest/account flow;
- payment, delivery, error, and order-review states.

### Stage 4: Search

- indexing eligibility;
- synonyms and controlled aliases;
- autocomplete;
- results and no-result behavior;
- search analytics.

### Stage 5: Homepage and Discovery

- homepage modules;
- rooms;
- materials;
- collections;
- styles;
- editorial commerce.

Homepage follows commerce foundations so it links only to verified destinations.

### Stage 6: Revenue Merchandising

- cross-sell;
- room completion;
- alternatives;
- saved selections;
- Best Sellers after data threshold;
- recommendation governance.

### Stage 7: Post Purchase

- order status;
- split shipment and supplier-aware updates;
- delivery preparation;
- care/assembly content;
- returns and support;
- measured re-engagement.

### Stage 8: Optimization

- funnel analytics;
- search demand;
- filter use;
- PDP decision friction;
- cart and checkout abandonment;
- consultation outcomes;
- controlled experimentation.

## Architecture Acceptance Criteria

Implementation should not begin until:

- every page type has an owner and activation prerequisite;
- the category and URL model supports at least 1500 products without changing
  top-level departments;
- every discovery path resolves only governed eligible products;
- multi-supplier delivery and return behavior is defined;
- PDP CTA states map to publication, sellability, availability, configuration,
  and consultation;
- filters use published controlled values;
- mobile behavior is specified alongside desktop;
- search, cart, checkout, account, and post-purchase states include failure and
  empty-state behavior;
- revenue modules have compatibility and governance rules;
- accessibility, performance, SEO, analytics, privacy, and rollback are
  explicit implementation gates.

## Reference Review

Competitive observations were reviewed against official public pages on June 6,
2026:

- Westwing homepage: `https://www.westwing.de/`
- Westwing furniture listing: `https://www.westwing.de/moebel/`
- Westwing Lennon sofa PDP:
  `https://www.westwing.de/modulares-ecksofa-lennon-105582.html`
- Apple Store: `https://www.apple.com/store`
- Apple product purchase flow: `https://www.apple.com/shop/buy-iphone`
- Apple Shopping Help: `https://www.apple.com/shop/help`
- Apple Shipping and Pickup:
  `https://www.apple.com/shop/help/shipping_delivery`
- Apple Order Status guidance:
  `https://www.apple.com/shop/help/viewing_changing_orders`
