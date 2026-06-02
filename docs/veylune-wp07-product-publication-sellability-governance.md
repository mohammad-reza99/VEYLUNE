# WP-07 Product Publication and Sellability Governance

## Phase 5 Scope

WP-07 Phase 5 creates governed publication and sellability contracts for future
production products. It adds static contracts and documentation only.

This phase does not onboard products or suppliers, run imports, activate
products, activate categories, publish catalog records, enable commerce, enable
search, or create product streams.

## Product Publication Contract

`ProductPublicationGovernanceContract` defines product publication as an
independent governance authority.

| State | Publicly eligible | Rule |
| --- | --- | --- |
| `draft` | No | Work in progress |
| `review` | No | Readiness and governance review |
| `approved` | No | Approved but not explicitly published |
| `published` | Yes | Explicit public-product publication |
| `suspended` | No | Immediate fail-closed withdrawal |
| `archived` | No | Terminal historical record |

Allowed transitions:

- `draft` -> `review`, `archived`
- `review` -> `draft`, `approved`, `archived`
- `approved` -> `draft`, `review`, `published`, `archived`
- `published` -> `suspended`, `archived`
- `suspended` -> `review`, `approved`, `archived`
- `archived` -> no transitions

Publication remains independent from Shopware activation, stock, visibility,
supplier status, sellability, and import approval.

## Sellability Governance Contract

`SellabilityGovernanceContract` defines commerce readiness separately from
publication.

Statuses:

- `sellable`
- `not_sellable`

Sellability requirements cover pricing, media, content, taxonomy, supplier, and
compliance. A product may be published but not sellable, or sellable but not
published. Stock and Shopware activation never imply sellability.

## Product Readiness Contract

`ProductReadinessContract` defines minimum requirements before publication
review:

- identity: Veylune SKU, supplier ID, supplier SKU, source batch
- content: EN and DE title and description
- media: primary image, detail image, rights, EN/DE alt text
- physical: dimensions, weight, unit
- materials: primary material, finish, color
- taxonomy: department, category, product type, room
- commerce: price, tax, lead time, delivery class, returns class
- SEO: EN/DE SEO title and meta description
- governance: quality status, reviewer, rollback target

Missing readiness facts keep the product out of publication review.

## Product Quality Gate Contract

`ProductQualityGateContract` defines approval requirements:

- content quality: EN/DE parity, premium tone, care guidance
- media quality: image quality and rights status
- taxonomy validation: canonical department, category, category lifecycle
- property validation: controlled material, finish, color, room
- commerce validation: price, tax, delivery, returns
- supplier validation: active supplier, supplier SKU mapping, approved batch
- governance validation: publication state, rollback target, reviewer approval

Quality approval is a prerequisite for publication approval. It does not publish
the product by itself.

## Product Withdrawal Contract

`ProductWithdrawalContract` governs `suspended` and `archived`.

| State | Publicly renderable | Sellable | Rollback allowed | Rule |
| --- | --- | --- | --- | --- |
| `suspended` | No | No | Yes | Immediate fail-closed withdrawal |
| `archived` | No | No | No | Terminal historical record |

Withdrawal ownership remains with `product_governance`. Suspended products must
be non-public and non-sellable immediately in future enforcement.

## Runtime Boundary

These contracts are not registered as services and are not consulted during
public requests. Existing WP-03 publication enforcement, WP-04 sitemap
containment, WP-05 mediated Edition retrieval, WP-06 activation-pending
storefront ownership, and WP-07 Phases 1 through 4 behavior remain unchanged.

## Rollback

Phase 5 rollback is code and documentation only:

1. Remove the five Phase 5 `VeyluneTheme\Catalog` static contract files.
2. Remove this document.
3. Remove the WP-07 Phase 5 reference from `docs/veylune-architecture.md`.
4. Run the existing governance suite.

No database rollback is required because Phase 5 performs no database writes.
