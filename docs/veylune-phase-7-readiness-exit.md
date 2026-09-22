# Veylune Phase 7 Commercial Readiness Gate

Status date: 2026-09-22

## Decision

Phase 7 technical preparation is implemented. Commercial activation remains
fail-closed until real supplier, media, price, availability, operations, legal,
localization, customer-order, and founder evidence is accepted.

No missing business fact may be replaced by generated, placeholder, inferred,
or convenience data. A configured Shopware payment or shipping method is
inventory evidence only; it is not a sandbox sign-off.

## Canonical launch candidate cohort

The launch candidate cohort is now aligned with the current 50-product Admin
catalog and contains the first ten governed furniture products:

1. `VLS-FUR-000001` Aurelia Modular Sofa
2. `VLS-FUR-000002` Liora Curved Sofa
3. `VLS-FUR-000003` Oris Leather Lounge Chair
4. `VLS-FUR-000004` Selene Oak Lounge Chair
5. `VLS-FUR-000005` Edda Dining Chair
6. `VLS-FUR-000006` Noma Metal Dining Chair
7. `VLS-FUR-000007` Forma Desk Chair
8. `VLS-FUR-000008` Talo Counter Stool
9. `VLS-FUR-000009` Stillwater Oak Bench
10. `VLS-FUR-000010` Elara Travertine Coffee Table

This is a candidate cohort, not founder approval or permission to publish.

## Implemented technical controls

- Cohort, supplier intake, handoff CSV, and runtime exposure registry share the
  same ten canonical product identities.
- Supplier intake derives its identities from the cohort, preventing future
  hand-edited drift.
- The runtime registry contains no approved product and uses canonical material
  keys for upholstery fabric, leather, wood, metal, and travertine.
- Every candidate remains inactive, stock-zero, and without public visibility
  while evidence is incomplete.
- A machine-readable operational contract owns twelve independent launch gates.
- The Phase 7 audit checks catalog identity, supplier evidence completeness,
  gallery depth, fail-closed state, operational evidence, payment/shipping/tax
  inventory, and runtime activation authority.
- The audit is part of the global governance determinism suite.

## Evidence still required

For every candidate product:

- supplier identity and supplier SKU;
- approved source batch;
- price and availability authority;
- specification pack and material evidence;
- at least five rights-cleared gallery assets;
- source owner, reviewer, and review timestamp.

For the operating business:

- shipping configuration sign-off;
- tax configuration sign-off;
- payment sandbox sign-off;
- transactional email sign-off;
- returns and customer-support sign-off;
- legal-entity and policy sign-off;
- EN/DE localization approval;
- governed customer and order fixtures;
- founder launch approval.

## Commands

Run the non-mutating readiness audit:

```bash
ddev exec php bin/console veylune:catalog:phase7-readiness-audit
```

Require the real Phase 7 exit condition:

```bash
ddev exec php bin/console veylune:catalog:phase7-readiness-audit --require-exit
```

The first command passes when the technical gate is internally consistent and
safe. The second command intentionally fails until all evidence is accepted
and the controlled runtime cohort is active and visible.

## Activation sequence after evidence arrives

1. Fill the supplier handoff CSV from authoritative documents.
2. Review and transfer accepted records into the supplier intake registry.
3. Import and approve at least five governed media assets per candidate.
4. Record each operational gate as accepted with a real evidence reference.
5. Run the readiness audit and review its JSON report.
6. Activate products and sales-channel visibility in one controlled change.
7. Run the audit with `--require-exit`, the commerce E2E suite, and full
   governance before opening public product, cart, or checkout routes.

## Exit rule

Phase 7 is complete only when ten products have accepted supplier evidence,
ten products have a minimum five-image approved gallery, all twelve operational
gates are accepted, the runtime activation gate allows publication, and all ten
products are active and visible through the governed sales channel.
