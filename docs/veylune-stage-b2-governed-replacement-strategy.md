# Veylune Stage B2 - Governed Replacement Strategy

**Date:** 2026-08-14  
**State:** Official-source shortlist complete; Founder selection required

## Problem

Aurelia and Calma were quarantined because their product and media identities
were not trustworthy. Replacement candidates must preserve the approved sofa
and travertine-table assortment roles while introducing exact manufacturer
identity, stable item numbers, technical evidence, and a viable supplier path.

## Evidence

### Aurelia primary recommendation

Ethnicraft N701 Modular Sofa, 3-seater Moss, manufacturer item `20256`.
Ethnicraft's 2026 commercial catalog supplies a 210 x 91 x 76 cm configuration,
material composition, modular-system context, and stable item number. A separate
manufacturer declaration of compliance is available.

### Aurelia alternate

Muuto Connect Modular Sofa. Muuto supplies an official product page, order
guide, module dimensions/item-number process, material construction, and EU
compliance declarations. It remains alternate because the final configuration
and resulting item numbers have not been frozen.

### Calma primary recommendation

GUBI Epic Dining Table, Elliptical, Neutral White, 240 x 120 cm, manufacturer
item `10059274`. GUBI identifies it as an Italian white-travertine table and
publishes current specifications. This matches the approved travertine dining
table role without reusing the conflicted Scott Keramik media.

## Risk

- Official product pages do not grant Veylune resale or media rights.
- Public retail prices are not wholesale pricing authority.
- Availability differs by territory and variant.
- Reusing the retired Veylune content/media would reintroduce identity drift.

## Options

1. Restore the old records. Rejected.
2. Select lookalike products without stable manufacturer identity. Rejected.
3. Select the primary official-source candidates, then obtain authorized
   supplier terms and media licenses before import. Recommended.

## Council Positions

- Brand, editorial, merchandising, discovery, and content roles prefer the N701
  and Epic candidates because their material/product identities are coherent.
- Design-system and accessibility roles require new rights-cleared media and
  accurate alt/content rather than inherited assets.
- Architecture, domain, Shopware, Symfony, data, type, and integration roles
  require new supplier mappings and batches; the quarantined SKUs must not be
  silently repurposed.
- Search, quality, security, performance, observability, and delivery roles
  require exact variants, source references, rights, fail-closed import, and
  rollback evidence.
- Experimentation does not choose product truth; Founder and merchandising do.

## Conflict

Ethnicraft offers a fixed, intake-friendly item quickly. Muuto provides deeper
configuration flexibility but creates greater SKU/configuration complexity.
For the first Level 3 cohort, the simpler N701 primary candidate is preferred.

## Recommendation

1. Select Ethnicraft N701 item `20256` as the Aurelia-role primary candidate.
2. Select GUBI Epic item `10059274` as the Calma-role primary candidate.
3. Keep Muuto Connect as the alternate sofa candidate.
4. Create new Veylune SKUs only after supplier acceptance; do not overwrite or
   reactivate the quarantined records.

## Acceptance Criteria

- Founder selects or rejects each primary candidate.
- An authorized supplier/reseller supplies territory-specific commercial terms.
- Exact variant, availability, delivery, returns, and media license are written.
- New source batch and supplier SKU mappings pass duplicate checks.
- Level 1 evaluation passes before any product write or preview.

## Founder Decision

Approve or reject:

- Aurelia role: Ethnicraft N701 `20256` primary; Muuto Connect alternate.
- Calma role: GUBI Epic `10059274` primary.

## Next Step

After approval, issue supplier outreach for quotation, availability, delivery,
returns, exact media license, and data-pack rights. Then create a controlled
source batch and run B3 Level 1 acceptance.
