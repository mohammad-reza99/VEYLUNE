# Veylune Phase 4.1A - Admin Catalog and Media Baseline

Status date: 2026-09-20

Status: complete, read-only baseline

## Outcome

The draft catalog identity layer is intact: all 50 manifest SKUs exist in Shopware. The media layer is not launch-ready. None of the 50 products has a Shopware product-media association or Admin cover, and none can currently satisfy the governed gallery, localized alt-text, rights, quality, or semantic-image gates.

Phase 4.1A made no database changes. It records the boundary that Phase 4.1B must cross and preserves the current CSS assets until the Admin-backed rendering path is verified.

## Measured inventory

| Measure | Result |
| --- | ---: |
| Draft products in manifest | 50 |
| Draft products present in database | 50 |
| Shopware product-media associations | 0 |
| Products with Admin media | 0 |
| Products with Admin cover | 0 |
| Products with EN and DE media alt text | 0 |
| Product assets stored in theme code | 19 |
| Theme assets mapped through product-specific CSS | 19 |
| Theme assets meeting the 1600 px minimum long edge | 0 |
| Theme assets below the 1600 px minimum long edge | 19 |
| Products without a dedicated asset | 31 |
| Semantically rendered product images | 0 |
| Launch-media-ready products | 0 |

Department distribution is 19 furniture, 10 lighting, 9 decor and objects, 5 textiles and rugs, 4 dining and kitchen, and 3 outdoor products.

## Current source map

- F01 through F19 each have one WebP asset in theme code. The active storefront maps these files to product records with CSS background selectors.
- L01 through L10, D01 through D09, T01 through T05, K01 through K04, and O01 through O03 have no dedicated product asset in Admin or theme code.
- Existing theme files are primary candidates only. Their rights and source provenance are unverified, so they are not approved for public launch.
- All 19 existing files measure 1122 by 1402 pixels. They are suitable as preview references, but all fall below the governed 1600 px minimum long edge and need a higher-quality source or an explicit review outcome before launch use.
- The private-preview card and PDP media treatments do not yet constitute a semantic, responsive Shopware image gallery.

## Governed gallery contract

Every launch product needs at least five useful Admin-managed slots:

1. Primary or cover image.
2. Alternate angle.
3. Material or detail image.
4. Scale or room-context image.
5. Dimensions diagram or useful variant image.

The preferred target is six useful images where evidence supports them. Every file must carry a product association, position, English and German alt text, rights owner and status, source provenance, quality status, dimensions, MIME type, and checksum. Unknown rights and missing alt text fail closed.

## Wayfair comparison

The live Wayfair reference exposes product imagery inside a dense discovery and commerce system: department navigation, search, linked product cards, pricing states, and product destinations are all data-backed. Veylune currently has the discovery shell, but its product imagery is disconnected from Shopware content authority. Reproducing the reference behavior responsibly therefore starts with Admin-backed media and semantic image delivery, not with another visual CSS patch.

Reference captured from `https://www.wayfair.com/` on 2026-09-20.

## Reversible migration plan

1. Inventory and hash current files. Complete in 4.1A.
2. Review rights, provenance, and source quality before import.
3. Import eligible F01-F19 assets into a governed Shopware Media folder with deterministic names.
4. Associate imported media to the correct product and set the cover in an idempotent transaction.
5. Write English and German metadata and the governance fields.
6. Render Admin media as semantic responsive images in cards and PDPs.
7. Verify public and private routes at desktop, tablet, mobile, keyboard, and zoom states.
8. Remove product-specific CSS background mappings only after parity passes.

Rollback is simple until step 8: retain the existing theme files and mappings, and make the import command dry-run capable and idempotent. No destructive cleanup belongs in 4.1B.

## Saved evidence

- Contract: `config/veylune-phase-4-media-source-contract.json`
- JSON report: `reports/catalog/phase-4-1a-media-baseline.json`
- CSV product matrix: `reports/catalog/phase-4-1a-media-baseline.csv`
- Read-only capture command: `php bin/console veylune:media:baseline`
- Regression audit: `php bin/veylune-phase-4-media-baseline-audit`

## Exact next package

Phase 4.1B: build an idempotent dry-run intake for the 19 existing theme assets, add rights and quality review inputs, and allow Admin import only as governed non-public candidates until a reviewer accepts a higher-quality source. Do not create covers or remove CSS fallbacks before approval.
