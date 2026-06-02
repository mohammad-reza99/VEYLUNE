# WP-07 Supplier Onboarding and Product Staging Harness

## Phase 6 Scope

WP-07 Phase 6 creates a non-public staging harness for future supplier
onboarding and product staging. It adds static contracts and isolated validators
only.

This phase does not onboard suppliers, onboard products, run imports, publish
products, activate categories, activate commerce, enable search, enable cart,
enable checkout, or create product streams.

## Supplier Staging Contract

`SupplierStagingContract` defines the supplier onboarding states used before any
real supplier is onboarded:

| State | Staging eligible | Rule |
| --- | --- | --- |
| `prospect` | No | Candidate supplier only |
| `review` | No | Commercial, compliance, media, and operations review |
| `approved` | Yes | Approved for staging preparation |
| `active` | Yes | Eligible for future import staging |

Staging requirements include supplier ID, legal name, display name, contact,
commercial owner, compliance owner, source system, media rights policy, returns
policy, and lead-time policy.

## SKU Reservation Harness

`SkuReservationHarness` validates candidate SKU reservations against in-memory
staging inputs only. It checks:

- canonical Veylune SKU format
- duplicate Veylune SKU
- retired SKU reuse
- duplicate supplier ID and supplier SKU mapping

The harness does not reserve SKUs in a database and does not create products.
It is a pre-import duplicate-detection contract.

## Batch Manifest Contract

`BatchManifestContract` defines the staged batch shape:

- batch ID
- supplier ID
- source reference
- source hash
- received timestamp
- owner
- rollback target
- items

Each item must include Veylune SKU, supplier SKU, publication state,
sellability state, taxonomy, properties, media, content, commerce, and SEO.

The manifest links every staged product candidate to rollback ownership before
future import execution.

## Product Readiness Audit Harness

`ProductReadinessAuditHarness` checks staged product candidates against the
Phase 5 readiness contract:

- taxonomy completeness
- property completeness
- media completeness
- commerce completeness
- SEO completeness
- EN content completeness
- DE content completeness

Candidates missing any required domain stay out of publication review.

## Media QA Harness

`MediaQualityAuditHarness` verifies staged media input:

- minimum 5 images
- primary image
- EN and DE alt text
- rights owner
- crop consistency
- quality approval

The harness does not read or write media entities. It validates staged media
metadata only.

## Content QA Harness

`ContentQualityAuditHarness` verifies staged content input:

- EN and DE titles
- EN and DE descriptions
- EN and DE SEO titles
- EN and DE meta descriptions
- EN/DE parity for titles and descriptions

The harness does not generate copy and does not publish content.

## Runtime Boundary

Phase 6 classes are intentionally not registered as services and are not
consulted during public requests. Existing WP-03 publication enforcement,
WP-04 sitemap containment, WP-05 mediated Edition retrieval, WP-06
activation-pending storefront ownership, and WP-07 Phases 1 through 5 behavior
remain unchanged.

## Rollback

Phase 6 rollback is code and documentation only:

1. Remove the six Phase 6 `VeyluneTheme\Catalog` static contract/harness files.
2. Remove this document.
3. Remove the WP-07 Phase 6 reference from `docs/veylune-architecture.md`.
4. Run the existing governance suite.

No database rollback is required because Phase 6 performs no database writes.
