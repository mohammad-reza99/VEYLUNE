# WP-07 Production Taxonomy and Property Dictionary Governance

## Phase 3 Scope

WP-07 Phase 3 defines the production taxonomy and property-dictionary
foundation required for a public catalog that can scale from 100 to 1500
products. It adds static contracts and documentation only.

This phase does not restructure categories, clean property records, import
suppliers or products, publish catalog records, activate categories, enable
search, create product streams, or change storefront behavior.

## Department Contract

`DepartmentContract` defines the stable top-level shopping departments:

| Canonical key | EN label | DE label |
| --- | --- | --- |
| `furniture` | Furniture | Moebel |
| `lighting` | Lighting | Leuchten |
| `decor_objects` | Decor & Objects | Dekor & Objekte |
| `textiles_rugs` | Textiles & Rugs | Textilien & Teppiche |
| `dining_kitchen` | Dining & Kitchen | Essen & Kueche |
| `outdoor` | Outdoor | Outdoor |

These department keys are stable for the 100, 500, and 1500 product targets.
Catalog growth occurs below departments. Adding, renaming, merging, or retiring
a department is an architecture decision owned by `taxonomy_governance`, not a
supplier-import decision.

## Category Governance Contract

`CategoryGovernanceContract` assigns categories to `taxonomy_governance`.
Category lifecycle is independent from the Shopware `active` flag:

| State | Publicly eligible | Purpose |
| --- | --- | --- |
| `draft` | No | Proposed taxonomy node |
| `review` | No | Governance and customer-intent review |
| `approved` | No | Accepted structure awaiting explicit publication |
| `published` | Yes | Explicitly authorized category |
| `retired` | No | Terminal historical mapping |

Category expansion requires a canonical department, distinct customer intent,
EN/DE labels and SEO metadata, sustained assortment depth for subcategories,
and taxonomy-governance approval. Supplier data cannot create public taxonomy.
Collections remain separate from the stable shopping tree.

Retirement requires product reclassification, canonical URL impact review, and
historical mapping retention for rollback.

## Property Dictionary Contract

`PropertyDictionaryGovernanceContract` defines six canonical dictionaries:

| Dictionary | Owner | Value source | Public-facet candidate |
| --- | --- | --- | --- |
| `material` | `property_dictionary_governance` | Controlled vocabulary | Yes |
| `finish` | `property_dictionary_governance` | Controlled vocabulary | Yes |
| `color` | `property_dictionary_governance` | Controlled vocabulary | Yes |
| `room` | `property_dictionary_governance` | Controlled vocabulary | Yes |
| `style` | `property_dictionary_governance` | Controlled vocabulary | Yes |
| `collection` | `collection_governance` | Governed collection registry | Yes |

All are multi-value dictionaries. Supplier values must map into canonical
values. Unmapped values fail closed and enter review; they never create new
public properties automatically.

## Controlled Vocabulary Contract

`ControlledVocabularyContract` provides deterministic canonical keys and EN/DE
label parity for material, finish, color, room, and style. Collection values are
registry-owned and therefore intentionally not hard-coded into the dictionary.

Normalization rules:

1. Trim supplier input.
2. Normalize ASCII case and separators.
3. Apply explicit supplier aliases only.
4. Accept the result only when it matches a canonical key.
5. Reject unmapped input into review.
6. Require non-empty EN and DE labels before future public eligibility.

Initial aliases are intentionally narrow, such as `gray` -> `grey`,
`off white` -> `warm_neutral`, and `office` -> `home_office`. Vocabulary growth
requires dictionary-governance review.

## Facet Governance Contract

`FacetGovernanceContract` assigns public-filter decisions to
`facet_governance`. A dictionary being a facet candidate does not activate a
filter.

Facet lifecycle:

| State | Publicly eligible |
| --- | --- |
| `draft` | No |
| `review` | No |
| `approved` | No |
| `published` | Yes |
| `suspended` | No |
| `retired` | No |

Before future activation, every facet requires owner approval, published facet
state, a canonical dictionary source, bounded values, EN/DE parity, category
relevance, minimum result coverage, zero-result behavior review, and analytics
review before expansion.

This prevents uncontrolled facet growth from supplier feeds and sparse filters.

## Scalability Verification

| Catalog target | Taxonomy model | Required discipline | Breaking point |
| --- | --- | --- | --- |
| 100 products | Stable departments with selective categories | Avoid empty branches; use controlled values from first import | Broad or sparse categories reduce conversion |
| 500 products | Stable departments, governed categories, selective subcategories | Require category-depth review and facet coverage analysis | Flat navigation and unbounded supplier values stop scaling |
| 1500 products | Same departments, deeper governed categories, commercial collections, analytics-reviewed facets | Maintain URL governance, EN/DE parity, supplier mapping, and retirement history | Opportunistic taxonomy changes create SEO fragmentation and filter noise |

The department layer does not change across targets. Scale is handled through
governed category depth, collection merchandising, and bounded facets.

## Runtime Boundary

These contracts are not registered as services and are not consulted during
public requests. Existing WP-03 publication enforcement, WP-04 sitemap
containment, WP-05 mediated Edition retrieval, WP-06 activation-pending
storefront ownership, and WP-07 Phase 1 and Phase 2 behavior remain unchanged.

## Rollback

Phase 3 rollback is code and documentation only:

1. Remove the five Phase 3 `VeyluneTheme\Catalog` static contract files.
2. Remove this document.
3. Remove the WP-07 Phase 3 reference from `docs/veylune-architecture.md`.
4. Run the existing governance suite.

No database rollback is required because Phase 3 performs no database writes.
