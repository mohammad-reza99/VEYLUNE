# VEYLUNE STUDIO

## WP-CAT-04 — Shopware-Ready Draft Catalog Seeding Plan

**Status:** Seeding implementation blueprint  
**Platform target:** Shopware 6.7.10.0  
**Scope:** 50 governed products from WP-CAT-01, WP-CAT-02, and WP-CAT-03  
**Implementation authority:** None  
**Public exposure authority:** None

---

## 1. Objective

Prepare one controlled future seeding operation that creates all 50 governed
products as private Shopware draft records.

The seed must preserve the approved product identity, department, product type,
room, collection, status, target price, and draft content while making every
record:

- inactive;
- unavailable for purchase;
- absent from every sales channel;
- absent from public category and collection listings;
- excluded from public search and autocomplete;
- inaccessible through a public PDP or SEO URL;
- ineligible for the Veylune exposure registry;
- reversible as one governed batch.

This plan does not authorize execution.

## 2. Source Authority

| Source | Authority used by this plan |
| --- | --- |
| WP-CAT-01 | Portfolio architecture, departments, product types, rooms, collections, materials, price positioning |
| WP-CAT-02 | Canonical IDs, SKUs, lifecycle defaults, assignments, target prices, consultation modes, readiness blockers |
| WP-CAT-03 | EN names, DE name drafts, commerce descriptions, material direction, status copy, visual direction, rail candidates |

Where a future supplier record contradicts a planning value, the supplier fact
must enter governance review. It must not silently overwrite the seeded draft.

---

# A. Fail-Closed Shopware State

## 3. Required Native Product State

Every payload must use these native Shopware controls:

```yaml
active: false
stock: 0
isCloseout: true
availableStock: 0
visibilities: []
categories: governed category associations
properties: governed draft material, room, and collection associations
coverId: null
media: []
```

### 3.1 Non-sellability

Shopware requires a tax and price when creating a standalone product. The seed
therefore stores the WP-CAT-02 target price as an unapproved planning price,
but it must not be interpreted as a commerce-ready price.

Non-sellability is enforced by:

- `active=false`;
- `stock=0`;
- `isCloseout=true`;
- no sales-channel visibility records;
- `publication_state=draft`;
- `sellability_status=not_sellable`;
- `availability_status=pending_acquisition`;
- `commerce_activation_state=blocked`;
- no exposure approval;
- no cart, checkout, feed, or recommendation admission.

The execution must resolve the existing standard tax ID and currency ID without
creating or changing tax or currency records. Net planning prices are derived
from the stored gross target using the resolved tax rate; this calculation is
technical payload completion, not pricing approval.

### 3.2 Public visibility

The seed must create **zero** `product_visibility` rows. No visibility level,
including link-only visibility, is permitted.

Category assignment does not confer visibility. Products remain attached to
their governed categories for internal catalog preparation while native
activity and sales-channel visibility remain disabled.

### 3.3 Search and indexing

“Not indexed” means excluded from every public search, autocomplete, feed,
sitemap, and route projection.

Shopware may still run internal DAL and entity index maintenance for persisted
records. Per-product suppression of that internal maintenance is neither
required nor equivalent to public search exclusion.

Public exclusion requires:

- `active=false`;
- no `product_visibility` rows;
- `search_index_state=excluded`;
- `search_exposure_status=not_approved`;
- no generated SEO URL;
- no public canonical route;
- no exposure-registry entry;
- no product-stream or recommendation assignment;
- no direct Store API eligibility.

## 4. Required Governance State

All 50 records receive:

```yaml
publication_state: draft
readiness_level: L0
quality_status: incomplete
sellability_status: not_sellable
availability_status: pending_acquisition
exposure_status: not_approved
search_index_state: excluded
storefront_activation_state: blocked
commerce_activation_state: blocked
sales_channel_visibility_state: none
collection_relationship_state: candidate
room_relationship_state: planning_inferred
material_confidence: target_unverified
source_batch: WP-CAT-04-DRAFT-50
record_owner: product_governance
rollback_target: delete_wp_cat_04_draft_batch
```

`Coming Soon` and `Supplier Selection` are planning statuses, not publication
states.

---

# B. Shopware Data Contract

## 5. Native Product Fields

| Shopware field | Seed rule |
| --- | --- |
| `id` | Deterministic UUID derived from the WP-CAT record ID and batch namespace |
| `productNumber` | Canonical WP-CAT-02 SKU |
| `name` | Approved EN draft from WP-CAT-03 |
| `translated.name` | EN name plus DE name draft |
| `description` | WP-CAT-03 commerce description; clearly marked draft in governance metadata |
| `active` | `false` |
| `stock` | `0` |
| `isCloseout` | `true` |
| `taxId` | Existing standard tax ID resolved during preflight |
| `price` | CAT-02 target EUR gross and derived net, tagged unapproved |
| `purchaseSteps` | `1` |
| `minPurchase` | `1` |
| `shippingFree` | `false` |
| `categoryIds` | Governed department and primary product-type category IDs |
| `propertyIds` | Governed material, room, and collection option IDs |
| `visibilityIds` | Empty |
| `media` | Empty until rights-cleared assets exist |
| `coverId` | `null` |
| `manufacturerId` | `null` until supplier selection |
| `ean` | `null` |
| `deliveryTimeId` | `null` |
| `weight`, dimensions | `null` |

No placeholder media, fake manufacturer, fabricated EAN, dimensions, delivery
time, stock, or supplier facts may be inserted.

## 6. Custom Field Set

Create or resolve one internal product custom-field set named
`veylune_catalog_governance`. Creation of the set is a prerequisite task and
does not itself authorize product seeding.

### 6.1 Identity and lifecycle

```text
veylune_catalog_record_id
veylune_publication_state
veylune_readiness_level
veylune_status_copy
veylune_source_batch
veylune_record_owner
veylune_rollback_target
```

### 6.2 Classification and relationships

```text
veylune_department_key
veylune_product_type_key
veylune_primary_material_key
veylune_secondary_material_keys
veylune_room_relationships
veylune_collection_relationships
veylune_consultation_mode
veylune_rail_candidates
veylune_founder_potential
```

Relationship values are structured JSON arrays containing:

```json
{
  "key": "living_room",
  "status": "candidate",
  "confidence": "planning_inferred",
  "source": "WP-CAT-02",
  "exposureApproved": false
}
```

### 6.3 Commerce and exposure controls

```text
veylune_target_price_gross
veylune_price_status
veylune_sellability_status
veylune_availability_status
veylune_exposure_status
veylune_search_index_state
veylune_storefront_activation_state
veylune_commerce_activation_state
```

### 6.4 Draft content

```text
veylune_material_story_draft
veylune_feature_drafts
veylune_primary_image_direction
veylune_detail_image_direction
veylune_context_image_direction
veylune_content_source
```

The custom fields preserve the complete CAT-03 production brief without
misusing public Shopware fields for unverified claims.

## 7. Taxonomy Associations

### 7.1 Categories

Each product receives exactly:

1. one department category;
2. one primary product-type category.

The execution must resolve existing governed category IDs by canonical key. It
must fail before writing products if any required category is missing,
duplicated, retired, or mapped outside its approved department.

No category may be created opportunistically by the product seed.

### 7.2 Rooms

Rooms use the governed `Room` property group and these canonical options:

- `living_room`
- `dining_room`
- `bedroom`
- `home_office`
- `hallway`
- `outdoor`

The public label `Workspace` maps to canonical key `home_office`.

Room options may be associated with inactive draft products, but every
relationship remains `planning_inferred` and `exposureApproved=false`.

### 7.3 Collections

Collections use the governed `Collection` property group:

| Code | Canonical key | Type |
| --- | --- | --- |
| NA | `new_arrivals` | Time-bound commercial candidate |
| FS | `founder_selection` | Founder-controlled candidate |
| QL | `quiet_living` | Permanent candidate |
| MF | `material_forms` | Permanent candidate |
| AL | `architectural_light` | Permanent candidate |
| TR | `table_rituals` | Permanent candidate |
| OA | `open_air` | Permanent candidate |

The seed stores candidate relationships only. It must not:

- add products to the runtime exposure registry;
- set founder approval;
- start a New Arrivals availability window;
- create public product streams;
- imply active collection membership.

---

# C. Complete Seed Matrix

## 8. Furniture

| Record | SKU | Status | Department / type | Rooms | Collections | Target EUR |
| --- | --- | --- | --- | --- | --- | ---: |
| F01 Aurelia Modular Sofa | `VLS-FUR-000001` | Coming Soon | Furniture / Sofas | Living Room | QL, FS | 4,900 |
| F02 Liora Curved Sofa | `VLS-FUR-000002` | Supplier Selection | Furniture / Sofas | Living Room | QL | 4,200 |
| F03 Oris Leather Lounge Chair | `VLS-FUR-000003` | Supplier Selection | Furniture / Lounge Chairs | Living Room, Bedroom | QL, FS | 2,250 |
| F04 Selene Oak Lounge Chair | `VLS-FUR-000004` | Supplier Selection | Furniture / Lounge Chairs | Living Room, Bedroom | QL | 1,850 |
| F05 Edda Dining Chair | `VLS-FUR-000005` | Supplier Selection | Furniture / Dining Chairs | Dining Room | QL | 690 |
| F06 Noma Metal Dining Chair | `VLS-FUR-000006` | Supplier Selection | Furniture / Dining Chairs | Dining Room | MF | 760 |
| F07 Forma Desk Chair | `VLS-FUR-000007` | Supplier Selection | Furniture / Office Chairs | Home Office | QL | 1,350 |
| F08 Talo Counter Stool | `VLS-FUR-000008` | Supplier Selection | Furniture / Benches & Stools | Dining Room | QL | 640 |
| F09 Stillwater Oak Bench | `VLS-FUR-000009` | Supplier Selection | Furniture / Benches & Stools | Hallway, Bedroom, Dining Room | QL | 1,250 |
| F10 Elara Travertine Coffee Table | `VLS-FUR-000010` | Supplier Selection | Furniture / Coffee Tables | Living Room | MF, FS | 2,200 |
| F11 Varo Oak Coffee Table | `VLS-FUR-000011` | Supplier Selection | Furniture / Coffee Tables | Living Room | QL | 1,650 |
| F12 Neri Marble Side Table | `VLS-FUR-000012` | Supplier Selection | Furniture / Side Tables | Living Room, Bedroom | MF | 890 |
| F13 Portico Travertine Console | `VLS-FUR-000013` | Supplier Selection | Furniture / Consoles | Hallway, Living Room | MF, FS | 2,750 |
| F14 Linea Writing Desk | `VLS-FUR-000014` | Supplier Selection | Furniture / Desks | Home Office, Bedroom | QL, FS | 2,450 |
| F15 Serein Platform Bed | `VLS-FUR-000015` | Supplier Selection | Furniture / Beds | Bedroom | QL, FS | 3,600 |
| F16 Vale Low Cabinet | `VLS-FUR-000016` | Supplier Selection | Furniture / Storage | Living Room, Dining Room | QL | 2,800 |
| F17 Canto Tall Cabinet | `VLS-FUR-000017` | Supplier Selection | Furniture / Storage | Living Room, Home Office | QL | 3,200 |
| F18 Mira Leather Day Chair | `VLS-FUR-000018` | Supplier Selection | Furniture / Lounge Chairs | Living Room, Bedroom | MF | 1,950 |
| F19 Plinth Glass Side Table | `VLS-FUR-000019` | Supplier Selection | Furniture / Side Tables | Living Room, Bedroom | MF | 820 |

## 9. Lighting

| Record | SKU | Status | Department / type | Rooms | Collections | Target EUR |
| --- | --- | --- | --- | --- | --- | ---: |
| L01 Nocturne Floor Lamp | `VLS-LGT-000001` | Coming Soon | Lighting / Floor Lamps | Living Room, Home Office | AL | 1,450 |
| L02 Orbis Counterweighted Floor Lamp | `VLS-LGT-000002` | Supplier Selection | Lighting / Floor Lamps | Living Room, Home Office | AL, FS | 1,850 |
| L03 Lumen Ceramic Table Lamp | `VLS-LGT-000003` | Supplier Selection | Lighting / Table Lamps | Bedroom, Living Room | AL | 590 |
| L04 Halo Ribbed Glass Pendant | `VLS-LGT-000004` | Supplier Selection | Lighting / Pendant Lights | Dining Room, Hallway | AL, FS | 1,150 |
| L05 Axis Linear Floor Lamp | `VLS-LGT-000005` | Supplier Selection | Lighting / Floor Lamps | Living Room | AL | 1,650 |
| L06 Alba Stone Table Lamp | `VLS-LGT-000006` | Supplier Selection | Lighting / Table Lamps | Bedroom, Living Room | MF | 790 |
| L07 Vela Ceramic Table Lamp | `VLS-LGT-000007` | Supplier Selection | Lighting / Table Lamps | Bedroom, Home Office | AL | 520 |
| L08 Meridian Pendant | `VLS-LGT-000008` | Supplier Selection | Lighting / Pendant Lights | Dining Room | AL | 1,250 |
| L09 Lucent Glass Wall Light | `VLS-LGT-000009` | Supplier Selection | Lighting / Wall Lighting | Bedroom, Hallway | AL | 620 |
| L10 Linea Wall Sconce | `VLS-LGT-000010` | Supplier Selection | Lighting / Wall Lighting | Hallway, Living Room | AL | 540 |

## 10. Decor & Objects

| Record | SKU | Status | Department / type | Rooms | Collections | Target EUR |
| --- | --- | --- | --- | --- | --- | ---: |
| D01 Atelier Stone Vessel | `VLS-DEC-000001` | Coming Soon | Decor & Objects / Vessels | Living Room, Dining Room, Hallway | MF | 420 |
| D02 Cairn Stone Vessel | `VLS-DEC-000002` | Supplier Selection | Decor & Objects / Vessels | Living Room, Hallway | MF | 480 |
| D03 Tectona Travertine Vessel | `VLS-DEC-000003` | Supplier Selection | Decor & Objects / Vessels | Living Room, Dining Room | MF, FS | 560 |
| D04 Meridian Cast Sculpture | `VLS-DEC-000004` | Supplier Selection | Decor & Objects / Sculptural Objects | Living Room, Home Office | MF, FS | 740 |
| D05 Arc Full-Length Mirror | `VLS-DEC-000005` | Supplier Selection | Decor & Objects / Mirrors | Bedroom, Hallway | QL, FS | 1,450 |
| D06 Strata Valet Tray | `VLS-DEC-000006` | Supplier Selection | Decor & Objects / Trays | Hallway, Bedroom | MF | 320 |
| D07 Forma Ceramic Object | `VLS-DEC-000007` | Supplier Selection | Decor & Objects / Decorative Objects | Living Room, Dining Room | MF | 290 |
| D08 Monolith Stone Object | `VLS-DEC-000008` | Supplier Selection | Decor & Objects / Sculptural Objects | Living Room, Hallway | MF | 680 |
| D09 Orbit Metal Object | `VLS-DEC-000009` | Supplier Selection | Decor & Objects / Decorative Objects | Living Room, Home Office | MF | 360 |

## 11. Textiles & Rugs

| Record | SKU | Status | Department / type | Rooms | Collections | Target EUR |
| --- | --- | --- | --- | --- | --- | ---: |
| T01 Tactile Hand-Knotted Rug | `VLS-TEX-000001` | Supplier Selection | Textiles & Rugs / Rugs | Living Room, Bedroom | QL, FS | 1,850 |
| T02 Loma Wool Runner | `VLS-TEX-000002` | Supplier Selection | Textiles & Rugs / Rugs | Hallway, Bedroom | QL | 890 |
| T03 Sera Woven Throw | `VLS-TEX-000003` | Supplier Selection | Textiles & Rugs / Throws | Living Room, Bedroom | QL | 320 |
| T04 Vale Textured Cushion | `VLS-TEX-000004` | Supplier Selection | Textiles & Rugs / Cushions | Living Room, Bedroom | QL | 160 |
| T05 Noma Bouclé Cushion | `VLS-TEX-000005` | Supplier Selection | Textiles & Rugs / Cushions | Living Room, Bedroom | QL | 180 |

## 12. Dining & Kitchen

| Record | SKU | Status | Department / type | Rooms | Collections | Target EUR |
| --- | --- | --- | --- | --- | --- | ---: |
| K01 Calma Travertine Dining Table | `VLS-DIN-000001` | Coming Soon | Dining & Kitchen / Dining Tables | Dining Room | MF, FS | 1,399 |
| K02 Sera Ceramic Place Setting | `VLS-DIN-000002` | Supplier Selection | Dining & Kitchen / Tableware | Dining Room | TR | 240 |
| K03 Talo Oak Serving Board | `VLS-DIN-000003` | Supplier Selection | Dining & Kitchen / Serveware | Dining Room | TR | 190 |
| K04 Linea Countertop Object | `VLS-DIN-000004` | Supplier Selection | Dining & Kitchen / Kitchen Objects | Dining Room | TR | 260 |

## 13. Outdoor

| Record | SKU | Status | Department / type | Rooms | Collections | Target EUR |
| --- | --- | --- | --- | --- | --- | ---: |
| O01 Terra Outdoor Lounge Chair | `VLS-OUT-000001` | Supplier Selection | Outdoor / Outdoor Seating | Outdoor | OA, FS | 1,650 |
| O02 Monolith Outdoor Table | `VLS-OUT-000002` | Supplier Selection | Outdoor / Outdoor Tables | Outdoor | OA, FS | 2,400 |
| O03 Grove Teak Planter | `VLS-OUT-000003` | Supplier Selection | Outdoor / Planters & Objects | Outdoor | OA | 520 |

---

# D. Seeding Package Design

## 14. Required Artifacts

The future implementation package should contain:

```text
catalog-seed/
├── manifest.json
├── products/
│   ├── furniture.json
│   ├── lighting.json
│   ├── decor-objects.json
│   ├── textiles-rugs.json
│   ├── dining-kitchen.json
│   └── outdoor.json
├── mappings/
│   ├── categories.json
│   ├── properties.json
│   ├── tax-currency.json
│   └── custom-fields.json
├── snapshots/
│   └── pre-seed.json
└── reports/
    ├── dry-run.json
    ├── write-result.json
    └── post-seed-audit.json
```

This document does not create those files.

## 15. Manifest Contract

```json
{
  "batchId": "WP-CAT-04-DRAFT-50",
  "mode": "create_draft_only",
  "expectedProducts": 50,
  "allowedStatuses": ["Coming Soon", "Supplier Selection"],
  "publicExposureAllowed": false,
  "commerceActivationAllowed": false,
  "storefrontActivationAllowed": false,
  "createTaxonomyAllowed": false,
  "createMediaAllowed": false,
  "createSeoUrlsAllowed": false,
  "rollbackTarget": "delete_wp_cat_04_draft_batch"
}
```

## 16. Product Payload Shape

```json
{
  "id": "<deterministic-uuid>",
  "productNumber": "VLS-FUR-000001",
  "active": false,
  "stock": 0,
  "isCloseout": true,
  "taxId": "<resolved-existing-tax-id>",
  "price": [{
    "currencyId": "<resolved-eur-id>",
    "gross": 4900.00,
    "net": "<derived-from-resolved-tax>",
    "linked": true
  }],
  "translations": {
    "<en-language-id>": {
      "name": "Aurelia Modular Sofa",
      "description": "<CAT-03 commerce description>"
    },
    "<de-language-id>": {
      "name": "Aurelia Modulares Sofa",
      "description": null
    }
  },
  "categoryIds": [
    "<furniture-category-id>",
    "<sofas-category-id>"
  ],
  "propertyIds": [
    "<upholstery-fabric-option-id>",
    "<living-room-option-id>",
    "<quiet-living-option-id>",
    "<founder-selection-option-id>"
  ],
  "visibilities": [],
  "customFields": {
    "veylune_catalog_record_id": "F01",
    "veylune_publication_state": "draft",
    "veylune_readiness_level": "L0",
    "veylune_status_copy": "Coming Soon",
    "veylune_sellability_status": "not_sellable",
    "veylune_search_index_state": "excluded",
    "veylune_exposure_status": "not_approved",
    "veylune_source_batch": "WP-CAT-04-DRAFT-50"
  }
}
```

The German description remains null because CAT-03 supplies a German name
draft, not an approved German commerce description.

---

# E. Execution Blueprint

## 17. Phase 0 — Authorization

Execution requires a separately approved implementation work package naming:

- environment;
- operator;
- product-governance approver;
- taxonomy and collection approvers;
- rollback owner;
- maintenance window;
- exact command or Admin API client;
- backup and audit locations.

Production execution is prohibited until that authorization exists.

## 18. Phase 1 — Preflight

Resolve and validate without writing:

1. Shopware version is 6.7.10.0 or explicitly requalified.
2. No canonical SKU already exists.
3. No deterministic target UUID already belongs to another entity.
4. All six department categories resolve uniquely.
5. All required product-type categories resolve under the correct department.
6. All material, room, and collection options resolve uniquely.
7. EN and DE language IDs resolve.
8. EUR currency and standard tax IDs resolve.
9. The governance custom-field set and fields exist with correct types.
10. No source record is active, visible, exposed, or publicly indexed.
11. A pre-seed snapshot and empty-batch rollback checkpoint exist.

Any failure aborts the entire batch before the first product write.

## 19. Phase 2 — Dry Run

The dry run builds all 50 final payloads and validates:

- record count is exactly 50;
- SKUs and UUIDs are unique;
- department allocation is `19/10/9/5/4/3`;
- statuses are exactly four `Coming Soon` and 46 `Supplier Selection`;
- every record has one department and one product type;
- every record has at least one room and one collection candidate;
- all relationships use canonical keys;
- every product is inactive, stock-zero, closeout, and visibility-empty;
- every exposure and activation state is blocked;
- no media, SEO URL, product stream, or sales-channel association is requested.

Dry-run success permits review, not execution.

## 20. Phase 3 — Transactional Write

Preferred write path:

1. one versioned internal command or Admin API batch client;
2. deterministic UUIDs;
3. product repository `upsert` in bounded chunks;
4. one execution context carrying batch and operator metadata;
5. stop-on-first-error semantics;
6. no asynchronous follow-up that creates visibility, SEO URLs, or exposure.

If the selected write mechanism cannot provide reliable all-or-nothing
behavior across 50 records, use six department checkpoints and automatically
roll back every completed checkpoint after any later failure.

## 21. Phase 4 — Post-Seed Audit

The operation passes only when database and application-level checks report:

```text
Products with source batch WP-CAT-04-DRAFT-50: 50
Active products: 0
Products with sales-channel visibility: 0
Sellable products: 0
Products with positive stock: 0
Products with generated SEO URLs: 0
Products in public search/autocomplete: 0
Products in sitemap or feeds: 0
Products in exposure registry: 0
Products with PDP route access: 0
Products with media: 0
Products with category assignments: 50
Products with room assignments: 50
Products with collection candidates: 50
Coming Soon records: 4
Supplier Selection records: 46
```

Public storefront smoke tests must show no change in:

- homepage products;
- category listings;
- room destinations;
- collection destinations;
- search and autocomplete;
- sitemap;
- Store API product results;
- direct PDP routes;
- cart and checkout eligibility.

## 22. Phase 5 — Acceptance

Acceptance requires signed reports from:

- product governance;
- taxonomy governance;
- collection governance;
- platform operator;
- rollback owner.

The accepted result is 50 private L0 records. Acceptance does not advance any
record to review, approval, publication, sellability, or exposure.

---

# F. Rollback and Leakage Controls

## 23. Rollback

Rollback selection uses both:

- `veylune_source_batch=WP-CAT-04-DRAFT-50`;
- the manifest's deterministic product UUID allowlist.

Rollback must:

1. verify none of the batch records advanced beyond `draft`;
2. delete product-property and product-category relationships;
3. delete any accidental visibility or SEO records;
4. delete all 50 product records;
5. leave shared categories, properties, tax, currencies, languages, and custom
   field definitions intact;
6. run the complete post-rollback leakage audit;
7. retain immutable execution and rollback reports.

If any product has been manually enriched or moved beyond draft, automatic
deletion must stop and require product-governance review.

## 24. Hard Prohibitions

The CAT-04 seeding operation must not:

- set `active=true`;
- create any sales-channel visibility;
- set positive stock;
- set `isCloseout=false`;
- approve a price;
- generate SEO URLs;
- create or activate storefront routes;
- add products to the exposure registry;
- create public product streams;
- add homepage or recommendation exposure;
- grant Founder Selection status;
- start a New Arrivals window;
- create fake media or supplier data;
- publish draft German descriptions;
- create missing categories or uncontrolled property options;
- enable search, PDP, cart, checkout, feeds, or sitemap inclusion.

---

# G. Exit Decision

WP-CAT-04 is implementation-ready when the 50 payloads can be generated from
CAT-01 through CAT-03, every referenced Shopware entity resolves, the dry run
passes, and rollback is proven in a non-production environment.

The authorized seed outcome remains:

```text
50 governed Shopware draft products
0 public products
0 sellable products
0 searchable products
0 storefront changes
0 commerce activation
```

