# VEYLUNE STUDIO

## WP-COM-ARCH-01 - Enterprise Supplier & Variant Commerce Architecture Program

**Status:** Architecture authority for WP-COM-ARCH-02 through WP-COM-ARCH-09
**Platform baseline:** Shopware 6.7.10.0
**Assessment date:** 2026-06-11
**Decision:** **Not ready** for first real supplier onboarding without the architecture program below
**Scope:** Supplier master data, product families, variants, import, assets, PDP data, merchandising, discovery, publication, rollback, and scale
**Non-scope:** Implementation, database writes, storefront changes, route activation, supplier onboarding, and publication

---

# 0. Executive Architecture Decision

## 0.1 Primary answer

If five suppliers arrived tomorrow with approximately 50 families and 300-1000
sellable variants, the current system could safely hold planning records and
keep them private, but it could not govern the complete path from supplier
source through import, variant creation, asset approval, sellability,
publication, PDP, listing, search, cart, checkout, supplier update, and
reversible rollback without redesign.

The current architecture is strongest at containment:

- 50 deterministic draft products exist as inactive, zero-stock, zero-visibility
  parent products;
- draft categories and properties are inactive and private;
- preview is token-gated, development-only, no-store, and non-indexable;
- public product, category, collection, search, cart, and checkout routes remain
  `activation_pending`;
- publication, sellability, exposure, supplier, SKU, import, readiness, media,
  taxonomy, and rollback contracts exist;
- current governance is fail-closed.

It is not yet a supplier-commerce operating system:

- the 50 draft records are flat products, not family parents with sellable
  children;
- no real supplier master, contract, contact, source profile, or brand
  persistence model exists;
- supplier registries are PHP configuration and currently contain mock records;
- no production import application service, durable batch ledger, field-level
  source history, resumability, or compensating rollback exists;
- no native variant-generation/import contract exists;
- no supplier media ingest, checksum, rights, rendition, slot, or approval
  pipeline exists;
- no executable publication gate aggregates all required domains;
- no governed family-aware listing, rail, search, or duplicate suppression
  projection exists;
- current public exposure is a four-SKU static registry, not a scalable query
  or approval model;
- public PDP, listing, search, cart, and checkout remain intentionally denied.

## 0.2 Target architecture

Veylune shall use:

1. **Shopware native products as the commerce aggregate.**
   A non-sellable parent product represents a product family. Native child
   products represent sellable variants.
2. **Native configurator options for variant-defining dimensions.**
   Material, finish, color, and size become configurator groups only when they
   create a distinct purchasable SKU. Descriptive attributes remain properties.
3. **Dedicated Veylune entities for supplier and workflow data.**
   Supplier contracts, contacts, source mappings, import batches, assets,
   compliance evidence, approvals, and rollback snapshots must not be encoded
   as JSON strings in product custom fields.
4. **Custom fields only for bounded product-local projections.**
   Custom fields expose stable governance status, family identity, operational
   classes, and approved PDP data to Shopware DAL, rules, and templates.
5. **Categories for stable customer navigation only.**
   Rooms, collections, campaigns, supplier feeds, and rails must not become
   navigation taxonomy merely to support merchandising.
6. **Dynamic product groups for rule-derived candidate sets.**
   Final rails use a hybrid model: dynamic eligibility plus explicit curation
   and ordering.
7. **A durable, idempotent import control plane.**
   CSV/API adapters normalize into one canonical manifest; validation and
   planning precede Sync API writes; every commit has a snapshot and immutable
   operation ledger.
8. **One authoritative publication eligibility projection.**
   PDP retrieval, listings, search, rails, sitemap, cart admission, and checkout
   must consume the same fail-closed decision.

## 0.3 Non-negotiable invariants

- Supplier active status never publishes a product.
- Import approval never publishes a product.
- Shopware `active`, stock, availability, visibility, or SEO URL presence never
  independently establishes public eligibility.
- Parent products are not stock-bearing sellable records.
- Every sellable variant has one immutable Veylune SKU and one supplier mapping.
- A supplier SKU is unique only within a supplier; a Veylune SKU is globally
  unique and never reused.
- Unknown taxonomy, property, compliance, contract, rights, or fulfillment data
  blocks progression.
- Public reads use approved projections, not unreviewed supplier source data.
- Suspension removes PDP, listing, search, rail, sitemap, cart, and new checkout
  eligibility together.
- Rollback is batch-scoped, version-aware, auditable, and cannot silently
  overwrite later human approvals.

---

# 1. Current Architecture Inventory

## 1.1 Evidence baseline

Repository and database inspection on 2026-06-11 established:

| Measure | Current value |
| --- | ---: |
| Shopware version | 6.7.10.0 |
| Total product rows | 70 |
| Parent rows | 60 |
| Variant rows | 10, all Shopware demo residue |
| WP-CAT-04 draft products | 50 |
| Active draft products | 0 |
| Draft product visibilities | 0 |
| Draft positive stock | 0 |
| Draft product SEO URLs | 0 |
| Draft search keywords | 0 |
| Draft products with category assignments | 50 |
| Draft departments / product types | 6 / 30 |
| Draft material / room / collection options | 10 / 6 / 7 |
| Product streams | 0 |

`DraftCatalogSeeder` creates categories despite the CAT-04 document saying
missing taxonomy must not be created opportunistically. This is acceptable for
the isolated draft batch but must not become the production supplier-import
behavior.

CAT-03 and CAT-04 are present. CAT-04 references CAT-01 and CAT-02 as source
authorities, but no files named CAT-01 or CAT-02 are present in `docs/`.
DES-CAT-01 through DES-CAT-03b and STORE/COM documents are present.

## 1.2 Inventory by architecture area

| Area | Current state | Strengths | Weaknesses | Scale risk |
| --- | --- | --- | --- | --- |
| Product entity | One native Shopware product per draft concept; all are parent rows with deterministic UUIDs | Native DAL, price, tax, translation, category and property associations | No family/variant distinction; planning price occupies native price; no supplier/manufacturer | Flat model must be replaced before variant onboarding |
| Draft strategy | `active=false`, stock 0, closeout, no visibility, L0, `draft`, blocked commerce/search/exposure | Excellent containment and auditability | Draft and production lifecycle fields are string projections without executable aggregate gate | Manual field drift at hundreds of variants |
| SKU | `VLS-{DEP}-{6 digits}`; optional `-{2 digit variant}` contract; deterministic UUID from SKU | Veylune owns public identity; collision checks and retirement rules exist | No durable reservation table; two digits cap a family at 99 variants; family and variant semantics are not persisted | Reservation and supplier mapping races |
| Slug | EN/DE reserved slug custom fields derived from names; collision check; no SEO URL | Keeps drafts private and checks obvious collisions | Name-derived slug is mutable; no durable slug history, redirect policy, family canonical strategy, or locale route lifecycle | SEO churn and variant canonical collisions |
| Category | Inactive department and product-type category tree under acquisition root | Stable six departments and 30 controlled types | Seed owns taxonomy creation; production lifecycle not executable; primary category not explicit | Sparse branches and URL fragmentation |
| Room | `Veylune Room` multi-value properties and JSON relationship evidence | Controlled keys; preview filtering works | Property assignment and evidence/approval are duplicated; public facet status absent | Unreviewed room claims leak into discovery |
| Collection | `Veylune Collection` properties plus JSON relationship evidence | Candidate planning and preview rails exist | Commercial, editorial, campaign, and rail concepts are conflated | Stale membership and duplicate merchandising |
| Material | `Veylune Material` properties; primary key also stored in custom field | Controlled values and multilingual labels | No distinction between configurable material, descriptive material, composition, finish, or evidence | Variant explosion or misleading selectors |
| Custom fields | 31 draft governance/content fields, many JSON-encoded lists | Fast, bounded prototype; easy DAL filtering | Unqueryable JSON strings, no referential integrity, no actor/time/version history, product translation/custom-field ambiguity | Admin usability and data integrity fail first |
| Preview | Dedicated dev-only token routes read inactive products via admin DAL | 404 fail-closed, noindex, no-store, no PDP/cart IDs or links | Family-unaware cards; three rails only; repeated full product query; no media or selector preview | N+1/query repetition and inaccurate variant presentation |
| Public exposure | Static four-SKU `ProductExposureService`; exact surface registry; native active/available/price/media/category checks | Surface-specific approval and fail-closed rejection | Hard-coded data, legacy SKU/material mismatch, no family projection, no durable approval records | Cannot support 300 variants or multiple editors |
| Route ownership | Products, categories, collections, search, cart, checkout, account remain denied | Strong ingress control and explicit prerequisites | Activation requires a new shared eligibility runtime and route transition process | Ad hoc route release could bypass governance |
| Publication | Static lifecycle/readiness/quality/sellability contracts | Correct separation of concerns and withdrawal doctrine | Most contracts are not runtime services; no aggregate state machine or persistence | Conflicting booleans and partial activation |
| Import/rollback | Draft seeder has dry-run, seed, audit, guarded delete rollback; staging validator exists | Deterministic, batch-tagged, collision-aware | Seeder is one bespoke source; direct DAL writes; rollback is deletion-only; no update merge or operation ledger | Unsafe re-import and rollback after human edits |

## 1.3 Existing homepage and preview rails

The preview currently supplies:

- New Arrivals: exact string match in JSON `veylune_rail_candidates`, max 16;
- Founder Selection: collection property, max 10;
- Living Room: room property, max 12.

Ordering is product number order. There is no supplier diversity, family
deduplication, variant suppression, eligibility state, curation override,
underfill strategy, or stable editorial position.

---

# 2. Supplier-Ready Data Model

## 2.1 New owned entities

Implement these as Shopware DAL custom entities/tables in the Veylune plugin.
Use UUID primary keys, `created_at`, `updated_at`, optimistic version/revision
fields, and explicit foreign keys.

| Entity | Purpose | Key fields |
| --- | --- | --- |
| `veylune_supplier` | Legal and operational supplier master | `supplier_code`, legal/display names, country, VAT/tax IDs, status, source profile, default currency, timezone, contract status, suspension reason |
| `veylune_supplier_brand` | Supplier-to-public-brand mapping | supplier FK, manufacturer FK, brand code, legal owner, display permission, active dates |
| `veylune_supplier_contact` | Role-specific contacts | supplier FK, role, name, email, phone, locale, active |
| `veylune_supplier_contract` | Commercial authority | supplier FK, status, effective/expiry dates, territories, currency, Incoterm, payment terms, returns policy, warranty baseline, signed artifact FK |
| `veylune_supplier_product_mapping` | Durable source identity | supplier FK, supplier SKU, manufacturer SKU, Shopware product FK, family source key, source revision, first/last seen |
| `veylune_import_batch` | Immutable intake and execution header | batch ID, supplier FK, source URI/hash, schema version, state, counts, actor, timestamps, parent batch, rollback snapshot FK |
| `veylune_import_operation` | Item/field-level write ledger | batch FK, entity, entity ID, operation, before hash/value reference, after hash/value reference, status, error code |
| `veylune_source_record` | Canonical normalized source payload | batch FK, supplier key, record type, canonical JSON, source hash, validation state |
| `veylune_asset` | Supplier asset master | supplier FK, source URL/key, checksum, MIME, dimensions, rights status/scope/expiry, asset state, Shopware media FK |
| `veylune_product_asset` | Product/family/variant media slot | product FK, asset FK, slot, locale, position, crop profile, alt text, approval state, inheritance mode |
| `veylune_compliance_record` | Evidence and expiry | supplier/product/family FK, type, jurisdiction, document asset FK, issuer, valid dates, status |
| `veylune_publication_approval` | Auditable gate decision | subject type/ID, gate, state, actor, decision time, reason, evidence hash, supersedes FK |
| `veylune_merchandising_assignment` | Explicit rail/collection curation | product-family FK, surface key, position, start/end, priority, approval, exclusion reason |
| `veylune_rollback_snapshot` | Restorable pre-commit state | batch FK, checkpoint, schema version, storage reference, hash, actor, created time |

## 2.2 Shopware native fields

| Concern | Native storage |
| --- | --- |
| Product family and variant | `product.parent_id`; parent is family, child is sellable variant |
| Veylune SKU | `product.product_number`; parent family number plus immutable child variant number |
| Public brand | `product.manufacturer_id`; backed by governed supplier-brand mapping |
| Name/description/SEO | Product translations |
| Price | Variant `price`; advanced prices only for Rule Builder/quantity/customer rules |
| Tax | `tax_id` on sellable variant, inherited where valid |
| Stock | Variant stock or approved external stock policy projection |
| Delivery time | Native `delivery_time_id` for customer-facing range |
| Dimensions/weight | Native width, height, length, weight where semantics match |
| Sales-channel exposure | Native product visibility, written only by publication service |
| Media gallery/cover | Native media and product-media associations, created from approved assets |
| Canonical product route | Native SEO URL generation after publication eligibility |
| Category assignment | Stable department/product-type navigation categories |
| Variant options | Native configurator option associations |
| Descriptive facets | Native properties |

## 2.3 Product properties/options

Use separate groups even where labels overlap:

- `Material` descriptive;
- `Material option` configurator;
- `Finish` descriptive/configurator;
- `Color` descriptive/configurator;
- `Size` configurator, with normalized machine key and translated label;
- `Room`, `Style`, `Outdoor suitability`, `Lighting type`, `Textile type`;
- optional bounded `Availability class` for discovery, derived from commerce
  data rather than supplier prose.

An option may be configurator-defining only when its change can select a
different child SKU. Never attach every descriptive property as a variant axis.

## 2.4 Product custom fields

Keep fields scalar, bounded, and indexed only when operationally necessary:

```text
veylune_family_code
veylune_supplier_id_projection
veylune_source_batch_id
veylune_publication_state
veylune_readiness_level
veylune_asset_status
veylune_content_status
veylune_commerce_status
veylune_governance_status
veylune_preview_status
veylune_sellability_status
veylune_stock_policy
veylune_delivery_class
veylune_return_class
veylune_warranty_class
veylune_compliance_status
veylune_media_rights_status
veylune_consultation_mode
veylune_listing_mode
veylune_default_variant_id
veylune_outdoor_suitability
veylune_public_eligibility
veylune_eligibility_reason_code
```

Do not store contacts, contracts, asset arrays, approval history, compliance
documents, or import operation history in product custom fields.

## 2.5 Categories, product groups, source manifests, media metadata

| Storage | Correct responsibility |
| --- | --- |
| Categories | Department and durable product-type navigation; optional governed subcategory only with assortment depth |
| Dynamic product groups | Eligibility candidates for rails, landing pages, cross-selling, internal queues; never sole publication authority |
| External canonical manifest | Supplier source facts, source revisions, aliases, family/variant matrix, asset references, translations, compliance references |
| Media metadata | Rights, source/checksum, slot, crop, alt text, locale, asset approval, focal point, rendition status |
| Custom entities | Supplier, contract, mapping, batch, operation, asset, compliance, approval, merchandising, rollback history |

---

# 3. Product Family and Variant Architecture

## 3.1 Family aggregate

For `Calma Dining Table`:

```text
Parent family: VLS-DIN-000001
Axes:
  size: 160, 180, 220, 260
  material: travertine, oak, walnut
  finish: natural, smoked, dark
Valid combinations are supplier-declared, not a blind 4 x 3 x 3 Cartesian set.
Children:
  VLS-DIN-000001-001 ...
```

Change the variant suffix contract from two digits to three digits before real
onboarding. Existing two-digit reservations remain valid, but new families use
`-{3 digits}`. Sequence numbers are stable and carry no option meaning; option
codes remain separate data. This prevents SKU renaming when labels or option
order change.

Parent rules:

- owns family code, generic story, shared categories/properties, default CMS
  layout, shared media, brand, and family-level approvals;
- has no stock, cannot be added to cart, and is not independently sellable;
- may be the listing projection when `listing_mode=family`;
- references one approved default/main variant.

Variant rules:

- owns product number, supplier SKU mapping, EAN/manufacturer number, price,
  stock policy, delivery, package/dimensions where different, option set, and
  variant-specific media;
- inherits only facts proven identical across the family;
- can be suspended independently;
- cannot publish if its parent family is not eligible.

## 3.2 Naming, image, price, visibility, and listing rules

| Concern | Rule |
| --- | --- |
| Public family name | Stable editorial name, e.g. `Calma Dining Table` |
| Variant display name | Family name plus only disambiguating options; selectors remain primary presentation |
| Admin name | Family name plus full option tuple and supplier SKU |
| Variant SKU | Immutable sequential suffix; never derived from translated option labels |
| Images | Parent supplies common story; child supplies option-accurate cover and material/finish images; no misleading inherited cover |
| Price | Child price is authoritative; parent listing displays approved default or `from` price calculated from eligible children |
| Visibility | Publication service writes visibility only for eligible projection products/children |
| Canonical URL | Family canonical by default; variant URL/query selection only where variant has materially distinct public identity |
| Listing fan-out | Explicit per family and per axis; never fan out all axes by default |

Shopware supports a main-product/single-variant listing mode and fan-out by
selected configurator properties. Veylune must govern those settings from
`veylune_listing_mode` and approved fan-out axes.

## 3.3 Department rules

| Department | Default listing | Fan-out permitted | PDP-only axes |
| --- | --- | --- | --- |
| Furniture | One family card | Material only when silhouette/price/imagery materially changes; modular configuration as separate family when topology changes | Size, finish, upholstery color |
| Lighting | One family card | Fixture type or materially different finish only; multi-light count may fan out if a distinct installation product | Color temperature, cable length, minor finish |
| Decor | Family card; fan-out more acceptable for visually distinct colorways | Color/material where each is an independent styled object | Minor size where image is unchanged |
| Textiles | Family card, optionally color fan-out where color is the primary discovery intent | Color/pattern; rug size only for category-specific merchandising | Most sizes remain selector choices |
| Dining | One family card | Material or table shape; size only when use case changes substantially | Standard size and finish combinations |
| Outdoor | One family card | Product type/material where weather behavior and imagery differ | Size, cushion color, minor finish |

Hard limit: a family must not expose more than one card per approved fan-out
value, and never more than six cards in one listing result. Search defaults to
family deduplication.

---

# 4. Import Architecture

## 4.1 Pipeline

```text
Source adapter
-> immutable raw receipt
-> schema parse
-> supplier-profile mapping
-> canonical manifest
-> semantic validation
-> identity/deduplication
-> family/variant expansion plan
-> media/compliance preflight
-> dry-run diff
-> approval
-> chunked Sync API application
-> index/SEO deferred jobs
-> post-commit audit
-> publication remains unchanged
```

Supported adapters are:

- manual Veylune CSV using the canonical column template;
- supplier CSV using a versioned supplier mapping profile;
- supplier API pull or webhook receipt into immutable raw storage;
- controlled Admin API submissions for backend integrations;
- Sync API execution for planned bulk entity operations;
- media URL/archive adapters that remain subordinate to the asset pipeline.

The Admin API is the authenticated backend integration boundary. Use the Sync
API endpoint for chunked bulk writes with deterministic IDs and explicit
operation keys. Do not expose either API through canonical public ingress.

## 4.2 Canonical input schema

Top level:

```text
schema_version, batch_id, supplier_code, source_reference, source_hash,
received_at, source_revision, locale_set, currency, mode, items, assets,
compliance, rollback_target
```

Family:

```text
supplier_family_key, veylune_family_code?, brand_code, names, descriptions,
taxonomy, descriptive_properties, configurator_axes, shared_dimensions,
shared_commerce, shared_assets, publication_intent=none
```

Variant:

```text
supplier_sku, manufacturer_sku, ean?, veylune_sku?, option_values,
price_inputs, tax_code, stock_policy, stock, lead_time, delivery_class,
return_class, warranty, dimensions, package, assets, compliance_refs,
source_updated_at, discontinued
```

## 4.3 Validation stages

1. **Transport:** file readability, encoding, delimiter, MIME, API signature.
2. **Schema:** required fields, types, locale and currency formats.
3. **Supplier:** active contract, allowed territory, source profile, contact.
4. **Identity:** supplier SKU uniqueness, SKU reservation, family mapping,
   retired SKU check, duplicate EAN/manufacturer SKU warning.
5. **Taxonomy:** only approved canonical mappings; unknown values quarantine.
6. **Variant:** complete unique option tuple, valid combinations, no empty child,
   no duplicate tuple, bounded variant count.
7. **Commerce:** valid price/tax/currency, stock semantics, lead time, delivery,
   returns and warranty classes.
8. **Media/compliance:** reachable assets, checksum, rights, required documents,
   expiry, slot minimum.
9. **Diff safety:** classify create/update/no-op/suspend/archive/conflict.
10. **Publication safety:** planned writes may not set published, visibility,
    active public exposure, SEO, or search eligibility.

## 4.4 Idempotency, updates, conflicts, and rollback

- Idempotency key: `supplier_id + source_record_key + source_revision`.
- Record hash makes unchanged re-import a no-op.
- Supplier-owned fields may update through the source profile.
- Veylune-owned fields are never overwritten by supplier input.
- Human overrides require an ownership marker and produce a conflict, not a
  last-write-wins update.
- Missing variants are not deleted. They become `source_missing`, then require
  an explicit suspend/archive decision.
- Commit uses chunks of 25-100 aggregate writes, depending on media payload.
- Every operation records before/after hashes and resulting IDs.
- Rollback creates compensating writes from the snapshot. Delete is allowed
  only for never-published entities created solely by that batch.
- Rollback refuses when a later batch or human approval supersedes a field,
  unless an authorized force plan identifies each conflict.

## 4.5 Commands

```text
veylune:supplier:validate <supplier>
veylune:supplier:import:receive <source>
veylune:supplier:import:dry-run <batch>
veylune:supplier:import:approve <batch>
veylune:supplier:import:commit <batch>
veylune:supplier:import:resume <batch>
veylune:supplier:import:audit <batch>
veylune:supplier:import:rollback <batch>
veylune:supplier:media:sync <batch|supplier>
veylune:supplier:media:audit <batch|supplier>
veylune:catalog:variants:audit <family>
veylune:catalog:eligibility:rebuild <scope>
```

Dry-run output must include totals, deterministic IDs, field ownership,
create/update/no-op/suspend counts, family and variant counts, unknown values,
conflicts, media work, index work, and exact rollback scope. Machine-readable
JSON and human-readable table outputs are required.

---

# 5. Media and Asset Pipeline

## 5.1 Slots

Slots are `hero`, `studio_cutout`, `lifestyle`, `detail`, `material`,
`scale`, `variant`, `room_context`, `dimensions`, and optional `video`.

| Package | Requirement |
| --- | --- |
| Minimum intake | Hero, studio/cutout, detail, material, scale; source and rights metadata |
| Premium | Minimum plus lifestyle, room context, second detail, dimensions, variant-accurate media |
| Publication | At least five approved images; hero/cover, detail, material, scale/context; every publicly selectable visual option accurately represented |

## 5.2 Asset lifecycle

`received -> scanned -> normalized -> rights_verified -> qa_review ->
approved -> published`; alternative terminal states are `rejected`, `expired`,
and `withdrawn`.

Pipeline requirements:

- stream download with timeout, size and MIME allowlist;
- malware scan before media persistence;
- SHA-256 deduplication per binary, without collapsing distinct rights records;
- preserve original; generate Shopware thumbnails/renditions asynchronously;
- store width, height, aspect ratio, color profile, file size and focal point;
- use deterministic file naming independent of public product name;
- apply slot-specific crop profiles rather than one crop across all images;
- alt text is localized, factual, product-specific, and excludes keyword
  stuffing;
- rights require owner, grant source, territories, channels, start/expiry,
  modification permission and evidence asset;
- rights expiry or withdrawal suspends affected public media and can suspend the
  product when no compliant package remains.

Reject media for absent/ambiguous rights, watermarks, supplier branding not
approved for public display, insufficient resolution, misleading variant
color/material, visible damage, severe compression, duplicate slots, unsafe
electrical/installation depiction, or unsupported factual claims.

Responsive delivery uses Shopware media thumbnails and `srcset`; publication
QA must test card, gallery, zoom, mobile, and high-density renditions. A
placeholder may exist only in private preview and must block publication.

---

# 6. PDP Architecture Readiness

| Section | Required data/source | Fallback | Blocker | Preview |
| --- | --- | --- | --- | --- |
| Hero gallery | Approved `product_asset` slots projected to native media | Shared parent media only when accurate | Yes | Show missing-slot reasons |
| Name/price/status | Translation; eligible variant price; availability projection | `From` price when family mode | Yes | Label target/unapproved prices |
| Variant selector | Native configurator settings and eligible child matrix | Hide absent axis | Yes if family has variants | Include disabled invalid combinations |
| Material selector | Configurator material group plus material evidence/story | Descriptive material when not configurable | Yes when purchasable axis | Show evidence status |
| Size selector | Configurator size and dimensions | None | Yes when required to buy | Show normalized and display values |
| Product story | Approved translated family content | Concise factual description | Yes | Draft watermark/state |
| Material story | Approved product-story field plus canonical properties | Hide optional editorial story | No, if facts remain complete | Show draft/evidence |
| Dimensions | Native dimensions plus structured dimension set/diagram | Text table | Yes | Identify inherited values |
| Delivery/lead time | Native delivery range plus delivery class/source timestamp | Consultation only when approved policy permits | Yes | Never claim availability |
| Care | Approved localized care guidance | None | Yes for relevant materials | Draft state |
| Supplier/provenance | Manufacturer plus approved provenance policy | Brand only | No unless provenance claim is made | Never expose legal contacts |
| Related products | Approved dynamic candidate set plus curation | Hide section | No | Include rejection reasons |
| Room compatibility | Approved room assignments | Hide section | No | Show candidate vs approved |
| Consultation CTA | Governed consultation mode and reason | Hide | No | Always non-transactional |
| Compliance/safety | Applicable approved compliance records | None | Yes where legally/operationally required | Show expiry and jurisdiction |

The PDP loader must receive one `ProductCommerceView` contract containing
family, selected variant, option matrix, approved assets, delivery, compliance,
eligibility and reason codes. Twig must not reconstruct governance from raw
custom fields.

---

# 7. Listing, Rail, and Homepage Architecture

## 7.1 Population model

Use three layers:

1. **Eligibility projection:** Level 3/public eligible, rights valid, family has
   an eligible child, surface approval valid.
2. **Dynamic product group:** category/room/newness/brand/material rules form
   candidates.
3. **Merchandising assignment:** optional pin, exclude, rank, start/end and
   editorial approval.

Dynamic product groups are rule-derived Shopware product streams and can fill
categories and Shopping Experience product sliders. They are candidate
selection, not governance authority.

## 7.2 Homepage rail contract

| Rail/block | Source | Min / max | Primary ordering |
| --- | --- | ---: | --- |
| Product family navigation | Published department/type categories | 6 / 8 | Taxonomy position |
| Two hero products | Explicit curation only | 2 / 2 | Position |
| New Arrivals | Dynamic eligibility plus release window | 6 / 12 | Published-at desc, then curation |
| Founder Selection | Explicit founder approval plus eligibility | 4 / 10 | Founder position |
| Living Room | Room-approved dynamic group | 6 / 12 | Curation, relevance, freshness |
| Dining Room | Room-approved dynamic group | 6 / 12 | Same |
| Lighting | Department/type dynamic group | 6 / 12 | Same |
| Outdoor | Department plus outdoor suitability | 4 / 10 | Same |
| Brand blocks | CMS/editorial, optionally brand-specific eligible families | 1 / 1 | Explicit |
| Consultation / Trade | CMS/acquisition content | 1 / 1 | Explicit |

Rules:

- one card per family unless a rail explicitly authorizes fan-out;
- no family appears twice on the homepage unless one occurrence is a hero;
- hero families are excluded from the next two product rails;
- no supplier may exceed 40% of a rail when at least three suppliers have
  eligible stock;
- preserve at least three suppliers in rails of eight or more when available;
- explicit pins still must pass eligibility;
- stable sort uses curation position, merchandising score, family code;
- underfilled rails pull from a documented fallback group in the same customer
  intent; below minimum after fallback, hide the rail;
- never fill with suspended, duplicate, draft, source-missing, or placeholder
  products.

---

# 8. Search, Filter, and Discovery Architecture

## 8.1 Facets

| Facet | Public | Storage |
| --- | --- | --- |
| Department | Yes | Category |
| Product type | Yes | Category / governed projection |
| Room | Yes after coverage threshold | Property |
| Material | Yes | Property |
| Finish | Yes where relevant | Property |
| Color | Yes for textiles/decor; conditional elsewhere | Property |
| Size | Category-specific | Configurator/property normalized values |
| Price | Yes | Eligible variant calculated price |
| Availability | Yes, coarse classes only | Derived projection |
| Supplier/brand | Brand public; legal supplier internal | Manufacturer / supplier entity |
| Collection | Public for approved commercial collections | Governed assignment/product group |
| Outdoor suitability | Yes in relevant departments | Property |
| Lighting type | Yes in Lighting | Property |
| Textile type | Yes in Textiles | Property |

Internal-only filters include supplier legal entity, contract state, source
batch, import state, rights expiry, compliance status, readiness blockers,
margin/cost, and governance owner.

## 8.2 Activation prerequisites

Search remains denied until:

- the eligibility projection is the sole indexing admission source;
- every indexed result resolves to an eligible PDP;
- family deduplication and approved fan-out are implemented;
- EN/DE names, SEO fields and search terms are complete;
- withdrawal removes results within the defined SLA;
- zero-result, typo, synonym, sorting, pagination and filter tests pass;
- demo products and draft batch products are excluded.

Filters require published dictionary state, translated labels, category
relevance, minimum coverage, bounded option count, and performance tests.
Suggested thresholds: expose a facet only when it applies to at least 20% of
the category and each shown option returns at least two families, except
high-intent size options.

Default sorting is curated relevance. Additional sorts: newest, price low-high,
price high-low. Popularity requires sufficient clean behavioral data. Search
performance risks are variant fan-out, high-cardinality properties, deep
associations, per-result eligibility checks, and synchronous indexing after
large imports. Eligibility must be denormalized/indexable; do not execute the
full gate graph per card.

---

# 9. Governance and Publication Gates

## 9.1 State machine

Required states:

```text
imported -> validated -> asset_complete -> content_complete ->
commerce_complete -> governance_approved -> preview_approved -> published
published -> suspended -> governance_approved|published
any non-terminal -> archived
archived is terminal
```

States represent the highest completed cumulative gate. Domain statuses remain
separate so a regression can identify the exact blocker.

| Gate | Required checks | Owner | Blocking conditions | Rollback |
| --- | --- | --- | --- | --- |
| Imported | Batch committed, mappings persisted, operation audit clean | Import governance | Partial/failed commit, unknown identity | Compensating batch |
| Validated | Schema, supplier, SKU, taxonomy, variant, source hash valid | Data governance | Any error; warnings explicitly accepted | Return to imported |
| Asset complete | Required slots, rights, QA, renditions, alt text | Media governance | Missing/expired/rejected asset | Remove asset projection |
| Content complete | EN/DE copy, dimensions, care, SEO, factual review | Content governance | Missing locale/factual conflict | Revoke content approval |
| Commerce complete | Price, tax, stock policy, lead time, delivery, return, warranty, compliance | Commerce governance | Unknown or expired commercial fact | Set not sellable |
| Governance approved | Active supplier/contract, quality gate, rollback target, no unresolved conflicts | Product governance | Any domain not approved | Revoke approval |
| Preview approved | PDP/listing/selector/media/locale/runtime QA | Experience owner + product governance | Broken combination, route, claim or rendering | Revoke preview |
| Published | Explicit publish command writes active/visibility/SEO/index projection atomically enough to fail closed | Publication authority | Any gate false | Immediate suspend |
| Suspended | Public eligibility false everywhere; reason and actor recorded | Product governance | None; fail closed | Re-approval required |
| Archived | Historical retention, mappings/redirect review, no commerce | Product + SKU governance | Existing open-order obligations unresolved | No return |

The gate engine emits immutable decisions and one denormalized
`public_eligibility` projection. A failed prerequisite automatically invalidates
downstream states; it never automatically republishes after recovery.

---

# 10. Scalability Risk Analysis

| Scale | Expected shape | Primary risks | Required controls |
| --- | --- | --- | --- |
| 50 families / 300 variants | Pilot | Manual approval load, media gaps, source inconsistency, early model churn | Complete target model, batch import, exception queue, family projection |
| 100 families / 1000 variants | Operating catalog | Admin variant editing, indexing bursts, rail duplication, 5k+ assets, supplier update conflicts | Async media/indexing, chunked Sync API, dashboards, deterministic re-import |
| 250 families / 3000 variants | Growth | Variant query cost, 15k-30k assets, cache churn, high-cardinality filters, approval aging | Search engine capacity tests, denormalized eligibility, CDN/object storage, SLA queues |

Specific controls:

- **Performance:** avoid loading all children/media on listings; fetch family
  projections and selected cover only.
- **Variant explosion:** supplier-declared valid combinations, per-family hard
  warning at 100 and architecture review above 250 children.
- **Media:** originals outside application filesystem where possible; CDN,
  asynchronous thumbnails, checksum dedupe, lifecycle cleanup.
- **Search:** batch indexing after commit; suspend projection before delete;
  family-level result documents unless fan-out approved.
- **Cache:** tag invalidation by affected family/surface, not global storefront
  clears per item.
- **Admin:** supplier/batch/family dashboards and exception queues; do not rely
  on opening 1000 native product forms.
- **Import runtime:** resumable chunks, backpressure, rate limits, job metrics,
  no single transaction around all products/media.
- **Governance:** risk-based review for known mappings; 100% review for new
  vocabulary, compliance, publication and founder selection.

---

# 11. Required Implementation Program

## WP-COM-ARCH-02 - Supplier Data Model

**Goal:** Persist real supplier, brand, contacts, contracts and source identity.
**Likely files:** new `src/Supplier/*`, DAL definitions/entities/collections,
migrations, repositories, admin/API services, validation commands; replace
`Resources/config/staging_registries.php` as authority.
**Entities:** supplier, brand, contact, contract, product mapping.
**Risk:** accidental exposure of legal/commercial data; duplicate supplier
identity.
**Acceptance:** FK-backed model; status transitions; unique supplier code and
supplier-SKU pair; contract expiry/suspension blocks import; audit trail; mock
registry no longer authoritative.
**Rollback:** migration down/forward compensation defined; no product mappings
or imports depend on removed supplier rows.

## WP-COM-ARCH-03 - Variant Architecture Foundation

**Goal:** Establish native family parents, sellable children, controlled axes,
SKU reservation and listing policy.
**Likely files:** `src/Catalog/*`, migrations, variant planner, SKU registry,
admin projections, audits; update draft manifest/seeder only in a separately
authorized migration plan.
**Entities:** product parent/child, configurator settings/options, SKU
reservation, supplier mapping.
**Risk:** Cartesian variant creation, wrong inheritance, URL/listing changes.
**Acceptance:** Calma reference family imports with only valid combinations;
unique option tuples; immutable SKUs; parent non-sellable; variant-specific
price/media; family and fan-out listing tests.
**Rollback:** remove never-published child set and restore parent snapshot;
published family changes require compensating migration and redirect review.

## WP-COM-ARCH-04 - Supplier Import Pipeline

**Goal:** One canonical, dry-run-first, idempotent CSV/API to Sync API pipeline.
**Likely files:** `src/Import/*`, commands, schema files, adapters, queues,
reports, batch/operation migrations, service configuration.
**Entities:** import batch, source record, operation, rollback snapshot.
**Risk:** partial writes, ownership overwrite, duplicate products.
**Acceptance:** receive/validate/dry-run/approve/commit/resume/audit/rollback;
repeat commit is no-op; injected failure resumes; conflicts are explicit; no
commit changes publication.
**Rollback:** verified compensating plan restores pre-batch hashes and refuses
superseded fields.

## WP-COM-ARCH-05 - Media & Asset Pipeline

**Goal:** Rights-safe supplier asset intake and product slot projection.
**Likely files:** `src/Media/*`, message handlers, migrations, media commands,
thumbnail/crop configuration, QA reports.
**Entities:** asset, product asset, native media/product media.
**Risk:** rights breach, storage growth, misleading variant images.
**Acceptance:** checksum dedupe, scan, metadata, rights evidence, slot/crop/alt
validation, async rendition, withdrawal behavior, five-image publication gate.
**Rollback:** detach batch projections; delete binaries only after complete
reference scan; retain rights and operation history.

## WP-COM-ARCH-06 - PDP Data Contract

**Goal:** Provide one governed family/variant commerce view to preview and
future public PDP.
**Likely files:** new `src/ProductDetail/*`, Store API/page loaders, structs,
Twig contracts, preview service/controller tests, product-story integration.
**Entities:** product, options, assets, compliance, eligibility projection.
**Risk:** raw custom-field interpretation and inconsistent variant selection.
**Acceptance:** every required PDP section has typed data/fallback/blocker;
invalid combinations disabled; EN/DE parity; suspended direct access denied.
**Rollback:** keep public product route pending; remove loader integration
without changing product records.

## WP-COM-ARCH-07 - Rail Population Engine

**Goal:** Dynamic eligibility plus controlled curation for all approved homepage
rails.
**Likely files:** replace `ProductExposureService`, add merchandising entities,
product-stream definitions, homepage page subscriber/view model, audit command.
**Entities:** product streams, merchandising assignments, family projection.
**Risk:** duplicates, supplier dominance, stale rails, variant flooding.
**Acceptance:** min/max, dedupe, diversity, underfill, expiry, stable ordering,
and eligibility tests for every rail.
**Rollback:** disable new population service and retain static public homepage;
no publication state changes.

## WP-COM-ARCH-08 - Search & Filter Readiness

**Goal:** Family-aware indexing, governed facets, sorting and withdrawal.
**Likely files:** search subscribers/indexers, listing criteria/result
decorators, facet registry, product sorting config, audits, route policy only
after authorization.
**Entities:** search keywords/index, properties, categories, eligibility
projection.
**Risk:** draft leakage, fan-out duplication, slow aggregations.
**Acceptance:** only eligible results; every result has valid PDP; family
dedupe; approved fan-out; category-specific facets; suspend-to-removal SLA;
load tests at 3000 variants.
**Rollback:** route returns to `activation_pending`; clear governed search
projection/index; PDP/publication remains independent.

## WP-COM-ARCH-09 - Publication Gate Engine

**Goal:** Make existing governance contracts executable and authoritative for
all commerce surfaces.
**Likely files:** consolidate lifecycle/readiness/quality/publication contracts
under `src/Governance/*`, approval entities/migrations, eligibility projector,
subscribers for PDP/listing/search/cart/sitemap, commands and audits.
**Entities:** publication approvals, product projections, visibility, SEO URLs,
search index.
**Risk:** split-brain state, partial publish, automatic republish.
**Acceptance:** all gates persisted with actor/evidence; one eligibility
decision consumed everywhere; explicit publish; immediate suspend; no automatic
republish; route and sitemap leakage suites pass.
**Rollback:** suspend affected scope first, restore previous projection and
route policy, retain approval history.

## 11.1 Required order

```text
02 Supplier model
-> 03 Variant foundation
-> 04 Import pipeline
-> 05 Media pipeline
-> 09 Gate engine core
-> 06 PDP contract
-> 07 Rail engine
-> 08 Search/filter readiness
-> separately authorized public route activation
```

WP-COM-ARCH-09 begins before PDP/rails/search integration because those
surfaces require its eligibility projection; final integration completes after
their contracts exist.

---

# 12. Final Verdict and Launch Thresholds

## 12.1 Verdict

**Not ready.**

Veylune is ready to preserve private draft concepts and validate parts of a
future supplier manifest. It is not ready to onboard a real supplier into a
durable, updateable, variant-aware, media-complete, commerce-capable operating
model.

## 12.2 Minimum required before first supplier

- WP-COM-ARCH-02 complete;
- canonical supplier intake schema and ownership matrix;
- supplier/contract/contact/source profile persisted;
- SKU reservation and supplier mapping persistence from WP-COM-ARCH-03;
- WP-COM-ARCH-04 receive, validate and dry-run operational;
- no production commit required yet.

## 12.3 Minimum required before first public product

- WP-COM-ARCH-02 through 06 and publication core of 09 complete;
- one family with valid native variants;
- approved publication media package;
- complete EN/DE PDP contract;
- price, tax, stock policy, lead time, delivery, returns, warranty and required
  compliance approved;
- explicit publish/suspend path;
- product route mediated by the common eligibility projection;
- cart admission and checkout behavior tested if the product is sellable.

## 12.4 Minimum required before public marketing

- at least ten Level 3 eligible families across credible departments;
- category/listing and homepage rail engine complete;
- no placeholder assets or supplier claims;
- withdrawal, underfill, dedupe and supplier-diversity tests pass;
- sitemap/canonical/EN-DE checks pass;
- customer support, delivery and returns ownership documented.

## 12.5 Minimum required before paid advertising

- end-to-end purchase, payment, tax, fulfillment, cancellation, refund and
  support tests pass for advertised products;
- campaign inventory/lead-time refresh SLA and automatic suspension exist;
- landing/PDP URLs are stable and monitored;
- asset rights cover paid media territories/channels and campaign dates;
- feed/search indexing contains only eligible variants/families;
- rollback and incident runbooks have been exercised;
- no campaign launches from supplier source data without Veylune publication
  approval.

---

# Appendix A - Architecture Decisions

| ID | Decision |
| --- | --- |
| ADR-01 | Native Shopware parent/child products are the family/variant commerce model |
| ADR-02 | Supplier and workflow history use dedicated DAL entities, not JSON custom fields |
| ADR-03 | Categories remain stable navigation; rooms/collections/rails use governed relationships and product groups |
| ADR-04 | Variant axes are limited to SKU-defining choices; descriptive properties do not generate children |
| ADR-05 | Family listing is default; fan-out is explicit and bounded |
| ADR-06 | All source adapters normalize to one versioned canonical manifest |
| ADR-07 | Sync API is the bulk write mechanism behind a Veylune import control plane |
| ADR-08 | Publication is an explicit command over a persisted gate graph |
| ADR-09 | One denormalized eligibility projection feeds PDP, listing, search, rail, sitemap, cart and checkout |
| ADR-10 | Rollback uses snapshots and compensating operations; deletion is restricted to never-published batch-owned entities |

# Appendix B - Shopware 6.7 Capability Basis

- Shopware product creation, properties, variant generation, variant-specific
  images/inheritance, listing main-variant modes, fan-out, visibility, SEO and
  canonical variant behavior:
  <https://docs.shopware.com/en/shopware-6-en/catalogues/products>
- Dynamic product groups are rule-derived candidate sets and can populate
  categories and Shopping Experience product sliders:
  <https://docs.shopware.com/en/shopware-6-de/Catalogues/Dynamicproductgroups?category=shopware-6-en%2Fcatalogues>
- Categories support manual or dynamic-product-group product assignment and
  remain the navigation/content tree:
  <https://docs.shopware.com/en/shopware-6-en/catalogues/categories>
- Installed Shopware 6.7.10.0 source confirms the Admin Sync endpoint at
  `/api/_action/sync`, product-stream filtering/indexing, variant listing
  configuration, SEO URL updating, product search keyword indexing, and native
  visibility entities.

# Appendix C - Current Repository Authorities Reviewed

Primary implementation:

- `DraftCatalogManifest`
- `DraftCatalogSeeder`
- `DraftCatalogPreviewService`
- `DraftCatalogPreviewController`
- `DraftCatalogPreviewAccess`
- `ProductExposureService`
- `ProductExposurePageSubscriber`
- `IdentityIngressAllowlistSubscriber`
- `StorefrontRouteOwnershipPolicy`
- catalog supplier, SKU, batch, readiness, media, taxonomy, publication,
  sellability and rollback contracts

Primary documents:

- WP-CAT-03 and WP-CAT-04
- WP-DES-CAT-01, 02, 03a and 03b
- storefront commerce UX architecture phase 1
- WP-COM-01 and WP-COM-02
- product readiness and attribution program
- WP-06 public storefront boundary
- WP-07 supplier, SKU, staging, taxonomy, media, publication and rollback
  foundations

CAT-01 and CAT-02 were referenced by CAT-04 but were not present as repository
documents at assessment time. Their intended authority was reconstructed only
from CAT-04, the implemented manifest, and later governance documents; they
must be restored or formally superseded before implementation begins.
