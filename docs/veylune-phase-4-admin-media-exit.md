# Veylune Phase 4 Admin Media Exit

Status: complete for the governed private-preview cover scope.

## Delivered

- 50 original Veylune draft cover images, one for every governed catalog record.
- 50 Shopware Admin Media records assigned as product covers.
- 13 Shopware Admin Media records for every category, room, and collection destination hero.
- Localized English and German media title and alt metadata.
- Source provenance, prompt family, rights status, SHA-256, visual-review status, and source-batch metadata.
- Semantic image rendering in catalog cards, product detail, Object Mode, cart, and checkout.
- Destination hero rendering resolves deterministic Admin Media records and no longer names theme files in the active template.
- Cart and checkout migration for selections saved before Admin covers existed.
- All 50 products remain inactive and unavailable to the public sales channel.

The generated cover set is a complete private-preview cover layer. It is not a launch gallery: launch approval still requires five independently reviewed images per approved product under the source contract.

## Safety and recovery

- Pre-import database snapshot: `phase4-pre-media-import-20260920`
- Pre-editorial-import database snapshot: `phase4-pre-editorial-media-20260921`
- Rollback manifest: `var/veylune-phase4-media-rollback.json`
- Editorial rollback manifest: `var/veylune-phase4-editorial-media-rollback.json`
- Audit report: `reports/catalog/phase-4-media-cover-pipeline.json`
- Editorial audit report: `reports/catalog/phase-4-editorial-media-pipeline.json`
- Source contract: `config/veylune-phase-4-media-source-contract.json`

Dry run:

```bash
ddev exec php bin/console veylune:media:cover-pipeline
```

Audit:

```bash
ddev exec php bin/console veylune:media:cover-pipeline --audit
ddev exec php bin/console veylune:media:editorial-pipeline --audit
php bin/veylune-phase-4-exit-audit
```

Rollback, if explicitly required:

```bash
ddev exec php bin/console veylune:media:cover-pipeline --rollback
ddev exec php bin/console veylune:media:editorial-pipeline --rollback
```

## Acceptance evidence

- 50 of 50 governed products have a renderable Admin Media cover.
- 13 of 13 catalog destination heroes are renderable Admin Media with localized metadata.
- 50 of 50 governed products remain inactive.
- Zero launch-approved products are inferred from draft-cover completion.
- Product media is rendered with semantic `img` elements; record-specific CSS background mappings are retired.
- Mobile filter and sort controls stay within the viewport.
- Previously saved cart entries are hydrated from the server-side media manifest.
- Cart and checkout expose the real product cover and accessible alt text.
- Storefront build, PHP lint, Twig lint, Phase 4 exit audit, and project governance must all pass before this phase is accepted.

## Manual verification routes

Append the configured private-preview token to each private route:

- `/__veylune-preview/catalog`
- `/__veylune-preview/catalog/category/furniture`
- `/__veylune-preview/catalog/product/F01`
- `/__veylune-preview/cart`
- `/__veylune-preview/checkout`

Next roadmap package: Phase 5.1A, governed commerce readiness and end-to-end interaction closure.
