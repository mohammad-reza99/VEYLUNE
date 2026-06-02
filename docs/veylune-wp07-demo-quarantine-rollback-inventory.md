# WP-07 Demo Quarantine and Rollback Inventory

## Phase 2 Scope

WP-07 Phase 2 records a governed quarantine inventory for Shopware demo residue.
It does not delete records, deactivate plugins, restructure taxonomy, onboard
catalog data, publish products, activate commerce, or change storefront runtime
behavior.

The scalar inventory is stored in
`Resources/config/demo_quarantine_inventory.php`. The static
`DemoResidueQuarantineContract` defines the only allowed classifications and the
later safe-removal sequence. Neither artifact is registered as a service or
consulted during public requests.

## Capture Baseline

The database was read on 2026-06-02 before any cleanup work:

| Resource | Total |
| --- | ---: |
| Products | 20 |
| Categories | 42 |
| Manufacturers | 5 |
| Property groups | 11 |
| Property options | 37 |
| Media | 101 |
| CMS pages | 13 |
| Product streams | 0 |
| SEO URLs | 169 |

`SwagPlatformDemoData` was installed and active at capture time. Deactivation is
required before a later cleanup sprint, but is intentionally not performed in
this phase.

## Quarantine Classification

| Group | Classification | Evidence and rule |
| --- | --- | --- |
| Six `SWDEMO` parent products | `remove` | Demo SKU prefix |
| Ten `SWDEMO` variants | `remove` | Demo SKU prefix and demo parent |
| Eight demo categories | `remove` | Demo taxonomy branches |
| Four Shopware demo manufacturers | `remove` | Demo manufacturer namespace; remove after products |
| `Farbe`, `Size`, `Zielgruppe`, `Zutaten` property groups | `remove` | Demo-only or unused demo dictionary groups |
| `Material` property group | `review` | Shared by demo and Veylune products |
| Four demo-attached `Material` options | `remove` | Referenced only by demo products |
| Three unassigned `Material` options | `review` | Origin is uncertain |
| Two Veylune `Material` options | `retain` | Referenced by Veylune products |
| Six demo product media records | `remove` | Referenced directly by demo products only |
| Eighty-three media records outside product assignments | `review` | No deletion without a full reference scan |
| Twenty-nine demo SEO URLs | `remove` | Foreign keys resolve to demo products or demo categories |
| Shared standard category CMS layout | `retain` | Referenced by 34 categories, including eight demo categories |
| Veylune listing layout and active homepage CMS page | `retain` | Existing storefront dependencies |
| Ten remaining CMS pages | `review` | No evidence-backed removal authorization |

The manifest records stable IDs for every evidence-backed removal candidate.
Review classification is deliberately fail-closed: uncertain records cannot be
removed automatically.

## Dependency Analysis

The demo product set owns:

| Dependency | Count | Removal impact |
| --- | ---: | --- |
| Category links | 6 | Detach before product removal |
| Property links | 20 | Detach before property-option cleanup |
| Variant option links | 16 | Remove with variants before parent products |
| Media links | 6 | Detach before media reference scan |
| Advanced price rows | 2 | Remove with product dependency rows |
| Product SEO URLs | 15 | Remove before product records |

The demo category set owns 14 SEO URLs. All eight demo categories reference the
shared standard category layout, so category cleanup must preserve that CMS
layout. Demo product media records are not category media.

## Rollback Inventory

Every later cleanup stage requires a verified database backup and the scalar
manifest committed with that cleanup change. Restore points:

1. `pre_cleanup_database_backup`
2. `post_plugin_deactivation`
3. `post_product_cleanup`
4. `post_taxonomy_cleanup`
5. `post_media_cleanup`
6. `post_regression_verification`

Rollback restores the latest verified database backup from before the failed
stage, restores the matching code revision, clears caches, and reruns the
governance regression suite. Phase 2 itself requires no database rollback
because it performs no database writes.

## Safe Removal Sequence

This sequence is documentation for a later authorized cleanup sprint:

1. Capture and verify a database backup against the manifest.
2. Deactivate `SwagPlatformDemoData`.
3. Remove demo SEO URLs.
4. Remove demo product dependency rows.
5. Remove demo variants before parent products.
6. Remove demo parent products.
7. Remove demo categories leaf-first.
8. Remove demo-only property options, then demo-only property groups.
9. Remove demo-only manufacturers.
10. Remove demo-only media after a complete reference scan.
11. Review unclassified media and CMS content manually.
12. Run the governance and runtime regression suite.

No step in this sequence is authorized by Phase 2.

## Runtime Boundary

These artifacts are static rollback preparation only. Existing WP-03
publication enforcement, WP-04 sitemap containment, WP-05 mediated Edition
retrieval, WP-06 activation-pending storefront ownership, and WP-07 Phase 1
catalog contracts remain unchanged.
