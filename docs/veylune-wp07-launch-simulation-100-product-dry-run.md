# WP-07 Launch Simulation and 100 Product Dry Run

## Phase 9 Scope

WP-07 Phase 9 runs a mock-only validation simulation for the 100 product launch
pipeline. It does not onboard real suppliers, import products, create products,
publish products, activate categories, activate commerce, enable search, or
write database records.

## Simulation Setup

The simulation defines:

- 3 mock suppliers
- 100 mock product candidates
- 4 generated batch manifests
- mock rollback snapshot metadata for each batch
- mock media, content, taxonomy, property, commerce, SEO, and governance fields

Batch allocation:

| Batch | Supplier | Products | Purpose |
| --- | --- | ---: | --- |
| `mock-launch-batch-1` | `mock-supplier-a` | 30 | Furniture core |
| `mock-launch-batch-2` | `mock-supplier-b` | 22 | Lighting |
| `mock-launch-batch-3` | `mock-supplier-c` | 28 | Decor and supporting assortment |
| `mock-launch-batch-4` | `mock-supplier-a` | 20 | Remaining furniture and dining support |

## Validation Chain

`LaunchSimulationRunner` generates manifests through `LaunchSimulationFactory`
and validates each batch with the Phase 8 `BatchValidationEngine`.

The simulation covers:

- supplier staging eligibility
- SKU uniqueness
- duplicate SKU negative probe
- duplicate supplier-SKU negative probe
- retired SKU negative probe
- manifest validation
- taxonomy validation
- controlled property validation
- product readiness validation
- media QA validation
- content QA validation
- rollback snapshot validation

## Command

`veylune:catalog:launch-simulation` runs the full mock-only simulation.

Expected output:

- `PASS` when all four batches and negative probes validate
- `FAIL` with detailed findings if any validation fails

The command is internal and command-only. It does not create or mutate catalog
entities.

## Readiness Interpretation

A passing Phase 9 simulation means the mock launch pipeline can validate a full
100 product staging shape. It does not approve production catalog execution by
itself. Production execution still requires real supplier records, real SKU
reservations, real media files, real batch manifests, and rollback rehearsal.

Projected readiness after a passing simulation:

| Area | Score |
| --- | ---: |
| Catalog | 68 |
| Operations | 70 |
| Import readiness | 64 |
| Launch readiness | 62 |

## Runtime Boundary

Phase 9 is command-only simulation. Existing WP-03 publication enforcement,
WP-04 sitemap containment, WP-05 mediated Edition retrieval, WP-06
activation-pending storefront ownership, and WP-07 Phases 1 through 8 behavior
remain unchanged.

## Rollback

Phase 9 rollback is code and documentation only:

1. Remove the launch simulation factory, runner, report, and command.
2. Remove Phase 9 mock supplier entries from `staging_registries.php`.
3. Remove Phase 9 service registrations.
4. Remove this document.
5. Remove the WP-07 Phase 9 reference from `docs/veylune-architecture.md`.
6. Run the existing governance suite.

No database rollback is required because Phase 9 performs no database writes.
