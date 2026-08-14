# Veylune Stage B2 - Aurelia Source Resolution and Runtime Quarantine

**Date:** 2026-08-14  
**Founder direction:** Execute source resolution  
**State:** Complete; identity conflict quarantined

## Problem and Evidence

Aurelia had no attributable supplier or rights chain. Visual inspection of its
three assigned media files proved that they depict three different sofa
designs: a floral wood-frame sofa, a curved sofa with integrated side tables
and a `MENTE FURNITURE` watermark, and a bowl-shaped wood-frame sofa. Exact-name
and watermark searches did not establish one official product identity or a
Veylune supplier/SKU relationship.

## Decision and Action

The record was classified `blocked_identity_conflict`. Before mutation, the
database snapshot `veylune-pre-aurelia-quarantine-20260814` was created.

The DAL quarantine changed only exposure:

```text
active: 1 -> 0
visibility rows: 1 -> 0
stock preserved: 100
```

Product, price, content, taxonomy, and media records were retained for forensic
review and rollback. None of the images is accepted as rights-cleared evidence.

## Council Position

Brand, content, merchandising, accessibility, domain, Shopware, data,
integration, search, quality, security, observability, and delivery positions
all require one attributable product identity and media-rights chain. Runtime
continuity cannot override conflicting product imagery.

## Acceptance Criteria

- Aurelia active state and visibility are zero.
- Stock and evidence records are preserved.
- Intake and cohort states reflect quarantine.
- Sitemap and full governance verification pass.

## Rollback

`ddev snapshot restore veylune-pre-aurelia-quarantine-20260814`

Rollback does not resolve identity or rights and must not be used to republish
the current media set.

## Next Step

Create a governed replacement strategy for Aurelia and Calma using candidates
with exact supplier identity, stable supplier SKU, technical specification,
commercial authority, and media rights before they enter Level 1.
