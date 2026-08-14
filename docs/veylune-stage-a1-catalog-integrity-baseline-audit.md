# Veylune Stage A1 - Catalog Integrity Baseline Audit

**Audit date:** 2026-08-14
**Environment:** DDEV development
**Batch:** `WP-CAT-04-DRAFT-50`
**Mode:** Read-only reconciliation
**Decision:** Catalog integrity passed; environment demo residue remains quarantined

## 1. Problem

The 50 Shopware draft products must be reconciled against the canonical
WP-CAT-04 manifest before supplier readiness or demo cleanup can proceed. The
audit must distinguish contamination of the governed batch from unrelated demo
residue that remains elsewhere in the environment.

## 2. Evidence

The audit used:

- `DraftCatalogManifest` as the canonical 50-product source;
- `veylune:catalog:draft-seed dry-run` for manifest, deterministic ID, SKU,
  slug, taxonomy, and infrastructure validation;
- `veylune:catalog:draft-seed audit` for aggregate database validation;
- read-only SQL reconciliation for per-product state and association coverage;
- the Phase 2 demo quarantine inventory for known demo IDs;
- the complete Veylune governance verification suite for runtime containment.

No product, category, property, media, visibility, or SEO record was written by
this audit.

## 3. Canonical Batch Reconciliation

| Check | Expected | Actual | Result |
| --- | ---: | ---: | --- |
| Batch products | 50 | 50 | Pass |
| Unique canonical SKUs | 50 | 50 | Pass |
| Unique catalog record IDs | 50 | 50 | Pass |
| Deterministic UUID/SKU conflicts | 0 | 0 | Pass |
| Invalid core states | 0 | 0 | Pass |
| Invalid governance states | 0 | 0 | Pass |
| Products with supplier/manufacturer facts | 0 | 0 | Pass |
| Products without exactly two governed categories | 0 | 0 | Pass |
| Products missing a governed material | 0 | 0 | Pass |
| Products missing a governed room | 0 | 0 | Pass |
| Products missing a governed collection | 0 | 0 | Pass |
| Product media assignments | 0 | 0 | Pass |
| Sales-channel visibility rows | 0 | 0 | Pass |
| Product SEO URLs | 0 | 0 | Pass |
| Search keyword rows | 0 | 0 | Pass |

An invalid core state means any batch product is active, has positive stock, or
has positive available stock.

An invalid governance state means any batch product differs from:

```text
publication_state = draft
readiness_level = L0
sellability_status = not_sellable
exposure_status = not_approved
search_index_state = excluded
storefront_activation_state = blocked
commerce_activation_state = blocked
```

## 4. Department and Status Distribution

| Department | Products |
| --- | ---: |
| Furniture | 19 |
| Lighting | 10 |
| Decor & Objects | 9 |
| Textiles & Rugs | 5 |
| Dining & Kitchen | 4 |
| Outdoor | 3 |
| **Total** | **50** |

| Planning status | Products |
| --- | ---: |
| Coming Soon | 4 |
| Supplier Selection | 46 |
| **Total** | **50** |

Both distributions match the canonical manifest.

## 5. Taxonomy Infrastructure

| Governed resource | Actual |
| --- | ---: |
| Department categories | 6 |
| Product-type categories | 30 |
| Room options | 6 |
| Collection options | 7 |
| Material options | 10 |

All 50 products have category, room, collection, and material associations.
Candidate relationships remain non-public and do not constitute merchandising
approval.

## 6. Public Exposure Containment

| Exposure vector | Actual | Result |
| --- | ---: | --- |
| Active products | 0 | Pass |
| Positive stock products | 0 | Pass |
| Visibility rows | 0 | Pass |
| Product SEO URLs | 0 | Pass |
| Search keywords | 0 | Pass |
| Product media assignments | 0 | Pass |

The regenerated identity sitemap contains four artifacts and twelve governed
URLs. The sitemap audit reports no non-governed URL leakage. The full governance
suite passes after aligning legacy homepage assertions with the approved
discovery navigation.

## 7. Demo Contamination

| Check | Actual | Result |
| --- | ---: | --- |
| Products in the environment | 70 | Informational |
| Known `SWDEMO` products | 16 | Quarantined residue |
| Active `SWDEMO` products | 0 | Contained |
| Batch products using a demo SKU | 0 | Pass |
| Batch links to quarantined demo categories | 0 | Pass |
| Batch links to quarantined demo properties/options | 0 | Pass |
| Batch links to quarantined demo media | 0 | Pass |

The governed 50-product batch has zero measured demo contamination. The
environment does not yet have zero demo residue: 16 inactive demo products
remain, and `SwagPlatformDemoData` remains active. Cleanup is not authorized by
this audit.

## 8. Risks

1. An active demo-data plugin can recreate or preserve demo residue during later
   environment operations.
2. The Phase 2 quarantine inventory was captured before the 50-product draft
   seed and must be revalidated immediately before cleanup.
3. Review-classified CMS pages, media, and shared property records must not be
   deleted automatically.
4. Catalog readiness remains L0; a passing integrity audit does not authorize
   supplier claims, media, price approval, publication, or sellability.

## 9. Council Positions

- **Eric Evans:** the governed batch preserves Product identity, taxonomy, and
  lifecycle boundaries; demo records remain outside the Veylune batch.
- **Martin Kleppmann:** manifest and database counts reconcile, but the cleanup
  stage needs a fresh backup and dependency snapshot rather than relying only
  on the June inventory.
- **Shopware Platform Authority:** cleanup must use Shopware DAL or documented
  lifecycle operations and must preserve shared CMS/property dependencies.
- **Kent Beck:** the full governance suite is the regression baseline for every
  cleanup checkpoint.
- **Troy Hunt:** publication, search, sitemap, visibility, and commerce controls
  remain fail-closed.

## 10. Acceptance Decision

Stage A1 passes for the governed catalog:

```text
50 governed drafts
50 unique canonical SKUs
0 invalid product states
0 public product exposure
0 invalid taxonomy coverage
0 batch-level demo contamination
```

Environment-wide demo cleanup is intentionally deferred.

## 11. Next Step

**Stage A2 - Demo Residue Cleanup Plan and Rollback Checkpoint**

Before any deletion:

1. refresh the quarantine inventory against the current database;
2. capture and verify a database backup;
3. define immutable pre-cleanup counts and ID allowlists;
4. deactivate `SwagPlatformDemoData`;
5. prove rollback before removing the first demo record;
6. request Founder approval for the exact removal allowlist.

