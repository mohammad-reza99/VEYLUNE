# Veylune Stage A3 - Non-destructive Residual Review

**Review date:** 2026-08-14  
**Environment:** DDEV development  
**Mode:** Read-only evidence and classification  
**State:** Complete; deletion gate remains closed

## Problem

Stage A2 removed the exact demo-residue allowlist. Records previously marked
`review` still require refreshed ownership and dependency evidence before they
can be retained, reclassified, or proposed for removal.

## Evidence

### Property dictionary

The legacy reviewed `Material` group (`a67c...6e32`) and its three reviewed
options are no longer present. The active governed dictionary contains nine
property groups. `Veylune Material` contains ten options and covers all 50
governed draft products. No active governed product depends on the missing
legacy IDs.

This is reconciled as migration/drift, not as proof of an authorized Stage A3
deletion. The immutable quarantine inventory remains the historical capture.

### CMS pages

All ten reviewed CMS page IDs still exist and have zero direct category or
sales-channel-home assignments. Their content roles include:

- legal pages: imprint, privacy, terms, payment/shipping, rescission;
- form pages: contact, newsletter, revocation request;
- default product-list and product-detail layouts.

Zero direct assignment does not prove that these system/editorial templates
have no operational value. Their classification remains `review-retain`.

### Media

Current media count is 77. Direct/indirect evidence found:

| Reference surface | Distinct media |
| --- | ---: |
| Product media | 12 |
| Category media | 21 |
| CMS slot JSON configuration | 1 |

The remaining media set mixes Veylune uploads, theme assets, supplier/product
candidates, and payment-plugin assets. Folder membership and zero direct DAL
references are insufficient proof of safe deletion. The old scalar count of 83
review candidates is stale after catalog/media work and Stage A2 cleanup.

## Risk

- Removing a system CMS page can break a later legal, form, listing, or PDP
  assignment even when the current direct reference count is zero.
- Removing plugin-owned media can break payment presentation after activation.
- Removing visually unassigned Veylune media can destroy source assets needed
  for catalog or editorial production.
- Treating historical IDs as current source of truth can hide migration drift.

## Options

1. Delete every currently unreferenced record. Rejected: evidence is
   insufficient and lifecycle ownership is mixed.
2. Retain everything permanently. Rejected: it preserves ambiguity and storage
   debt without an ownership model.
3. Keep the deletion gate closed, add owner/origin/reference classification,
   then present a new exact candidate manifest. Recommended.

## Council Positions

- Gary Friedman and Delia Lachance: preserve Veylune editorial source assets
  until their visual role and replacement are explicit.
- Lisa Chi and Kristina Halvorson: CMS/media retention needs a commerce purpose,
  content owner, locale, and lifecycle state.
- Brad Frost and W3C WAI: default layouts and media must be reviewed against the
  active component and accessibility contracts before removal.
- Martin Fowler: do not build an automated deletion subsystem before ownership
  evidence exists; keep the next mechanism small and reversible.
- Eric Evans: distinguish asset, media record, CMS template, product media, and
  supplier source media in the domain language.
- Shopware Platform Authority and Fabien Potencier: use DAL lifecycle operations
  only after complete extension/plugin ownership scans.
- Martin Kleppmann: current database state is the operational source of truth;
  historical inventory remains audit evidence, not an executable manifest.
- Product Search Authority and Anders Hejlsberg: removal candidates need explicit
  typed states so discoverability inputs cannot silently disappear.
- Sam Newman: supplier-origin assets need supplier/batch attribution before
  cleanup or migration.
- Kent Beck: every future candidate needs a reference regression and restore
  checkpoint.
- Troy Hunt: legal pages and payment assets are fail-closed retain candidates.
- Steve Souders: optimize delivered media, not merely database row count.
- Ronny Kohavi: no customer experiment is justified for this infrastructure
  decision; use operational evidence.
- Charity Majors and Jez Humble: record candidate IDs, owner, reason, restore
  point, execution result, and post-cleanup governance evidence.

## Conflict

Storage cleanliness favors early deletion, while brand, legal, integration, and
recovery safety favor retention. Evidence does not yet isolate a safe removal
set, so reversibility and integrity win this gate.

## Recommendation

Close Stage A3 without deletion. Reclassify all present CMS review pages and all
unresolved media as `review-retain` until an ownership ledger identifies an
exact, dependency-free removal set. Mark the absent legacy Material IDs as
`historical-drift-reconciled` rather than silently removing them from history.

## Implementation Impact

- No database mutation.
- No storefront/runtime behavior change.
- No historical quarantine inventory rewrite.
- A future media/CMS ownership ledger must include ID, origin, owner, purpose,
  locale, direct references, indirect references, lifecycle state, and decision.

## Acceptance Criteria

- Stage A2 allowlisted residue remains zero.
- Governed catalog remains 50 products with full taxonomy/property coverage.
- All reviewed CMS IDs and their current assignments are documented.
- Media direct and CMS-config references are measured.
- No review-classified record is deleted.
- Full Veylune governance verification passes.

## Founder Decision

No destructive decision is requested in Stage A3. Founder approval will be
required only if a future exact removal manifest is produced.

## Next Step

Proceed to Stage B1: Supplier Readiness and first-ten Level-3 product execution.
Media/CMS ownership classification continues as a non-blocking governance
ledger and cannot authorize deletion by itself.
