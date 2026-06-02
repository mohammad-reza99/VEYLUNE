# WP-07 Controlled Staging Validation and Dry-Run Harness

## Phase 8 Scope

WP-07 Phase 8 creates command-only validation infrastructure for future staged
supplier and product batches. It does not onboard suppliers, create products,
run imports, publish products, activate categories, activate commerce, enable
search, enable cart, enable checkout, or create product streams.

## Supplier Staging Registry

`SupplierStagingRegistry` reads persisted staging records from
`Resources/config/staging_registries.php`.

The registry supports supplier states from the Phase 6 staging contract:

- `prospect`
- `review`
- `approved`
- `active`

Only `approved` and `active` suppliers are staging-eligible. The default
registry is intentionally empty, so Phase 8 does not onboard any supplier.

## SKU Reservation Registry

`SkuReservationRegistry` reads canonical SKU reservations and retired SKU
records from the same governed staging registry file. It validates:

- canonical Veylune SKU format
- duplicate Veylune SKU reservations
- duplicate supplier ID and supplier SKU mappings
- retired SKU reuse

The registry supports reservation validation and retirement checks. It does not
write reservations.

## Batch Validation Engine

`BatchValidationEngine` validates staged batch manifests only. It checks:

- manifest required fields
- supplier registry presence and staging eligibility
- supplier staging requirements
- rollback snapshot shape and checkpoint
- SKU uniqueness across registry and manifest
- taxonomy department and required taxonomy fields
- controlled material, finish, color, and room values
- product readiness requirements
- media QA requirements
- content QA requirements

The engine returns a `BatchValidationReport` with PASS/FAIL semantics and
detailed violations. It does not import or persist product records.

## Rollback Snapshot Contract

`RollbackSnapshotContract` defines rollback snapshot metadata:

- snapshot ID
- batch ID
- checkpoint
- owner
- created timestamp
- catalog reference
- SKU mapping reference
- property mapping reference
- media reference
- restore notes

Checkpoint values must match rollback governance checkpoints. Restore execution
is not implemented in Phase 8.

## Dry-Run Validation Command

`veylune:catalog:staging-dry-run <manifest>` validates a JSON batch manifest.

Output:

- `PASS` with item and violation counts when validation succeeds
- `FAIL` with detailed violations when validation fails

The command is internal and command-only. It performs no catalog writes and is
not consulted by public requests.

## Runtime Boundary

Phase 8 services are command/validation infrastructure only. Existing WP-03
publication enforcement, WP-04 sitemap containment, WP-05 mediated Edition
retrieval, WP-06 activation-pending storefront ownership, and WP-07 Phases 1
through 7 behavior remain unchanged.

## Rollback

Phase 8 rollback is code and documentation only:

1. Remove the Phase 8 catalog registry/validation classes.
2. Remove `StagingDryRunValidationCommand`.
3. Remove the Phase 8 service registrations.
4. Remove `Resources/config/staging_registries.php`.
5. Remove this document.
6. Remove the WP-07 Phase 8 reference from `docs/veylune-architecture.md`.
7. Run the existing governance suite.

No database rollback is required because Phase 8 performs no database writes.
