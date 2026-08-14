# Veylune Stage A2 - Demo Residue Cleanup Checkpoint

**Checkpoint date:** 2026-08-14
**Environment:** DDEV development
**Mode:** Executed exact-allowlist cleanup
**State:** Complete; post-cleanup governance PASS

## 1. Problem

The development environment retains Shopware demo records outside the governed
Veylune catalog. Cleanup requires a refreshed inventory, an exact ID allowlist,
a verified rollback point, and proof that deactivating the source plugin does
not change the protected 50-product batch.

## 2. Pre-cleanup Snapshot

| Field | Value |
| --- | --- |
| Snapshot name | `veylune-stage-a2-pre-cleanup-20260814` |
| Snapshot artifact | `.ddev/db_snapshots/veylune-stage-a2-pre-cleanup-20260814-mysql_8.0.gz` |
| SHA-256 | `81e977c7cfbceb0f926338017187c1a0192ad37e588a5431027e896067a28622` |
| Restore command | `ddev snapshot restore veylune-stage-a2-pre-cleanup-20260814` |
| Verification | Snapshot appears in `ddev snapshot --list` |

The snapshot was captured before deactivating the demo-data plugin and before
any residue deletion.

## 3. Refreshed Environment Inventory

| Resource | Current count |
| --- | ---: |
| Products | 70 |
| Known `SWDEMO` products | 16 |
| Demo parent products | 6 |
| Demo variants | 10 |
| Active demo products | 0 |
| Categories | 78 |
| Manufacturers | 5 |
| Property groups | 14 |
| Property options | 60 |
| Media | 86 |
| CMS pages | 13 |
| SEO URLs | 169 |

The count changes relative to the June Phase 2 baseline are expected because
the governed 50-product draft catalog and its taxonomy were added later.

## 4. Refreshed Demo Dependencies

| Dependency | Current count |
| --- | ---: |
| Demo product-category links | 6 |
| Demo product-property links | 20 |
| Demo variant-option links | 16 |
| Demo product-media links | 6 |
| Demo advanced-price rows | 2 |
| Demo product SEO URLs | 15 |
| Demo category SEO URLs | 14 |

These dependency counts match the Phase 2 quarantine assumptions.

## 5. Exact Removal Allowlist

The only authorized candidate IDs are the records classified as `remove` in:

`custom/plugins/VeyluneTheme/src/Resources/config/demo_quarantine_inventory.php`

Allowlist SHA-256:

`627b1a8524cfdab62d698bb0a0e537e97c2de0e3ce52a454bce679071f78d881`

| Allowlist group | Expected IDs | Present |
| --- | ---: | ---: |
| Demo products | 16 | 16 |
| Demo categories | 8 | 8 |
| Demo manufacturers | 4 | 4 |
| Demo-only property groups | 4 | 4 |
| Demo-attached material options | 4 | 4 |
| Demo product media | 6 | 6 |
| Demo product/category SEO URLs | 29 | 29 |

No `review` or `retain` record is part of the removal allowlist.

## 6. Plugin Lifecycle Checkpoint

`SwagPlatformDemoData` was deactivated successfully and its cache-clear
completed. It remains installed for rollback safety.

Post-deactivation state:

```text
SwagPlatformDemoData: installed, inactive
VeyluneTheme: installed, active
```

## 7. Protected Catalog Verification

After demo-plugin deactivation:

```text
Veylune products: 50
Active Veylune products: 0
Visibility rows: 0
Positive stock: 0
Product SEO URLs: 0
Search keywords: 0
Category coverage: 50/50
Room coverage: 50/50
Collection coverage: 50/50
Identity sitemap audit: PASS
```

No governed Veylune product was modified.

## 8. Proposed Cleanup Sequence

The cleanup operation must stop on the first failed checkpoint.

1. Verify the snapshot name and SHA-256.
2. Verify `SwagPlatformDemoData` remains inactive.
3. Re-read the allowlist file and verify its SHA-256.
4. Remove the 29 allowlisted demo SEO URL rows.
5. Remove demo product dependency rows.
6. Remove 10 variants before 6 parent products.
7. Remove 8 demo categories leaf-first.
8. Remove 4 demo-attached material options.
9. Remove 4 demo-only property groups.
10. Remove 4 demo manufacturers after product removal.
11. Remove 6 demo media records only after a fresh reference scan.
12. Clear caches and regenerate the identity sitemap.
13. Run catalog audit and the complete governance suite.
14. Reconcile all post-cleanup counts against the allowlist.

## 9. Hard Stop Conditions

Cleanup must stop and restore or escalate if:

- the snapshot or allowlist hash changes;
- any allowlisted product is no longer a `SWDEMO` product;
- any candidate has a dependency outside the documented demo graph;
- a media candidate has a non-demo reference;
- a category candidate owns a non-demo child;
- a property group or option is referenced by a Veylune product;
- any Veylune product count or containment metric changes;
- sitemap or runtime governance fails.

## 10. Rollback

Use:

```bash
ddev snapshot restore veylune-stage-a2-pre-cleanup-20260814
ddev exec php bin/console cache:clear
ddev exec php bin/console sitemap:generate --force
wsl bash bin/veylune-governance-check
```

Restoring the snapshot also restores the pre-deactivation database state.
Code must be restored to the matching revision if cleanup implementation code
has been added.

## 11. Council Recommendation

- Martin Kleppmann: the snapshot and immutable allowlist are sufficient to enter
  a controlled cleanup only if every stage is reconciled.
- Shopware Platform Authority: delete through Shopware DAL/lifecycle-safe
  operations rather than raw ad-hoc SQL.
- Kent Beck: run the regression baseline after plugin deactivation and after
  every destructive checkpoint.
- Troy Hunt: keep public exposure fail-closed throughout cleanup.
- Jez Humble: treat restore capability as a release gate, not as documentation.

## 12. Founder Decision Gate

Stage A2 preparation is complete when the full regression suite passes.

No residue deletion is authorized by this checkpoint. The next action requires
Founder approval for the exact allowlist and cleanup sequence above.


## 13. Founder Approval and Execution Result

Founder approval was received for the exact allowlist. The lifecycle-safe,
resume-safe command `veylune:catalog:demo-cleanup` passed syntax validation,
container lint, and dependency preflight before execution.

The database was already in a partially-cleaned state when the final preflight
was run. The command therefore reconciled existing allowlisted IDs and resumed
without touching non-allowlisted records.

```text
CLEANUP PASS
remainingDemoProducts: 0
remainingAllowlistedCategories: 0
remainingAllowlistedManufacturers: 0
remainingAllowlistedPropertyGroups: 0
remainingAllowlistedPropertyOptions: 0
remainingAllowlistedMedia: 0
remainingAllowlistedSeoUrls: 0
```

## 14. Post-cleanup Verification

The identity sitemap was regenerated for both configured languages.

```text
Identity sitemap audit: PASS
Checked artifacts: 4
Checked URLs: 12
Full Veylune governance verification: PASS
```

The attempted standalone command `veylune:catalog:audit` does not exist in this
codebase; catalog containment is instead covered by the registered governance
command audits and full `bin/veylune-governance-check`, which passed.

## 15. Next Stage

Stage A3 is a non-destructive residual review of records classified as
`review`, especially shared media, CMS pages, and material/property candidates.
No review-classified record is authorized for deletion without a new evidence
set and Founder decision.
