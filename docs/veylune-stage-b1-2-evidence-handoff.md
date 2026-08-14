# Veylune Stage B1.2 - Supplier Evidence Handoff Pack

**Date:** 2026-08-14  
**State:** Handoff pack complete; awaiting external source completion  
**Runtime impact:** None

## Problem

All ten Level 3 candidates are blocked because no acceptable supplier source
package exists locally. Suppliers and internal source owners need one precise,
auditable submission format rather than free-form email or inferred facts.

## Evidence

Repository inspection found only planning manifests, legacy commerce records,
storefront copy, and Phase 9 mock data. None establishes legal supplier
identity, current commercial authority, specifications, or media rights.

## Risk

Free-form intake creates missing units, ambiguous price authority, stale
availability, invalid rights, duplicate supplier mappings, and unsupported
material claims.

## Options

1. Continue through informal documents. Rejected.
2. Populate fields from existing product names and prices. Rejected.
3. Provide a ten-row handoff sheet plus a strict machine-readable schema.
   Implemented.

## Council Positions

Product, brand, content, design, merchandising, and UX require evidence before
claims or presentation. Architecture, domain, Shopware, data, type, and
integration roles require stable identifiers, units, source references, and
explicit boundaries. Quality, security, performance, observability, and
delivery roles require validation, fail-closed defaults, and traceable owners.
Experimentation is not applicable before a valid commercial offer exists.

## Conflict

Supplier convenience favors fewer fields; Level 3 integrity requires enough
information to prove identity, sellability, physical truth, and rights. The
handoff keeps one flat operational sheet while the JSON Schema defines the
strict accepted contract.

## Recommendation

Send the CSV to the accountable supplier/source owner. Require source document
references for every authority field. Convert returned rows into schema-valid
records, then run intake audit and Level 1 evaluation product by product.

## Implementation Impact

- Added a prefilled ten-product CSV handoff sheet.
- Added a JSON Schema for accepted evidence records.
- No catalog or runtime mutation.

## Acceptance Criteria

- The sheet contains exactly the fixed ten cohort SKUs.
- Every row exposes identity, pricing, availability, specifications, rights,
  materials, ownership, and review fields.
- The schema rejects synthetic supplier identifiers and incomplete objects.
- Existing intake remains zero accepted until returned evidence passes review.

## Founder Decision

Founder approval is required only for supplier substitution or an
internal-source authority. Completing the handoff template does not authorize
publication.

## Next Step

Return at least the Aurelia and Calma evidence rows plus referenced source
documents. Then perform the first Level 1 evaluation and produce exact gaps.
