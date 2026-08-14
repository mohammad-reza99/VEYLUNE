# Veylune WP-07 Batch Manifest Contract

## Phase 7 Scope

WP-07 Phase 7 defines the canonical structural boundary for a supplier product
batch before it can enter controlled staging validation. It closes the gap
between the Phase 6 staging harnesses and the Phase 8 file-backed dry-run
validator.

This phase does not onboard a supplier, reserve a SKU, import or create a
product, read a supplier file, publish content, activate commerce, or write to
the database.

## Contract

`BatchManifestContract` requires every batch manifest to contain:

- `batch_id`
- `supplier_id`
- `source_reference`
- `source_hash`
- `received_at`
- `owner`
- `rollback_target`
- `items`

Every item in `items` must contain:

- `veylune_sku`
- `supplier_sku`
- `publication_state`
- `sellability_state`
- `taxonomy`
- `properties`
- `media`
- `content`
- `commerce`
- `seo`

Presence is deliberately distinct from approval. Empty arrays are valid for
structured sections when a later validator needs to report their missing or
incomplete contents. Missing keys, `null`, and empty-string scalar values fail
the structural boundary.

## Ownership and Provenance

The canonical contract owner is `batch_governance`. A manifest must retain a
stable batch identifier, supplier identifier, immutable source reference and
hash, receipt timestamp, accountable owner, and explicit rollback target.

Those fields allow later phases to explain where a batch came from, who owns
its review, and how the batch can be reversed. Their presence does not imply
that the supplier, source, or rollback target has passed semantic validation.

## Validation Boundary

The contract exposes deterministic required-field lists and two fail-closed
checks:

```php
BatchManifestContract::missingFields($manifest);
BatchManifestContract::missingItemFields($item);
```

Both methods return missing field names in canonical contract order. They do
not mutate their inputs and do not perform I/O. Content, media, taxonomy,
readiness, supplier, SKU, publication, and sellability semantics remain owned
by their dedicated governance contracts and audit harnesses.

## Relationship to Adjacent Phases

- Phase 6 defines the staging and quality harnesses that consume governed
  candidate data.
- Phase 7 defines the minimum manifest and item envelope passed to validation.
- Phase 8 resolves file-backed registries and applies the complete dry-run
  validation chain.
- Phase 9 generates mock suppliers and candidates to exercise that chain at
  launch volume without real commerce data.

## Acceptance

Phase 7 is complete when:

1. required batch fields are explicit and stable;
2. required item fields are explicit and stable;
3. missing values are reported deterministically;
4. the contract performs no I/O or mutation;
5. architecture documentation records the Phase 7 boundary;
6. Phases 8 and 9 can depend on the contract without widening public runtime
   behavior.

## Rollback

Phase 7 rollback is code and documentation only:

1. remove `BatchManifestContract`;
2. remove this document;
3. remove the Phase 7 paragraph from `docs/veylune-architecture.md`;
4. update later validation code that depends on the contract.

No database rollback is required because Phase 7 performs no database writes.
