# VEYLUNE STUDIO

## WP-COM-ARCH-01A - First Supplier Minimum Viable Commerce Architecture

**Target:** One supplier, 10 product families, 50-100 sellable variants
**Platform:** Shopware 6.7.10.0
**Parent authority:** WP-COM-ARCH-01
**Objective:** Launch safely with the smallest architecture that does not create
a migration dead end

---

# 1. Decision

Veylune does not need the full enterprise control plane before Supplier #1.
It does need the correct commerce aggregate, durable source identity, a
repeatable import, an executable publication check, and immediate suspension.

The minimum architecture is:

```text
One governed supplier
-> one canonical CSV format
-> dry-run and idempotent import
-> native Shopware family parents and variant children
-> native media, category, property, price, stock and delivery data
-> one publication eligibility service
-> native PDP, listing, cart and checkout
-> batch snapshot and rollback
```

Only four custom persistence concerns are required:

1. supplier master;
2. supplier-product identity mapping;
3. import batch and snapshot;
4. publication decision.

Everything else should use Shopware native data or bounded scalar custom fields
until scale justifies dedicated services.

# 2. What Must Not Be Simplified

These enterprise decisions apply from the first supplier:

- a Shopware parent product represents one product family;
- child products represent sellable variants;
- only SKU-defining choices create variants;
- every child has an immutable Veylune SKU;
- supplier SKU remains source identity, never public identity;
- supplier status, import success, Shopware `active`, stock and visibility do
  not independently publish a product;
- supplier-owned fields and Veylune-owned fields remain distinct;
- unknown variant, taxonomy, rights, compliance or fulfillment data fails
  closed;
- no product is public before an explicit publication decision;
- suspension removes PDP, listing, sitemap, search if enabled, and new cart
  admission;
- rollback cannot overwrite a later manual correction silently.

These rules preserve the enterprise upgrade path. The implementation around
them can remain small.

---

# 3. Minimum Data Architecture

## 3.1 Native Shopware data

Use Shopware directly for:

| Concern | Storage |
| --- | --- |
| Product family | Parent `product` |
| Sellable variant | Child `product` with `parent_id` |
| Veylune SKU | `product_number` |
| Supplier brand | `manufacturer` |
| Variant dimensions | Native configurator options |
| Descriptive attributes | Native properties |
| Navigation | Department and product-type categories |
| Price and tax | Variant price and tax |
| Stock | Variant stock |
| Delivery range | Native delivery time |
| Dimensions and weight | Native product fields |
| Product copy and SEO | Product translations |
| Gallery and cover | Native media/product-media |
| Public channel exposure | Native visibility |
| PDP URL | Native SEO URL |
| Cart and checkout | Native Shopware commerce |

The parent has no independent stock and cannot be the purchasable line item.
One approved child is the default/main variant.

## 3.2 Required custom records

### A. `veylune_supplier`

One row is sufficient for Supplier #1.

Required fields:

```text
id
supplier_code
legal_name
display_name
status
contract_status
contract_effective_at
contract_expires_at
primary_contact_name
primary_contact_email
source_profile_version
default_currency
default_stock_policy
default_return_class
default_warranty_class
created_at
updated_at
```

The first release does not need separate brand, contact and contract tables.
The field names and supplier UUID must remain compatible with later extraction
into those enterprise entities.

### B. `veylune_supplier_product_mapping`

One row per sellable variant.

Required fields:

```text
id
supplier_id
supplier_family_key
supplier_sku
manufacturer_sku
shopware_product_id
veylune_sku
source_revision
source_hash
first_seen_at
last_seen_at
source_state
```

Unique constraints:

- `veylune_sku`;
- `shopware_product_id`;
- `(supplier_id, supplier_sku)`;
- `(supplier_id, supplier_family_key, supplier_sku)`.

### C. `veylune_import_batch`

One row per import attempt, with the canonical manifest and pre-commit snapshot
stored as immutable files under a governed private path.

Required fields:

```text
id
batch_code
supplier_id
schema_version
source_filename
source_hash
state
manifest_path
snapshot_path
report_path
created_by
approved_by
created_at
committed_at
rolled_back_at
```

For 50-100 variants, a separate operation table is not mandatory. The batch
report must contain item-level create/update/no-op/conflict results and
before/after hashes. Introduce `veylune_import_operation` before Supplier #2 or
before automated supplier updates.

### D. `veylune_publication_decision`

One current decision per family, with append-only superseding decisions.

Required fields:

```text
id
family_product_id
state
decision
reason
checks_hash
decided_by
decided_at
supersedes_id
```

States:

```text
draft
approved
published
suspended
archived
```

The first release does not need a separate row for every domain gate.
The publication service calculates domain checks and stores their immutable
result in the batch report and `checks_hash`. Add domain-specific approval
records when multiple teams or suppliers require independent workflow.

## 3.3 Required custom fields

Use scalar product custom fields only:

```text
veylune_family_code
veylune_supplier_id
veylune_source_batch_id
veylune_publication_state
veylune_readiness_level
veylune_asset_status
veylune_content_status
veylune_commerce_status
veylune_compliance_status
veylune_media_rights_status
veylune_sellability_status
veylune_stock_policy
veylune_delivery_class
veylune_return_class
veylune_warranty_class
veylune_consultation_mode
veylune_listing_mode
veylune_public_eligibility
veylune_eligibility_reason_code
```

Use media custom fields for the first supplier:

```text
veylune_asset_slot
veylune_rights_owner
veylune_rights_scope
veylune_rights_expires_at
veylune_asset_approval_status
veylune_alt_text_status
```

Do not store contact data, variant matrices, asset arrays, or import history in
product custom fields.

---

# 4. Minimum Variant Rules

## 4.1 SKU

```text
Family:  VLS-{DEP}-{6 digits}
Variant: VLS-{DEP}-{6 digits}-{3 digits}
```

The suffix is sequential and carries no material, size, finish or color
meaning. Option labels may change without changing the SKU.

## 4.2 Axes

Create a variant axis only when the option identifies a distinct supplier SKU,
price, stock position, fulfillment fact, physical specification, or customer
selection required to purchase.

For Supplier #1:

- maximum three configurator axes per family;
- maximum 100 children per family;
- supplier provides the list of valid combinations;
- never generate a blind Cartesian product;
- every child option tuple must be unique;
- descriptive material, room, style and care facts remain properties/content.

## 4.3 Listing

Use one family card by default.

- `listing_mode=family`;
- one default eligible child determines cover and displayed price;
- display `From` only when eligible child prices differ;
- keep all size, finish and color choices inside PDP;
- no variant fan-out for first launch unless a family cannot be understood as
  one card without it.

This avoids duplicate listings and removes the need for a family-aware search
decorator before launch.

---

# 5. Minimum Import Architecture

## 5.1 One input format

Supplier #1 must be mapped into one Veylune-controlled UTF-8 CSV template.
Do not build a generic mapping UI, supplier API, webhook or multiple adapters.

Use two CSV files:

```text
families.csv
variants.csv
```

Assets may be uploaded manually to Shopware and referenced by stable filename
or media UUID in the CSV.

Required family columns:

```text
supplier_family_key
family_sku
name_en
name_de
description_en
description_de
department
product_type
brand
listing_mode
consultation_mode
```

Required variant columns:

```text
supplier_family_key
supplier_sku
manufacturer_sku
veylune_sku
option_1_group
option_1_value
option_2_group
option_2_value
option_3_group
option_3_value
gross_price
tax_code
stock
stock_policy
delivery_time
delivery_class
return_class
warranty_class
width
height
length
weight
compliance_status
source_revision
```

## 5.2 Commands

Only four commands are required:

```text
veylune:supplier:import:dry-run <directory>
veylune:supplier:import:commit <batch>
veylune:supplier:import:audit <batch>
veylune:supplier:import:rollback <batch>
```

The command may use DAL repositories directly for this volume. A Sync API
transport abstraction is not required inside the same Shopware application.
The importer must still produce deterministic payloads so the write transport
can later move to Sync API without changing the canonical manifest.

## 5.3 Required behavior

Dry-run must validate:

- supplier and contract are active;
- exact CSV schema and encoding;
- unique family code, Veylune SKU and supplier SKU;
- existing mapping conflicts;
- approved category/property values only;
- unique and complete variant option tuples;
- valid price, tax, stock policy, delivery, return and warranty data;
- required dimensions;
- required compliance status;
- required media package and rights status;
- no requested public activation or visibility;
- exact create/update/no-op/conflict plan.

Commit must:

- require the approved dry-run source hash;
- write parents before children;
- use deterministic IDs;
- write in chunks of 25;
- remain idempotent;
- leave all new records private;
- create a pre-commit snapshot;
- write a complete item report;
- run a post-commit audit.

Rollback must:

- delete never-published records created by the batch;
- restore updated records from the snapshot;
- refuse fields changed after the batch;
- never delete SKU or supplier mapping history for a published product;
- finish with the same private-exposure audit used after import.

---

# 6. Minimum Media Architecture

Do not build automated supplier media ingestion for Supplier #1.

Use Shopware Media Manager with:

- one private folder for Supplier #1 originals;
- one stable naming convention;
- native thumbnails;
- media custom fields for slot, rights and approval;
- manual product and variant assignment;
- a command/audit that verifies required slots and rights metadata.

Minimum family package:

- hero or studio cover;
- lifestyle/context;
- detail;
- material;
- scale or dimensions image.

Variant-specific media is required only where material, finish or color would
otherwise be represented inaccurately. Shared parent media may be inherited
when it is true for every child.

Publication blockers:

- fewer than five approved family images;
- missing cover;
- missing or expired rights;
- missing EN/DE alt text;
- placeholder or watermarked media;
- selector option without an accurate image where visual accuracy is material.

Malware automation, checksum deduplication, remote download, asset entities,
rendition queues and rights-expiry jobs can wait. Rights expiry must still be
reviewed manually before publication and recorded in the launch runbook.

---

# 7. Minimum Publication and Runtime Gate

Implement one `ProductPublicationEligibilityService`.

Input: parent family ID.
Output:

```text
eligible: bool
reasons: list<string>
default_variant_id: string|null
eligible_variant_ids: list<string>
checks_hash: string
```

Required family checks:

- supplier active and contract active;
- publication decision is `published`;
- parent content, SEO, category and properties complete in EN/DE;
- approved media package;
- valid media rights;
- at least one eligible child;
- no unresolved import conflict.

Required child checks:

- unique supplier mapping and Veylune SKU;
- active source state;
- valid option tuple;
- positive valid price and tax;
- approved stock policy;
- delivery, return and warranty classes;
- dimensions and weight where required;
- applicable compliance approved;
- sellability status `sellable`;
- media is accurate for selected visual options.

The service is the single authority for:

- PDP request admission;
- category/listing result filtering;
- visibility publication;
- SEO URL and sitemap eligibility;
- add-to-cart admission;
- final checkout validation.

Publish command:

```text
veylune:product:publish <family-sku>
```

It recalculates eligibility, writes the publication decision, activates the
required native product state, assigns visibility, and audits the public route.

Suspend command:

```text
veylune:product:suspend <family-sku> --reason=<code>
```

It first makes eligibility false, then removes public visibility and cart
admission, invalidates the relevant cache, and records the decision. It must
not delete the product or order history.

---

# 8. Minimum Public Storefront Scope

Supplier #1 launch requires:

- family PDP with native variant selectors;
- one stable category/listing route for each populated department or product
  type;
- native cart;
- native checkout;
- payment, shipping, tax, order, cancellation and refund tests;
- canonical family URLs;
- EN/DE behavior if both locales remain a launch requirement;
- direct-access denial for ineligible products.

Use manual Shopware category assignment and manual product slider/rail
selection. Ten families do not justify a rail population engine.

Public search can remain denied for the first launch. Navigation and curated
homepage/category links are sufficient for 10 families. If search is enabled,
it becomes Must Have to filter by the same eligibility service and return one
result per family.

---

# 9. Priority Classification

## 9.1 Must Have

These block first supplier launch.

### Supplier and identity

- `veylune_supplier`;
- `veylune_supplier_product_mapping`;
- active contract and operational contact;
- immutable Veylune family and variant SKUs;
- uniqueness constraints and retired-SKU protection.

### Product model

- native parent family and child variants;
- controlled configurator axes and valid combinations;
- family-default listing mode;
- variant price, tax, stock policy, delivery and dimensions;
- approved categories and properties only.

### Import and rollback

- one canonical CSV format;
- dry-run, commit, audit and rollback commands;
- deterministic IDs and idempotent re-import;
- source hash and batch record;
- pre-commit snapshot and item-level report;
- conflict refusal instead of overwrite.

### Media and content

- native Shopware media;
- minimum five-image package;
- variant-accurate visual media;
- rights owner/scope/expiry and approval status;
- EN/DE product content, SEO and alt text where those locales are public;
- dimensions, care and compliance content.

### Publication and commerce

- `veylune_publication_decision`;
- one executable eligibility service;
- explicit publish and suspend commands;
- PDP, listing, sitemap, cart and checkout using the same decision;
- direct URL and add-to-cart denial for ineligible products;
- payment, shipping, tax, cancellation, refund and order tests;
- rollback and suspension runbooks.

## 9.2 Should Have

These should be completed for operational stability, but a tightly controlled
first launch can proceed with documented manual operation.

- admin read-only supplier/batch/family status view;
- import resume after a failed chunk;
- media audit command;
- automatic contract and rights-expiry warnings;
- source-missing variant detection;
- scheduled stock/lead-time CSV update;
- family-level readiness report with reason codes;
- curated related products;
- room and collection properties for future discovery;
- launch monitoring for PDP errors, cart rejection and checkout failures;
- a second approver for publication;
- staging rehearsal using the exact production batch.

## 9.3 Can Wait

These are enterprise-scale capabilities, not first-supplier launch blockers.

- multiple supplier adapters;
- supplier API pulls, webhooks and Sync API integration transport;
- separate supplier brand, contact and contract entities;
- field-level import operation table;
- automated media download, scanning, checksum deduplication and asset entity;
- asynchronous media rendition pipeline beyond native thumbnails;
- automated compliance document lifecycle;
- domain-specific approval entities and workflow UI;
- dynamic product groups for homepage rails;
- merchandising assignment entity and supplier-diversity rules;
- public search and advanced filters;
- variant fan-out in listings;
- search engine tuning for thousands of variants;
- multi-warehouse and supplier-specific shipment orchestration;
- automated feed and paid-advertising integration;
- supplier portal;
- analytics-driven sorting;
- event-driven cache and index orchestration;
- dedicated operations dashboard and exception queues.

---

# 10. Lean Implementation Sequence

## Slice 1 - Data and Variant Foundation

Deliver:

- supplier and mapping tables;
- SKU reservation/validation;
- native family/variant payload contract;
- required custom fields;
- Calma reference family with at least two axes and invalid combinations.

Exit:

- parent is non-sellable;
- all children have unique source and Veylune identity;
- no product is public.

## Slice 2 - CSV Import and Rollback

Deliver:

- schema;
- dry-run, commit, audit and rollback;
- batch table, immutable manifest, snapshot and item report.

Exit:

- the same batch can be committed twice without duplication;
- injected failure leaves a recoverable batch;
- rollback restores the pre-batch private catalog.

## Slice 3 - Media and PDP Contract

Deliver:

- media custom fields and audit;
- five-slot minimum;
- family/variant PDP view;
- selectors, dimensions, delivery, care and compliance presentation.

Exit:

- all 10 families render correctly in private preview;
- no inaccurate inherited variant media;
- EN/DE checks pass.

## Slice 4 - Publication and Commerce

Deliver:

- publication decision table;
- eligibility service;
- publish/suspend commands;
- governed PDP/listing/cart/checkout admission;
- route ownership transition for only required surfaces.

Exit:

- one family can be published and suspended end to end;
- suspension removes public and cart eligibility immediately;
- payment-to-refund test passes.

## Slice 5 - Supplier #1 Launch Batch

Deliver:

- all 10 families and 50-100 variants;
- final media/content/compliance review;
- exact production dry-run and snapshot;
- controlled family-by-family publication.

Exit:

- zero draft or ineligible leakage;
- every visible family has an eligible default variant;
- every purchasable variant passes checkout;
- rollback and suspension are exercised before launch approval.

---

# 11. Complexity Budget

For Supplier #1, enforce these limits:

| Area | Limit |
| --- | ---: |
| Custom DAL entities/tables | 4 |
| Import formats | 1 canonical format, 2 CSV files |
| Import commands | 4 |
| Publication commands | 2 |
| Variant axes per family | 3 |
| Variants per family | 100 |
| Listing cards per family | 1 |
| Public supplier count | 1 |
| Automatic rail engines | 0 |
| Public advanced filters | 0 |
| Supplier APIs/webhooks | 0 |

Crossing a limit requires an explicit architecture review rather than an
incremental workaround.

# 12. Launch Verdict

The smallest safe first-supplier architecture is not a reduced version of the
current flat draft seeder. It is a thin production layer around Shopware native
variants.

**Must Have:** correct family/variant model, durable supplier identity,
idempotent CSV import, media rights, one eligibility service, publication and
suspension, native commerce, and rollback.

**Should Have:** operational reporting, warnings, resumability, stock updates
and monitoring.

**Can Wait:** generic multi-supplier workflows, API ingestion, full asset
pipeline, dynamic merchandising, public search/filtering and enterprise
automation.

This scope is sufficient for one supplier and 10 families without discarding
any enterprise decision from WP-COM-ARCH-01. The four minimal records can be
expanded into the full supplier, import, asset and approval model without
changing Shopware product IDs, Veylune SKUs, supplier mappings, canonical
manifest semantics, or publication authority.
