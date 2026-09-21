# Veylune Phase 4.1B - Governed Media Intake Dry Run

Status date: 2026-09-20

Status: complete, read-only dry run

## Outcome

The 19 existing theme product assets now have a deterministic, checksum-bound Shopware Media intake plan and an editable per-asset review record. The process is intentionally fail-closed: all 19 candidates remain held because rights, source quality, and localized metadata have not been approved.

Phase 4.1B did not import media, create product-media associations, set Admin covers, or remove CSS fallbacks.

## Verified result

| Measure | Result |
| --- | ---: |
| Source candidates | 19 |
| Unique deterministic target names | 19 |
| Unique idempotency keys | 19 |
| Explicitly approved for private intake | 0 |
| Held by governance | 19 |
| Would import in the current state | 0 |
| Existing private intake candidates | 0 |
| Target-name collisions | 0 |
| Product associations planned | 0 |
| Admin covers planned | 0 |
| Database mutations | 0 |

The Shopware database remains at 77 total media records, 0 Veylune intake-named media records, 0 product-media associations for the draft batch, and 0 batch products with a cover.

## Review input

Each candidate has a versioned record containing:

- Exact source path, SHA-256 checksum, width, height, and MIME type.
- Rights status, owner, license reference, provenance, reviewer, and review time.
- Quality status, required and measured long edge, decision, reviewer, and review time.
- English and German alt-text drafts with an explicit metadata review flag.
- A separate explicit approval for private Admin intake.

The default is `hold`. Missing rights, missing quality approval, unreviewed localized metadata, or missing intake approval each produce a distinct blocked reason.

## Deterministic target contract

All candidates target the private folder path `Veylune/Catalog Intake/WP-CAT-04-DRAFT-50`. The target file name is derived from SKU, media role, record ID, and the first 12 SHA-256 characters. The full source checksum and target name produce a unique idempotency key.

The dry run also checks current Shopware Media names. An existing private target becomes an idempotent no-op candidate; a matching public name becomes a fail-closed collision. No product association or cover is permitted in 4.1B.

## Wayfair comparison

Wayfair's live discovery experience connects product cards, product destinations, and visible commerce information to a data-backed catalog. The corresponding Veylune requirement is not another hard-coded image selector: it is a reproducible Admin media identity with controlled metadata and provenance. This package establishes that identity layer without presenting unapproved imagery as launch-ready.

Reference captured from `https://www.wayfair.com/` on 2026-09-20.

## Commands and evidence

- Create the review template once: `php bin/console veylune:media:intake-plan --write-review-template`
- Repeat the dry run: `php bin/console veylune:media:intake-plan`
- Verify the saved plan: `php bin/veylune-phase-4-media-intake-audit`
- Review input: `config/veylune-phase-4-media-intake-review.json`
- JSON plan: `reports/catalog/phase-4-1b-media-intake-plan.json`
- CSV plan: `reports/catalog/phase-4-1b-media-intake-plan.csv`

## Exact next package

Phase 4.1C: implement the private Admin media importer and rollback manifest. It must remain idempotent, accept only records that pass every 4.1B review gate, store imported media as private, and refuse product associations or Admin covers. With the current review file, its expected result is a safe refusal with zero imports.
