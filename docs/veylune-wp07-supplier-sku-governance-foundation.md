# WP-07 Supplier and Canonical SKU Governance Foundation

## Phase 4 Scope

WP-07 Phase 4 creates supplier governance and canonical SKU governance for
future supplier onboarding. It adds static contracts and documentation only.

This phase does not onboard suppliers, onboard products, run imports,
restructure categories, activate products, publish products, activate commerce,
enable search, or create product streams.

## Supplier Master Contract

`SupplierMasterContract` defines supplier identity, ownership, lifecycle, and
import eligibility.

Supplier master identity fields:

- supplier ID
- legal name
- display name
- country
- primary contact
- commercial terms owner
- compliance owner
- source system
- supplier status

Supplier lifecycle:

| Status | Import eligible | Rule |
| --- | --- | --- |
| `prospect` | No | Candidate supplier |
| `review` | No | Commercial and compliance review |
| `approved` | No | Approved but not active |
| `active` | Yes | Explicit onboarding eligibility |
| `suspended` | No | Immediate fail-closed stop |
| `retired` | No | Terminal historical supplier |

Only `supplier_governance` owns supplier status transitions. Active supplier
status authorizes future import eligibility only; it never publishes products.

## Canonical SKU Contract

`CanonicalSkuContract` makes Veylune SKU the public product identity.

SKU structure:

```text
VLS-{DEPARTMENT}-{SEQUENCE}
VLS-{DEPARTMENT}-{SEQUENCE}-{VARIANT}
```

Examples:

- `VLS-FUR-000001`
- `VLS-FUR-000001-01`

Rules:

- Veylune SKU is globally unique.
- Supplier ID plus supplier SKU must be unique.
- Supplier SKU never becomes public identity.
- Retired SKU is never reused.
- Variant SKU must reference its parent SKU.
- SKU assignment requires a batch reference.

SKU lifecycle:

| State | Rule |
| --- | --- |
| `reserved` | Held for future assignment |
| `assigned` | Mapped to a governed product candidate |
| `published` | Linked to future published product identity |
| `retired` | Permanently reserved and never reused |

SKU retirement requires SKU-governance approval, supplier mapping history,
public URL impact review, and rollback target retention.

## Batch Governance Contract

`BatchGovernanceContract` defines import batch identity and rollback ownership.

Batch identity fields:

- batch ID
- supplier ID
- source reference
- received timestamp
- source hash
- review owner
- rollback target
- batch state

Batch lifecycle:

| State | Apply eligible |
| --- | --- |
| `received` | No |
| `mapped` | No |
| `review` | No |
| `approved` | Yes |
| `applied` | No |
| `rolled_back` | No |
| `archived` | No |

Every batch must define a rollback target before application. Rollback target
must include previous catalog snapshot, SKU mapping, media reference, and
property mapping snapshots.

## Import Governance Contract

`ImportGovernanceContract` defines future source review and approval gates.

Review states:

- `received`
- `mapped`
- `validated`
- `approved`
- `rejected`

Approval requires active supplier status, canonical SKU assignment,
supplier-SKU duplicate check, taxonomy mapping review, property mapping review,
media rights review, commerce fact review, content quality review, and rollback
target definition.

Publication independence rules:

- Import approval never publishes a product.
- Supplier active status never publishes a product.
- Shopware active flag never implies product publication.
- Stock availability never implies product publication.
- Visibility assignment never implies product publication.
- Product lifecycle state remains the separate publication authority.

## Rollback Governance Contract

`RollbackGovernanceContract` defines rollback authority, scope, checkpoints, and
ownership.

Governed rollback scopes:

- supplier
- batch
- SKU mapping
- property mapping
- media mapping
- catalog records

Rollback checkpoints:

- `pre_supplier_activation`
- `pre_batch_mapping`
- `pre_import_application`
- `post_import_application`
- `pre_publication_review`
- `post_regression_verification`

`rollback_governance` owns execution authority. Domain owners must approve
rollback for their owned scope: supplier governance for supplier status, SKU
governance for SKU mapping, property-dictionary governance for property mapping,
media governance for media mapping, and product governance for catalog records.

## Runtime Boundary

These contracts are not registered as services and are not consulted during
public requests. Existing WP-03 publication enforcement, WP-04 sitemap
containment, WP-05 mediated Edition retrieval, WP-06 activation-pending
storefront ownership, and WP-07 Phases 1 through 3 behavior remain unchanged.

## Rollback

Phase 4 rollback is code and documentation only:

1. Remove the five Phase 4 `VeyluneTheme\Catalog` static contract files.
2. Remove this document.
3. Remove the WP-07 Phase 4 reference from `docs/veylune-architecture.md`.
4. Run the existing governance suite.

No database rollback is required because Phase 4 performs no database writes.
