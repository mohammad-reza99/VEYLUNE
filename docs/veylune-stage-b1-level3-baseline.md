# Veylune Stage B1 - First 10 Level 3 Baseline and Intake Gate

**Date:** 2026-08-14  
**State:** Baseline complete; supplier intake blocked on external evidence  
**Runtime impact:** None

## Problem

The approved ten-product Level 3 cohort existed only as a planning register,
while the database now contains both four legacy runtime products and a newer
50-product governed draft catalog. The cohort needed deterministic identity,
state reconciliation, and an explicit evidence gate before enrichment.

## Evidence

The fixed cohort is now machine-readable in
`Resources/config/level3_cohort.php` and audited by
`veylune:catalog:level3-baseline-audit`.

```text
BASELINE PASS
Cohort products: 10
Legacy remediation: 4
Governed drafts: 6
Level 3 ready: 0
Supplier/evidence blockers: 8
```

The four legacy remediation records are Aurelia, Calma, Atelier, and Nocturne.
The governed drafts are Tectona, Meridian Sculpture, Strata, Tactile Rug,
Lumen, and Stillwater. Every draft remains inactive with stock and visibility
at zero.

The eight blocked evidence domains are supplier master, supplier SKU mapping,
approved source batch, pricing authority, availability authority,
specification pack, media-rights schedule, and material evidence.

## Risk

- Mock suppliers would create false provenance.
- Legacy `active` state is not Level 3 evidence.
- Similar names across legacy and draft namespaces can cause identity merges.
- Enrichment before exact supplier facts creates unsupported premium claims.

## Options

1. Reuse Phase 9 mock suppliers. Rejected: simulation data is not commercial
   evidence.
2. Infer supplier and specifications from product names/images. Rejected:
   unverifiable and unsafe.
3. Freeze the exact cohort, preserve fail-closed draft state, and open a
   supplier evidence intake gate. Approved recommendation.

## Council Positions

- Product/brand/merchandising: the cohort matches the approved quick-win and
  assortment sequence, but premium claims require real product proof.
- Content/design/accessibility: copy, alt text, and media treatment must follow
  rights-cleared factual source packages.
- Fowler/Evans/Shopware/Potencier: keep legacy remediation and governed drafts
  distinct; use a small read-only command and standard service wiring.
- Kleppmann/Newman: provenance, stable supplier mapping, batch identity, and
  idempotent reconciliation precede enrichment.
- Search/type architecture: canonical SKU and typed cohort lanes prevent silent
  identity substitution.
- Beck/Hunt/Souders: keep drafts non-public; do not trade safety for apparent
  completeness or optimize media before rights and purpose are known.
- Kohavi/Majors/Humble: this is an operational gate, not an experiment; preserve
  auditable state, diagnostics, rollback, and automated verification.

## Conflict

Speed favors filling fields immediately. Integrity and brand trust require
supplier-backed facts. Integrity wins; the cohort is selected but not promoted.

## Recommendation

Accept the B1 baseline and move to B1.1 supplier intake. Do not connect mock
suppliers, activate drafts, add visibility, or claim Level 3 readiness.

## Implementation Impact

- Added a fixed, reviewable ten-product cohort register.
- Added a read-only baseline audit command.
- Added no product, price, inventory, visibility, or publication writes.

## Acceptance Criteria

- Exactly ten unique SKUs resolve to exactly one database product each.
- Four products are in the legacy-remediation lane.
- Six products are governed drafts and remain fail-closed.
- Missing external evidence is explicit.
- Level 3 ready count remains zero until per-product evidence passes.
- Full Veylune governance verification passes.

## Founder Decision

No publication decision is requested. Founder input is required if supplier
source packages are supplied, substituted, or an internal-source authority is
proposed.

## Next Step

B1.1 Supplier Evidence Intake: collect and validate legal supplier/source,
supplier SKU, approved batch, pricing, availability, specifications, media
rights, and material evidence for each of the ten products. Then run the first
controlled Level 1 readiness evaluation.
