# Veylune Stage B2 - Calma Runtime Quarantine

**Date:** 2026-08-14  
**Founder decision:** Approved  
**State:** Complete

## Problem

Calma (`VLS-SOF-003`) remained active and visible after source reconciliation
identified a product identity, material, and media-rights conflict.

## Evidence and Action

Preflight resolved exactly one product ID and one canonical sales-channel
visibility row. A pre-change database snapshot was created:

`veylune-pre-calma-quarantine-20260814`

The lifecycle-safe quarantine changed only runtime exposure:

```text
active: 1 -> 0
visibility rows: 1 -> 0
stock preserved: 100
```

Product, price, stock, content, media, and taxonomy records were not deleted.

## Council Position

Identity, brand, content, domain, data, Shopware, quality, security, and release
authorities support fail-closing exposure. Architecture and delivery require a
reversible DAL operation and verified snapshot. Founder approval resolved the
runtime continuity conflict in favor of product truth.

## Verification

- Level 3 cohort baseline: PASS
- Identity sitemap: PASS (4 artifacts, 12 URLs)
- Full Veylune governance verification: PASS

## Rollback

`ddev snapshot restore veylune-pre-calma-quarantine-20260814`

Rollback is permitted only after the identity and rights decision is reviewed;
restoring exposure alone does not resolve the underlying conflict.

## Next Step

Resolve Aurelia's supplier/source identity or approve a governed replacement.
Calma remains quarantined until exact product identity, supplier relationship,
commercial authority, materials, and media rights all pass review.
