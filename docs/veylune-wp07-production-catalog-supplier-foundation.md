# WP-07 Production Catalog & Supplier Foundation

## Phase 1 Scope

WP-07 Phase 1 creates the governing catalog contract required before production
catalog preparation. It adds static contract definitions and documentation only.
It does not import suppliers or products, restructure categories, activate
routes, publish catalog records, create product streams, or change storefront
behavior.

## Product Lifecycle Contract

`ProductLifecycleContract` defines a product-publication lifecycle separate from
the WP-03 identity-record publication policy.

| State | Publicly eligible | Rule |
| --- | --- | --- |
| `draft` | No | Work in progress |
| `review` | No | Awaiting governance review |
| `approved` | No | Approved but not explicitly published |
| `published` | Yes | Explicit product-publication state |
| `suspended` | No | Immediate fail-closed withdrawal |
| `archived` | No | Terminal retained product record |

Allowed transitions:

- `draft` -> `review`, `archived`
- `review` -> `draft`, `approved`, `archived`
- `approved` -> `draft`, `review`, `published`, `archived`
- `published` -> `suspended`, `archived`
- `suspended` -> `approved`, `published`, `archived`
- `archived` -> no transitions

Product lifecycle remains independent from Shopware activation, stock, and
visibility:

```text
Shopware active product
!=
Stock availability
!=
Sales-channel visibility
!=
Published product record
```

Only an explicitly `published` product record may become eligible for future
public-commerce exposure. This phase does not implement that future enforcement.

## Catalog Ownership Contract

`CatalogOwnershipContract` assigns one governing authority to each catalog
resource.

| Resource | Governing authority |
| --- | --- |
| Products | `product_governance` |
| Categories | `taxonomy_governance` |
| Collections | `collection_governance` |
| Properties | `property_dictionary_governance` |
| Media | `media_governance` |

## Supplier Ownership Contract

`SupplierOwnershipContract` separates source authority from Veylune storefront
authority.

Supplier-owned source data:

- supplier ID and supplier SKU
- source product facts
- source stock
- source cost and price input
- source lead time
- source media and usage rights
- compliance data

Veylune-owned canonical data:

- Veylune SKU and canonical product identity
- taxonomy and property-dictionary mapping
- public copy and SEO metadata
- publication state
- merchandising
- final pricing policy
- customer experience

Supplier input never implies publication.

## Structured Product Contract

`StructuredProductContract` defines facts by domain.

| Domain | Required facts |
| --- | --- |
| Identity | Veylune SKU, supplier ID, supplier SKU, manufacturer |
| Classification | department, category, product type, room |
| Materials | primary material, finish, color |
| Physical | width, height, depth, weight, assembly requirements |
| Commerce | price, tax, stock, sellability, lead time, delivery class, returns class |
| Content | EN/DE title, description, SEO metadata, care guidance |
| Media | primary image, detail image, EN/DE alt text, rights status |
| Governance | publication state, quality status, reviewer, source batch, rollback target |

Optional structured facts include EAN, subcategory, commercial collection,
editorial collection, style, secondary material, editorial story, context image,
and dimensions diagram.

Structured facts must remain separate from editorial prose. Existing Veylune
product-story fields remain enrichment only.

## Taxonomy Governance Contract

`TaxonomyGovernanceContract` separates stable shopping taxonomy from
merchandising and editorial context.

| Layer | Owner | Purpose |
| --- | --- | --- |
| Departments | `taxonomy_governance` | Stable shopping navigation |
| Categories | `taxonomy_governance` | Primary product classification |
| Subcategories | `taxonomy_governance` | Assortment depth when supported |
| Commercial collections | `collection_governance` | Conversion merchandising |
| Editorial collections | `editorial_governance` | Brand and discovery context |

Collections must not act as the parent container for the stable department tree.
Suppliers must not create public taxonomy directly.

## Runtime Boundary

These contracts are static definitions only. They are intentionally not
registered as services and are not consulted during public requests.

WP-06 route families remain `activation_pending`. Later work packages must
implement and verify enforcement before any catalog route is authorized.

## Rollback

Rollback is code and documentation only:

1. Remove the five `VeyluneTheme\Catalog` static contract files.
2. Remove this document.
3. Remove the WP-07 reference from the runtime architecture document.
4. Run the existing governance suite.

No database rollback is required.
