# Veylune Stage B2 - Aurelia and Calma Source Reconciliation

**Date:** 2026-08-14  
**State:** Hard stop; no supplier package accepted

## Problem

Aurelia and Calma were the first supplier-evidence targets. Neither record had
supplier attribution in Shopware; both use `VEYLUNE STUDIO` as manufacturer.

## Evidence

Exact-name web research found multiple unrelated products named Aurelia but no
source connecting any of them to Veylune SKU `VLS-SOF-001`, its three media
records, or a rights chain. Aurelia is therefore `blocked_no_verifiable_source`.

Calma media includes the local filename `scott-keramik-cattelan-italia-3`.
Cattelan Italia's official Scott Keramik page describes a marble-effect ceramic
top with a steel base, while Veylune SKU `VLS-SOF-003` is presented as a
travertine table. The source does not establish Veylune resale authorization or
media rights. Calma is therefore `blocked_identity_conflict`.

Official reference:
https://www.cattelanitalia.com/en/products/68179469-05FE-4F6B-99B5-7FC3FF9FC169?c=15

## Risk

- False manufacturer/material attribution.
- Unverified image rights and resale authority.
- Customer deception and incorrect care guidance.
- Search, taxonomy, and editorial claims built on the wrong product identity.

## Options

1. Map Calma to Cattelan Italia automatically. Rejected: exact model/variant,
   commercial relationship, price authority, and rights are unproven.
2. Keep both products moving through Level 1. Rejected.
3. Hard-stop both records and request Founder decisions/source evidence.
   Implemented.

## Council Positions

Brand, merchandising, content, UX, accessibility, domain, Shopware, data,
integration, search, quality, security, observability, and delivery roles agree
that identity and rights conflicts block readiness. Performance and
experimentation cannot override product truth. Architecture recommends a small
explicit reconciliation record rather than speculative supplier mapping.

## Conflict

Legacy runtime continuity conflicts with identity integrity. This review does
not silently mutate commercial exposure; it escalates the active Calma record
to the Founder runtime decision gate.

## Recommendation

- Quarantine Calma from runtime exposure until identity, material, supplier,
  price, and media rights are resolved.
- Require an attributable supplier/source package for Aurelia or approve a
  replacement candidate through acquisition governance.
- Do not reuse unrelated web products merely because their names match.

## Acceptance Criteria

- Intake states record the two distinct blockers.
- No external result is imported as accepted evidence.
- Level 1 remains zero.
- Founder explicitly decides Calma runtime quarantine and any candidate
  substitution.

## Founder Decision Required

1. Approve or reject immediate Calma runtime quarantine.
2. Provide Aurelia supplier/source evidence or authorize candidate replacement.

## Next Step

After the Founder decision, execute the reversible Calma exposure change if
approved and obtain source packages for the retained candidates.
