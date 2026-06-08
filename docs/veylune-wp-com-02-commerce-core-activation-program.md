# VEYLUNE STUDIO

## WP-COM-02 — Commerce Core Activation Program

**Status:** Audit and activation planning
**Audit date:** 7 June 2026
**Implementation authority:** None
**Objective:** Restore complete commerce without weakening existing governance

---

## 1. Executive Decision

Veylune does not lack basic commerce templates. Shopware and the Veylune theme
already contain product-detail, cart, checkout, account, order-history, and
related presentation components. The current environment also reports three
active payment methods and two active shipping methods.

Commerce Core is unavailable because its route families are deliberately
classified as `activation_pending` and denied fail-closed by the canonical
storefront ingress policy.

That denial is currently correct. The required product publication,
sellability, quality, readiness, and exposure contracts exist, but they are not
yet one authoritative runtime gate for every commerce route. The catalog also
contains only two runtime-approved products, while the full readiness audit
places all current products below complete Level 1 compliance.

The fastest safe restoration path is:

1. complete the activation gate and ten Level 3 products;
2. activate governed PDPs;
3. activate cart and checkout as one tested transaction release;
4. activate governed search;
5. activate account/order support, then decide separately whether wishlist
   adds enough value to activate.

Removing a `404` denial is not commerce activation. Commerce is restored only
when every public route consumes the same publication, sellability, readiness,
and exposure authority.

---

# A. Commerce Core Inventory

## 2. Current Capability Status

| Capability | Status | Current blocker |
| --- | --- | --- |
| PDP | Native/theme implementation present; public route returns `404` | Products route is `activation_pending`; no request-time Level 3/publication/sellability mediation; catalog contract incomplete |
| Search | Native route exists; public route returns `404` | Search is `activation_pending`; no approved indexing eligibility, governed search projection, or runtime verification |
| Cart | Native/theme implementation present; public route returns `404` | Cart is `activation_pending`; sellability is not enforced as an authoritative line-item admission gate |
| Checkout | Native/theme implementation present; confirm route returns `404` | Checkout is `activation_pending`; cart is inactive; payment, shipping, tax, returns, order, and failure paths are not end-to-end approved |
| Account | Native/theme login, profile, address, and order templates present; login returns `404` | Account is `activation_pending`; customer account/privacy/support policy and runtime verification are incomplete |
| Wishlist | Shopware capability exists but configuration is disabled; public route returns `404` | Wishlist is `activation_pending`; business decision and runtime verification are absent |

## 3. Supporting Runtime Facts

| Fact | Observed state | Meaning |
| --- | --- | --- |
| Catalog records | 20 | Sixteen are demo/quarantine records and four are Veylune records |
| Active Shopware products | 4 | Shopware activation does not imply publication, readiness, sellability, or exposure |
| Runtime exposure approvals | 2 | Aurelia and Calma only |
| Contract-complete Level 3 products | 0 in the latest readiness audit | Existing runtime approval is legacy/partial evidence, not complete production readiness |
| Active payment methods | 3 | Availability alone does not prove configuration, settlement, refund, failure, or legal readiness |
| Active shipping methods | 2 | Availability alone does not prove freight, lead-time, access, damage, or returns operations |
| Wishlist setting | Disabled | No accidental wishlist activation exists |

## 4. What Prevents Complete Commerce

Four independent gaps combine:

1. **Route authorization gap:** commerce route families are intentionally
   denied.
2. **Runtime authority gap:** static governance contracts are not yet enforced
   consistently on PDP, search, cart, checkout, account, and withdrawal paths.
3. **Catalog readiness gap:** no current product has demonstrated the full
   cumulative Level 0-to-Level 3 contract.
4. **Operating proof gap:** payment, shipping, returns, customer support,
   order handling, supplier/stock ownership, and rollback have not passed a
   production-like end-to-end journey.

---

# B. Route Ownership Audit

## 5. Route Ownership States

| Surface | State | Owner | Recorded prerequisites |
| --- | --- | --- | --- |
| Products | `activation_pending` | `product_publication_policy` | Product publication policy; catalog quality gate; PDP runtime verification |
| Search | `activation_pending` | `search_architecture` | Search governance; indexing readiness; search runtime verification |
| Cart | `activation_pending` | `native_commerce` | Sellability policy; cart runtime verification |
| Checkout | `activation_pending` | `native_commerce` | Cart activation; payment/shipping readiness; checkout runtime verification |
| Account | `activation_pending` | `native_commerce` | Customer account policy; account runtime verification |
| Wishlist | `activation_pending` | `commerce_policy` | Wishlist business decision; wishlist runtime verification |

## 6. Denial Chain

The current request sequence is:

```text
Canonical storefront request
→ IdentityIngressAllowlistSubscriber
→ StorefrontRouteOwnershipPolicy lookup
→ route family is activation_pending
→ fail-closed 404 response
→ native Shopware controller never becomes publicly usable
```

The allowlist is not the only blocker. Its enforcement order is:

1. deny public Store API and Admin API ingress;
2. deny any route family marked `activation_pending`;
3. allow only explicitly public or governed-public routes;
4. deny unclassified routes.

Adding a route name to the allowlist while leaving its ownership state pending
would not authorize it. Changing the ownership state without implementing its
prerequisites would violate WP-06.

## 7. Governance Mechanisms

### Route ownership policy

Controls whether an entire route family may become public.

### Identity ingress allowlist

Enforces deny-by-default behavior at the canonical public storefront.

### Publication contract

Only `publication_state=published` is publicly eligible. Shopware activity,
stock, visibility, supplier status, sellability, and import approval do not
imply publication.

### Sellability contract

Requires approved pricing, media, bilingual content, taxonomy, supplier facts,
lead time, delivery, returns, and compliance. Publication does not imply
sellability.

### Product readiness and quality contracts

Require complete identity, commerce, physical, content, media, material,
taxonomy, SEO, supplier, reviewer, and rollback facts before publication.

### Product exposure service

Currently filters the four known Veylune SKUs for homepage collection surfaces.
It checks registry approval, Veylune SKU shape, Shopware activity,
availability, price, cover, category, material, and exact surface approval.

It is not currently a complete Commerce Core mediator:

- it is wired to the homepage page-load event only;
- it does not govern direct PDP route admission;
- it does not govern search indexing/results;
- it does not govern cart line-item admission or revalidation;
- it does not govern checkout;
- its material keys still include the legacy `fabric` mapping rather than the
  final canonical contract;
- its two approved records do not satisfy the complete readiness program.

## 8. Required Authority Model

Activation must use one existing decision chain:

```text
Level 0 identity exists
AND Level 1 commerce is complete
AND Level 2 discovery is complete
AND publication_state = published
AND sellability_status = sellable
AND Shopware active/available/visible
AND quality gate = approved
AND material confidence = documented or verified
AND exact surface exposure = approved
AND no blocking or withdrawal state exists
→ Level 3 surface eligibility
```

No route may substitute native Shopware flags for this chain.

---

# C. PDP Activation Audit

## 9. Can PDP Be Activated Today?

**No.**

The two homepage products are runtime-approved by a narrow registry but remain
incomplete against the final readiness contract. Direct product links currently
return `404`, which is safer than publishing incomplete commerce facts.

## 10. PDP Activation Prerequisites

### Product prerequisites

Every public PDP must have:

- immutable product identity and canonical Veylune SKU;
- accepted supplier/source identity or explicit governed `internal_source`;
- EN/DE approved names, descriptions, SEO, slugs, and routes;
- approved positive price, tax, currency, and sellability;
- current availability, stock policy, lead time, delivery, returns, and
  shipping constraints;
- complete dimensions, weight, assembly, care, safety, and compliance;
- five or more approved rights-cleared images with EN/DE alt text;
- governed materials, finish, color, confidence, and evidence;
- approved department, category, product type, and room relationships;
- resolved consultation mode;
- publication, quality, exposure, reviewer, and rollback records.

### Route prerequisites

- product route state is explicitly changed from `activation_pending` only
  after authorization;
- direct retrieval is mediated by Level 3 eligibility;
- unpublished, suspended, archived, non-sellable, unavailable, recalled,
  rights-restricted, or mismatched products fail closed;
- canonical EN/DE routes and redirect behavior are verified;
- sitemap and indexing include only eligible PDPs;
- withdrawal removes route access and transactional admission immediately.

### Presentation prerequisites

The approved PDP hierarchy must answer:

- product identity and price;
- selected variant/configuration;
- availability and delivery;
- dimensions and fit;
- materials, finish, variation, and care;
- installation/assembly and returns;
- supplier/manufacturer where meaningful;
- consultation trigger;
- exact purchase CTA state.

## 11. PDP Exit Gate

PDP activation is safe when:

1. at least ten products are fully Level 3 for product exposure;
2. every direct PDP request is checked against the same runtime authority;
3. all ten pass rendered EN/DE tests;
4. each CTA correctly resolves to add, configure, consult, notify, or no action;
5. suspended-product and evidence-revocation tests return fail-closed behavior;
6. no demo/quarantine product is directly retrievable.

Ten products are not a technical route requirement. They are the minimum
supplier-trust milestone established by WP-COM-01.

---

# D. Search Activation Audit

## 12. Can Search Safely Return Products Today?

**No.**

Search is not safe merely because Shopware can index active products. The
current database contains demo/quarantine records, rejected products, and
legacy-approved products that do not satisfy the final contract.

## 13. Missing Dependencies

- authoritative product eligibility projection for indexing;
- removal or exclusion of demo/quarantine records;
- Level 3 product and exact search-exposure approval;
- canonical product type, category, room, material, finish, manufacturer, and
  controlled alias data;
- EN/DE query and result parity;
- canonical URL and duplicate review;
- price and availability freshness;
- immediate removal on suspension, unavailability, rights expiry, recall, or
  evidence revocation;
- approved sort and facet publication;
- autocomplete eligibility;
- no-result behavior and unresolved-demand logging;
- search runtime, performance, and leakage tests.

## 14. Required Search Rule

```text
Searchable set
= products that are Level 3
AND published
AND sellable
AND active/available/visible
AND approved for search exposure
AND not blocked, withdrawn, expired, or mismatched
```

Level 2 may support internal search testing. It may not enter public results.

## 15. Search Sequencing

Search follows PDP and transaction activation under the approved Commerce UX
implementation order. Public search must never return a product whose PDP is
unavailable or whose CTA cannot resolve safely.

---

# E. Cart and Checkout Audit

## 16. Can Cart Exist Before Catalog Readiness?

Technically, an empty cart route can exist. Operationally, Veylune should not
activate it first.

Reasons:

- it adds no supplier trust without sellable products;
- the header already demonstrates the damage caused by advertising an
  unavailable bag;
- cart line-item admission must revalidate sellability, price, availability,
  configuration, and consultation state;
- stale or suspended products must be removed or blocked with an accountable
  customer message;
- the cart must group delivery expectations correctly.

Cart activation requires at least one fully sellable Level 3 PDP and is best
released with checkout so the customer is not led into another dead end.

## 17. Can Checkout Exist Before Supplier Onboarding?

It can only exist safely if Veylune is the accountable merchant and every
product uses a governed `internal_source` or another complete fulfillment
model.

Under the current assumptions and catalog state, **no**:

- no supplier contracts exist;
- current product source and fulfillment ownership are incomplete;
- supplier-specific lead time and inventory authority are unresolved;
- payment/shipping method activity has not been converted into approved
  operating readiness;
- order acceptance, cancellation, refund, damage, return, and customer support
  paths are unproven.

Supplier onboarding itself is not the abstract requirement. Accountable stock,
fulfillment, legal representation, and settlement ownership are. A real
supplier contract is one way to establish them; governed internal ownership is
another.

## 18. Cart Admission Gate

At add-to-cart and every cart reload, verify:

- product remains Level 3 for commerce;
- publication is `published`;
- sellability is `sellable`;
- selected variant/configuration is valid;
- price and tax are current;
- availability and quantity policy permit purchase;
- consultation mode does not require a blocked direct purchase;
- delivery and returns classes exist;
- supplier/source remains active;
- no withdrawal or blocking event exists.

## 19. Checkout Release Gate

Before public checkout:

- cart admission is authoritative;
- guest checkout decision is approved;
- payment authorization, capture, failure, cancellation, refund, and
  reconciliation are tested;
- shipping eligibility, price, lead time, freight/access, split shipment, and
  delivery communication are tested;
- tax and invoice behavior is verified;
- terms, withdrawal, privacy, returns, and customer communication are approved;
- order confirmation and status are correct in EN/DE;
- stock/inventory reservation behavior is understood;
- failed orders and duplicate submissions are controlled;
- customer support and incident owners are named;
- a complete test order, cancellation, refund, and return pass;
- rollback can stop new orders without losing existing order support.

---

# F. Account and Wishlist Audit

## 20. Account Timing

Account should activate **with or immediately after checkout**, not before
Commerce Core.

Reasons:

- an empty account provides little customer or supplier value;
- pre-commerce account collection creates privacy and support obligations
  without an operating benefit;
- guest checkout should not be blocked by account readiness;
- account becomes valuable when it contains addresses, orders, invoices,
  delivery status, returns, and support history.

Account activation requires:

- customer registration and authentication policy;
- privacy, consent, retention, deletion, and security handling;
- address and locale behavior;
- order-history accuracy;
- password reset and account recovery;
- guest-order association decision;
- support ownership;
- EN/DE runtime and transactional-email verification.

## 21. Wishlist Timing

Wishlist is not Commerce Core and should be last.

Current state:

- route is `activation_pending`;
- configuration is disabled;
- no approved business decision exists.

Activate only after:

- PDP and search are stable;
- saved-product behavior for withdrawn, unavailable, or changed products is
  defined;
- privacy and anonymous/authenticated persistence are approved;
- supplier exposure metrics will not be misrepresented;
- runtime verification passes.

Wishlist may remain disabled without blocking supplier readiness above 70.

---

# G. Supplier Trust Impact

## 22. Capability Value

Scores below are directional supplier-readiness estimates, not measured
conversion results.

| Activation | Trust value | Expected readiness effect | Reason |
| --- | --- | ---: | --- |
| Governed PDP | Very high | +8 to +10 | Proves Veylune can represent product identity, materials, price, delivery, supplier, and consultation accurately |
| Cart alone | Low to negative | 0 to +2 | Has little value without complete checkout; another dead end would reduce trust |
| Cart + Checkout | Very high | +8 to +11 | Proves the platform can convert demand into accountable orders and payment |
| Search | High | +4 to +6 | Proves findability, assortment structure, and controlled supplier visibility |
| Account + Order Support | Medium-high | +3 to +5 | Proves post-purchase continuity, order access, and customer care |
| Wishlist | Low | 0 to +1 | Useful retention feature but weak supplier-acquisition proof |

## 23. Highest-Value Activation

**PDP is the highest-value first route activation.**

It closes the most visible current contradiction: homepage products are shown
but cannot be opened. It also proves the Material Authority, readiness,
supplier presentation, delivery, consultation, and exposure systems on the
actual product surface.

**The highest-value completed milestone is PDP plus cart/checkout.**

Suppliers ultimately need proof that presentation becomes an order, not only
that a product page renders.

---

# H. Activation Sequence

## 24. Step 1 — Complete the Commerce Activation Gate

Do not change route states yet.

Required outcomes:

- remediate the existing four Veylune products;
- produce enough approved products to reach at least ten full Level 3 records;
- establish one authoritative runtime eligibility decision using the existing
  publication, sellability, quality, readiness, exposure, and withdrawal
  contracts;
- exclude all demo/quarantine records;
- complete payment, shipping, tax, returns, legal, support, and rollback
  operating evidence;
- remove public demo/future-state residue and unavailable commerce actions;
- assign owners for product governance, commerce, customer support, finance,
  fulfillment, and incidents;
- pass a private end-to-end test journey.

**Exit condition:** route activation can consume a tested authority rather than
native Shopware flags.

## 25. Step 2 — Activate Governed PDP

Change only the Products route family after explicit authorization.

Required behavior:

- eligible PDPs return `200`;
- ineligible, unpublished, suspended, archived, demo, or mismatched products
  return fail-closed;
- CTA state follows sellability, availability, configuration, and consultation;
- EN/DE routes, metadata, sitemap, canonical URLs, media, delivery, returns,
  and supplier/manufacturer presentation pass;
- product withdrawal is immediate.

**Exit condition:** ten eligible PDPs work with zero direct-access leakage.

## 26. Step 3 — Activate Cart and Checkout Together

Follow the approved Commerce UX Stage 3 order.

Required behavior:

- add-to-cart accepts only currently sellable Level 3 products;
- cart revalidates every line item;
- checkout supports the approved guest/account model;
- payment, shipping, tax, terms, order confirmation, cancellation, refund,
  return, support, and failure paths pass;
- consultation-required products cannot bypass their mode;
- order rollback stops new commerce while preserving existing order care.

**Exit condition:** a production-like order, cancellation, refund, and return
complete in EN/DE.

## 27. Step 4 — Activate Governed Search

Follow the approved Commerce UX Stage 4 order.

Required behavior:

- only eligible PDPs enter the index;
- categories, rooms, materials, finishes, collections, manufacturers, and
  controlled aliases resolve correctly;
- autocomplete and results never leak ineligible products;
- prices and availability are current;
- suspension and evidence revocation remove products promptly;
- no-result and performance behavior pass.

**Exit condition:** every public result resolves to a valid governed PDP and
safe purchase/consultation state.

## 28. Step 5 — Activate Account; Review Wishlist Separately

Activate account/order support after transaction proof.

Required behavior:

- registration, login, recovery, privacy, addresses, order history, invoices,
  delivery state, returns, and support pass;
- guest checkout remains available if approved;
- post-purchase communication is consistent with account data.

Wishlist remains disabled until its separate business decision and runtime
verification pass.

**Exit condition:** customers can manage completed commerce; wishlist is either
explicitly approved or intentionally deferred.

---

# I. Risk Matrix

## 29. Activation Risks

| Risk | Timing | Severity | Consequence | Mitigation |
| --- | --- | --- | --- | --- |
| Native active products bypass governance | Too early | Critical | Demo, rejected, or incomplete products become public | Central Level 3 runtime authority; deny all non-approved retrieval |
| Search leaks unavailable products | Too early | Critical | Broken PDPs and false assortment | Index only approved eligibility projection; withdrawal synchronization |
| Cart accepts non-sellable items | Too early | Critical | Wrong price, unavailable stock, invalid configuration | Revalidate on add, load, checkout, and order placement |
| Checkout accepts orders without fulfillment ownership | Too early | Critical | Cancellations, legal exposure, supplier conflict, customer harm | Require governed source, stock, delivery, returns, and support owner |
| Payment/shipping methods are assumed ready because active | Too early | Critical | Failed payment, incorrect shipping, refund/reconciliation defects | End-to-end authorization, capture, failure, refund, and delivery tests |
| Account collects data without service value | Too early | High | Privacy/support burden and weak trust | Activate with post-purchase utility and approved data policy |
| Wishlist misrepresents demand | Too early | Medium | Suppliers receive unreliable interest signals | Keep disabled until definitions and analytics are approved |
| Route denial remains after prerequisites pass | Too late | High | Supplier trust remains at prototype level; customer interest is wasted | Time-box activation review after each exit gate |
| Catalog production waits for every future supplier | Too late | High | Commerce proof never occurs | Use governed internal-source or pilot products where ownership is complete |
| Search waits for 1000 products | Too late | Medium | Poor direct discovery and supplier visibility | Activate at ten credible products once result quality is useful |
| Account waits until large order volume | Too late | Medium | Weak post-purchase support | Activate after transaction proof, before scaled acquisition |
| Overbuilding wishlist delays core | Too late | Medium | Effort diverted from purchase journey | Defer wishlist without blocking the 70+ target |

## 30. Balance Rule

Activate a capability when:

- its existing prerequisites are complete;
- its runtime authority is fail-closed;
- it has enough real inventory or transactions to provide honest utility;
- its failure and rollback paths are tested;
- keeping it blocked now causes more trust damage than exposing it.

Do not wait for 1000 products. Do not activate on architectural intent alone.

---

# J. Final Recommendation

## 31. Fastest Path From 42 to 70+

The shortest defensible path is not six independent route flips. It is one
governed transaction chain.

| Milestone | Directional readiness |
| --- | ---: |
| Current supplier readiness | 42 |
| Activation gate complete: ten Level 3 products, identity cleanup, terms, fulfillment and support proof | 50–53 |
| Ten governed PDPs active | 59–63 |
| Cart and checkout pass complete order/refund/return journey | 68–73 |
| Governed search active | 72–77 |
| Account/order support active | 75–81 |
| Wishlist | Optional; no material effect required |

These estimates assume all WP-COM-01 supplier milestones outside route
activation are also completed, especially supplier terms, fulfillment
ownership, accountable intake, corporate trust signals, and pilot evidence.
Route activation alone cannot produce a credible 70.

## 32. Immediate Decision

Commerce routes should remain blocked today.

The next authorized work should complete Step 1, not modify the ingress
allowlist. The first route released should be PDP, after ten products are
contract-complete and direct retrieval is mediated by the final Level 3
authority.

Cart and checkout should then launch together as a tested transaction system.
Search follows once every indexed result has a valid PDP and CTA. Account
follows transaction proof. Wishlist may remain disabled.

## 33. Acceptance Standard

Commerce Core is restored when:

- ten or more products satisfy the full cumulative Level 3 contract;
- PDP, cart, checkout, search, and account consume the same authoritative
  readiness and withdrawal decisions;
- no demo, rejected, unpublished, non-sellable, unavailable, or unapproved
  product is publicly retrievable, searchable, cartable, or orderable;
- payment, shipping, tax, returns, customer support, and rollback pass;
- public navigation contains no advertised `404` commerce action;
- suppliers can observe a complete path from governed presentation through
  purchase and post-purchase care.

That state can raise supplier readiness above 70 without weakening Material
Authority, publication independence, exposure governance, or fail-closed
storefront ownership.

---

# Deliverable Summary

## A. Commerce Core Inventory

All six capabilities have native foundations. All are unavailable publicly;
wishlist is additionally disabled.

## B. Route Ownership Audit

`StorefrontRouteOwnershipPolicy` marks the route families pending, and
`IdentityIngressAllowlistSubscriber` denies them before native handling.

## C. PDP Audit

PDP is the first and highest-value activation, but requires complete Level 3
records and request-time mediation.

## D. Search Audit

Search cannot safely expose the current Shopware index. It requires an
eligibility-controlled index and valid PDP destinations.

## E. Cart and Checkout Audit

Cart should not launch as an empty or isolated route. Checkout requires
accountable fulfillment, payment, shipping, returns, and support, whether
products are supplier-backed or internally sourced.

## F. Account and Wishlist Audit

Account follows transaction proof. Wishlist remains optional and last.

## G. Supplier Trust Impact

PDP provides the strongest first trust gain. Completed cart/checkout provides
the strongest proof of commercial maturity.

## H. Activation Sequence

Activation gate → PDP → cart/checkout → search → account; wishlist separately.

## I. Risk Matrix

Early activation risks customer and supplier harm. Late activation preserves a
prototype signal. Exit-gated releases balance both.

## J. Final Recommendation

Keep the current route blocks until Step 1 passes. Restore one governed
transaction chain to move supplier readiness from 42 to approximately 75–81.
