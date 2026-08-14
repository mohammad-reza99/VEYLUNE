# Veylune Stage B1.1 - Supplier Evidence Intake

**Date:** 2026-08-14  
**State:** Intake mechanism complete; 10 products blocked on external evidence  
**Runtime impact:** None

## Problem

The fixed Level 3 cohort requires real supplier provenance and commercial,
technical, rights, and material evidence. Repository-wide inspection found
planning data, legacy storefront records, and mock simulation suppliers, but no
acceptable source package for any cohort product.

## Evidence

```text
INTAKE BASELINE PASS
Records: 10
Accepted: 0
Blocked external evidence: 10
Synthetic suppliers: forbidden
```

Each intake record requires supplier identity and SKU, source batch, pricing
and availability authority references, specification pack, media-rights
schedule, material evidence, source owner, review timestamp, and reviewer.

## Risk

- Treating draft-manifest prices as approved supplier pricing.
- Treating existing images as proof of channel/territory/crop rights.
- Reusing Phase 9 mock suppliers as commercial provenance.
- Inferring specifications or materials from names and imagery.

## Options

1. Manufacture complete-looking data. Rejected.
2. Import planning facts as supplier evidence. Rejected.
3. Preserve explicit blocked records and validate real source packages when
   supplied. Implemented.

## Council Positions

- Brand, merchandising, content, and UX require factual premium claims and
  rights-cleared assets.
- Domain, data, Shopware, Symfony, integration, and type authorities require
  stable supplier/SKU/batch identity and explicit source references.
- Search requires evidenced attributes before indexing or faceting.
- Quality, security, performance, observability, and delivery authorities
  require fail-closed state, synthetic-data rejection, audit output, and no
  runtime mutation.
- Experimentation has no role until a real offer and measurable customer
  outcome exist.

## Conflict

The implementation can be completed locally; supplier truth cannot. Product
speed does not authorize fabricated provenance, so integrity wins.

## Recommendation

Keep all ten records at `blocked_external_evidence`. Accept a record only after
all required source references are present and reviewed. Never promote mock or
placeholder suppliers.

## Implementation Impact

- Added a ten-record fail-closed supplier intake register.
- Added an audit command enforcing cohort identity, evidence completeness,
  allowed states, and synthetic-supplier rejection.
- No product, supplier, pricing, stock, visibility, or publication writes.

## Acceptance Criteria

- Exactly ten intake records match the fixed cohort and order.
- Synthetic suppliers are rejected.
- An accepted record cannot omit any required evidence field.
- Current honest result is zero accepted and ten blocked.
- Baseline and full governance verification pass.

## Founder Decision

No Founder publication decision is requested. Founder or an accountable source
owner must provide/approve real supplier packages or explicitly authorize an
internal-source model backed by equivalent evidence.

## Next Step

External evidence handoff, followed by the first Level 1 readiness evaluation.
The evaluation begins with Aurelia and Calma, then Atelier and Nocturne, and
then the six governed drafts. Until evidence arrives, Level 3 remains zero.
