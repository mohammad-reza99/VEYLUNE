# VEYLUNE Product Readiness & Attribution Program

## Decision

No future product may enter the Veylune Revenue Engine until it has reached
**Level 3: Exposure Ready** for the specific surface that will consume it.

Shopware activation, stock, availability, publication, sellability, discovery
attribution, exposure approval, and Founder Selection are independent
authorities. No one state implies another.

The current catalog contains 20 records:

- 4 Veylune product records
- 16 Shopware demo, parent, or variant records
- 2 products currently approved by the exposure registry
- 18 products rejected, unavailable, or ineligible for Veylune exposure

The current two approvals are not sufficient evidence of production readiness.
Under this program, all 20 records remain at Level 0 until the cumulative
requirements below are complete. Aurelia and Calma have useful Level 2-4
evidence, but they do not yet pass the Level 1 contract.

## Review Basis

The exact titled source documents named in the brief are not present as
standalone files in this repository. This program therefore uses their
available implementation evidence:

- WP-07 production catalog, taxonomy, readiness, quality, sellability, media,
  content, and launch-simulation contracts
- identity ingress and canonical storefront boundary documentation
- the current material dictionary and exposure service
- WP-IMP-06's stated population result of 20 reviewed, 2 eligible, and 18
  rejected or unavailable
- the live June 6, 2026 Shopware product, translation, media, category,
  property, price, availability, visibility, and route records

## Governing Principles

1. **Facts precede copy.** Structured facts are authoritative; editorial prose
   cannot supply a missing material, room, category, or commerce fact.
2. **Readiness is cumulative.** A product cannot skip a lower level.
3. **Readiness is fail-closed.** Missing, expired, disputed, or unmapped data
   blocks advancement.
4. **Attribution is evidence-based.** SEO demand and merchandising convenience
   are not attribution evidence.
5. **Exposure is surface-specific.** Approval for one category, room, or
   collection does not approve another.
6. **Founder Selection is human authority.** It is never inferred from sales,
   recency, supplier status, or completeness.
7. **Every decision is attributable.** Approval, rejection, revocation, actor,
   evidence, timestamp, and review date are retained.

# Part I: Catalog Readiness Model

## Level 0: Catalog Exists

Purpose: establish an internally controlled record without implying commerce or
public eligibility.

Required:

- immutable internal product ID
- working name
- source system and source record ID
- supplier ID and supplier SKU, or explicit `internal_source`
- lifecycle state: `draft`, `review`, `approved`, `published`, `suspended`, or
  `archived`
- record owner
- source batch
- created and updated timestamps
- demo/quarantine classification

Level 0 records may not appear in search, navigation, recommendations,
collections, room pages, category destinations, feeds, campaigns, or the
Founder Selection.

## Level 1: Commerce Ready

Purpose: prove that Veylune can identify, price, fulfill, support, and legally
represent the product.

All Level 0 requirements plus:

- canonical Veylune SKU in `VLS-{department}-{six digits}` format
- unique supplier ID and supplier SKU pair
- manufacturer
- EN and DE product name and factual description
- reserved canonical slug and PDP route for each supported locale
- positive gross price, net price, currency, tax class, and pricing approval
- explicit sellability status
- availability source, stock policy, and last availability verification
- lead time, delivery class, returns class, and shipping constraints
- width, height, depth, weight, units, and assembly requirements
- care guidance and applicable compliance/safety facts
- at least five approved images: primary, alternate, detail, scale, and context
- image rights owner, usage scope, expiry if applicable, EN/DE alt text, crop,
  and quality approval for every image
- active supplier and approved source batch
- rollback target and commerce reviewer

`active=true`, stock, or a positive price does not independently satisfy Level
1.

## Level 2: Discovery Ready

Purpose: make the product accurately retrievable without degrading Veylune's
taxonomy or material authority.

All Level 1 requirements plus:

- canonical department, primary category, and product type
- secondary categories only where independent customer intent exists
- primary material, material family, finish, color, and material confidence
- all materially significant secondary materials
- at least one governed room attribution or explicit `room_not_applicable`
- room relevance evidence, confidence, approver, and review date
- EN/DE SEO title and meta description
- canonical URL and duplicate/redirect review
- controlled-vocabulary validation for material, finish, color, room, and style
- category lifecycle is `published`
- no supplier-created public taxonomy or unmapped public property values

Level 2 permits governed retrieval and internal discovery testing. It does not
permit public merchandising exposure.

## Level 3: Exposure Ready

Purpose: authorize a product for a named public Revenue Engine surface.

All Level 2 requirements plus:

- product publication state is `published`
- sellability is `sellable`
- Shopware product is active, available, and visible in the intended sales
  channel
- quality gate approved for content, media, taxonomy, properties, commerce,
  supplier, and governance
- material confidence is `verified` or `documented`
- exposure approval exists for each exact category, room, collection, campaign,
  feed, recommendation, or homepage surface
- exposure start, optional end, approver, rationale, and rollback target
- consultation mode is resolved
- no unresolved blocking issue, expired evidence, rights restriction, recall,
  supplier suspension, or registry mismatch
- rendered PDP and destination-page checks pass in EN and DE

Exposure approval is scoped. A product approved for `living_room` and
`new_arrivals` is not automatically approved for `dining_room` or
`founder_selection`.

## Level 4: Founder Selection Eligible

Purpose: establish the pool from which the founder may make a deliberate
selection.

All Level 3 requirements plus:

- documented Veylune point of view: material, proportion, craft, spatial
  relevance, or collectible identity
- complete product story without contradicting structured facts
- stable supply or an explicit limited/made-to-order operating model
- consultation path complete where required
- no provisional material, room, media, or commerce evidence
- founder review status: `pending`, `selected`, `declined`, or `revoked`
- founder decision, rationale, effective date, and review/expiry date

Level 4 means **eligible for founder decision**, not automatically selected.
Only `founder_status=selected` permits Founder Selection exposure.

# Part II: Product Data Contract

## Mandatory Contract

| Domain | Mandatory fields | First required |
| --- | --- | --- |
| Identity | `product_id`, `name_en`, `name_de`, `slug_en`, `slug_de`, `pdp_route_en`, `pdp_route_de`, `veylune_sku`, `sku_state`, `supplier_id`, `supplier_sku`, `manufacturer`, `source_batch` | L0-L1 |
| Lifecycle | `publication_state`, `sellability_status`, `shopware_active`, `sales_channel_visibility`, `availability_status`, `availability_source`, `availability_checked_at` | L0-L3 |
| Commerce | `gross_price`, `net_price`, `currency`, `tax_class`, `price_approved_by`, `stock_policy`, `lead_time`, `delivery_class`, `returns_class`, `shipping_constraints` | L1 |
| Physical | `width`, `height`, `depth`, `weight`, `dimension_unit`, `weight_unit`, `assembly_requirements`, `care_guidance`, `compliance_status` | L1 |
| Content | `description_en`, `description_de`, `seo_title_en`, `seo_title_de`, `meta_description_en`, `meta_description_de`, `content_approved_by` | L1-L2 |
| Media | `media_count`, `primary_image_id`, `media_slots`, `rights_owner`, `rights_scope`, `rights_expiry`, `alt_text_en`, `alt_text_de`, `crop_profile`, `quality_status`, `media_approved_by` | L1 |
| Material | `primary_material`, `material_family`, `secondary_materials`, `finish`, `color`, `material_confidence`, `material_evidence`, `material_approved_by`, `material_reviewed_at` | L2 |
| Category | `department`, `primary_category`, `secondary_categories`, `product_type`, `taxonomy_approved_by`, `taxonomy_reviewed_at` | L2 |
| Room | `room_attributions`, `room_relevance_basis`, `room_confidence`, `room_approved_by`, `room_reviewed_at`, `room_review_due_at` | L2 |
| Collection | `collection_attributions`, `collection_type`, `collection_status`, `collection_approved_by`, `collection_start_at`, `collection_end_at` | L3 when assigned |
| Consultation | `consultation_mode`, `consultation_triggers`, `consultation_note_en`, `consultation_note_de`, `consultation_owner` | L3 |
| Exposure | `approved_surfaces`, `exposure_status`, `exposure_approved_by`, `exposure_start_at`, `exposure_end_at`, `exposure_reason`, `exposure_rollback_target` | L3 |
| Founder | `founder_eligibility`, `founder_status`, `founder_rationale`, `founder_decided_by`, `founder_decided_at`, `founder_review_due_at` | L4 |
| Governance | `readiness_level`, `blocking_reason_codes`, `quality_status`, `record_owner`, `reviewer`, `last_audited_at`, `next_review_at`, `rollback_target` | All |

## Contract Rules

- Empty strings, placeholder copy, inherited demo data, and unknown values do
  not count as present.
- Every enum value comes from a versioned registry.
- Every many-to-many attribution stores status, evidence, actor, and dates on
  the relationship, not only on the product.
- Secondary categories, rooms, materials, and collections are arrays of
  governed relationship records, not free text.
- The validator returns stable reason codes such as
  `MISSING_DE_DESCRIPTION`, `UNMAPPED_MATERIAL`, `MEDIA_BELOW_MINIMUM`,
  `ROOM_EVIDENCE_EXPIRED`, and `EXPOSURE_SURFACE_NOT_APPROVED`.
- The product's readiness level is the highest fully completed cumulative
  level. Partial completion never rounds upward.

# Part III: Material Attribution Program

## Material Model

Every product requires:

- one `primary_material`
- one `material_family`
- zero or more `secondary_materials`
- one or more finishes where applicable
- evidence and confidence for every material attribution

The primary material is the material that most defines customer expectation,
visible area, structural identity, care, value, or product naming. Record every
secondary material that is visible, structurally important, care-relevant,
safety-relevant, or at least 10% of the finished object by meaningful
composition.

## Normalized Naming

Use stable lowercase keys and localized labels:

| Canonical key | Family | EN label | DE label |
| --- | --- | --- | --- |
| `travertine` | `stone` | Travertine | Travertin |
| `marble` | `stone` | Marble | Marmor |
| `stone` | `stone` | Stone | Stein |
| `wood` | `wood` | Wood | Holz |
| `metal` | `metal` | Metal | Metall |
| `upholstery_fabric` | `fabric` | Upholstery Fabric | Polsterstoff |
| `wool` | `fabric` | Wool | Wolle |
| `leather` | `leather` | Leather | Leder |
| `glass` | `glass` | Glass | Glas |
| `ceramic` | `ceramic` | Ceramic | Keramik |

Specific material and family are separate facts. `travertine` must not be
replaced by the broader `stone`; it is stored as
`primary_material=travertine`, `material_family=stone`.

Aliases are supplier-input mappings only. Public records use canonical keys.
The current runtime alias `fabric` must migrate to `upholstery_fabric` to match
the controlled dictionary.

## Confidence Levels

| Confidence | Evidence | Public use |
| --- | --- | --- |
| `verified` | Veylune inspection, test, or approved physical sample | Allowed |
| `documented` | Current supplier specification, certificate, or bill of materials | Allowed |
| `inferred` | Derived from imagery, prose, naming, or an unverified legacy record | Internal only |
| `unverified` | Missing, contradictory, or disputed evidence | Blocked |

## Governance

- Supplier imports may map aliases but cannot create canonical materials.
- New values require dictionary owner approval, EN/DE labels, family mapping,
  definition, alias review, collision review, and version release.
- Material evidence is reviewed when the specification changes, the supplier
  changes, quality disputes arise, or at least annually for exposed products.
- Revocation immediately removes material-led exposure and may reduce readiness
  to Level 1.
- Broad values such as `stone`, `wood`, `metal`, and `fabric` are accepted as
  primary materials only when more specific evidence is genuinely unavailable
  and the product is not exposed through a more specific material claim.
- Facet publication additionally requires adequate result coverage and
  zero-result behavior review.

# Part IV: Room Attribution Program

## Assignment

A room may be assigned only when all applicable evidence supports it:

1. the product has a normal functional use in the room;
2. typical scale and circulation are compatible;
3. installation, moisture, heat, electrical, load, and care constraints are
   compatible;
4. placement does not depend on a purely decorative SEO interpretation;
5. the room exists in the controlled vocabulary.

Each relationship stores:

- canonical room key
- relevance: `primary` or `secondary`
- evidence statement
- confidence: `verified`, `documented`, or `inferred`
- assigner, approver, assignment date, and review date

Only verified or documented room relationships can support Level 3 exposure.

## Review

- Taxonomy governance approves room definitions.
- Product governance proposes product-room relationships.
- A second reviewer approves exposed room relationships.
- Review occurs on material dimension changes, product redesign, installation
  changes, customer-fit incidents, taxonomy changes, or every 12 months.

## Revocation

Revoke a room relationship when:

- functional, scale, environmental, safety, or installation evidence fails;
- the source evidence expires or becomes contradictory;
- the room value is retired;
- the assignment was created for keyword reach rather than product fit.

Revocation removes the product from that room surface immediately. It does not
delete history or automatically affect valid relationships with other rooms.

## SEO Firewall

SEO staff may identify demand but cannot assign rooms. Search volume, competitor
usage, campaign briefs, copy mentions, photography location, and desired page
depth are explicitly invalid assignment evidence.

# Part V: Collection Attribution Program

| Collection type | Entry rule | Owner | Approval | Exit rule |
| --- | --- | --- | --- | --- |
| Founder Selection | Level 4 eligible and founder status `selected` | Founder authority | Founder only; product governance verifies data | Founder revocation, readiness below L3, or expired review |
| New Arrivals | Level 3 and first public availability within the configured window | Collection governance | Automated date rule plus collection approval | Automatic expiry; never manually left permanent |
| Permanent Collection | Level 3, durable commercial concept, defined membership rule, no end date required | Collection governance | Collection owner plus taxonomy impact review | Rule failure, collection retirement, or product withdrawal |
| Editorial Collection | Level 3 for shoppable exposure; L2 allowed only for internal editorial planning | Editorial governance | Editorial owner plus product governance | Story expiry, factual conflict, rights expiry, or readiness loss |

Collections never replace departments or categories. Supplier feeds cannot
assign collections. Each membership is a dated relationship with status,
rationale, approver, and rollback target.

# Part VI: Consultation Attribution Program

## Modes

- `required`: direct purchase is blocked or secondary to consultation.
- `recommended`: direct purchase remains possible; consultation is visibly
  available.
- `none`: standard product information is sufficient.

## Objective Triggers

Set `required` when any trigger applies:

- configuration or modular composition affects the order;
- customer-selected material, finish, dimensions, or electrical specification
  must be confirmed;
- product is bespoke, made-to-order, or non-cancellable after production;
- site measurement, access assessment, installation, anchoring, or assembly by
  specialists is required;
- natural material variation requires slab, batch, or finish approval;
- freight class, delivery access, or handling cannot be reliably priced through
  standard checkout;
- legal, electrical, fire, or project compliance must be confirmed.

Set `recommended` when none of the required triggers applies but at least one
applies:

- room scale or module planning materially affects fit;
- natural variation is meaningful but does not require approval;
- lead time exceeds the governance threshold;
- care, placement, or pairing has non-obvious constraints;
- the product is high-consideration under the approved pricing threshold.

Otherwise set `none`. Thresholds are configuration values owned by commerce
governance, not ad hoc reviewer judgment. Every non-`none` result stores the
trigger codes and EN/DE support note.

# Part VII: Current Catalog Readiness Audit

Audit basis: database state and repository contracts reviewed on June 6, 2026.
Because levels are cumulative, the attained level below is the highest fully
passed level, not the highest domain containing some data.

| Product set | Count | Current level | Current exposure | Principal blockers | Required action |
| --- | ---: | --- | --- | --- | --- |
| Shopware demo parents/variants | 16 | L0, quarantined | Rejected/unavailable | Non-Veylune identity, noncanonical SKU, demo taxonomy/content, inactive parents, inherited/partial variants, no Veylune governance | Exclude from all Veylune repositories, search, feeds, audits, and counts; remove only through the demo-residue rollback process |
| Aurelia Modular Sofa | 1 | L0; partial L2/L4 evidence | Currently eligible | Noncanonical `VLS-SOF-001`; no DE content; no dimensions/weight/delivery time; 3 images vs 5; supplier and batch governance absent; finish and room values outside the controlled dictionary; `fabric`/`upholstery_fabric` registry mismatch | Assign canonical furniture SKU; complete L1; add two approved media slots; normalize material/finish/rooms/categories; validate consultation trigger; rerun quality and surface approvals |
| Calma Travertine Table | 1 | L0; partial L2/L4 evidence | Currently eligible | Noncanonical `VLS-SOF-003`; empty DE record; no dimensions/weight/delivery time; 2 images vs 5; supplier and batch governance absent; `Honed Stone`, `Gallery Space`, and `Lounge` are noncanonical; database category and exposure registry mappings are not one governed relationship | Assign canonical furniture/dining SKU; complete L1; add three approved media slots; normalize `travertine` + `stone`, `honed`, rooms, and categories; formalize natural-stone consultation; rerun approvals |
| Nocturne Floor Lamp | 1 | L0 | Rejected | Noncanonical `VLS-SOF-002`; description incorrectly identifies a modular sofa; no DE/SEO; no governed material, finish, color, room, or collection properties; no dimensions/weight/delivery time; 3 images vs 5; no exposure approval | Correct identity and copy first; assign lighting SKU; complete commerce, electrical/compliance, media, material, taxonomy, room, and consultation data; submit new exposure review |
| Atelier Stone Vessel | 1 | L0 | Rejected | Noncanonical `VLS-SOF-004`; generic placeholder copy; no DE/SEO; product name claims stone without governed material evidence; no finish/color/room/collection properties; no dimensions/weight/delivery time; 4 images vs 5; no exposure approval | Verify the actual stone and evidence; assign decor SKU; replace placeholder copy; complete commerce/media/taxonomy/room data; submit new exposure review |

## Immediate Catalog Actions

1. Keep the 16 demo records fail-closed and remove them from operational catalog
   denominators. The production readiness denominator is 4, not 20.
2. Treat the current Aurelia and Calma exposure approvals as temporary legacy
   approvals. Remediate or suspend them before the new contract becomes
   enforcement authority.
3. Replace the static four-SKU exposure registry with governed product and
   relationship data.
4. Unify material keys, room keys, category keys, and SKU format across
   contracts, database properties, imports, and exposure runtime.
5. Add an automated readiness validator before importing additional products.

# Part VIII: Scaling Model

| Scale | Operating model | Governance workload | Attribution workload | Review model |
| --- | --- | --- | --- | --- |
| 20 products | Manual remediation with one authoritative dashboard | Product-by-product baseline; weekly review | Manual evidence collection and normalization | Two-person approval for exposure; founder reviews individually |
| 200 products | Batch intake, supplier templates, automated validation, exception queues | Governance owners review exceptions and vocabulary changes, not every scalar field | Supplier aliases map to controlled values; sampled evidence QA; relationship bulk tools | Risk-based batch review plus 100% review of Level 3, new values, and Founder candidates |
| 1000+ products | PIM/MDM-backed contract, event-driven validation, versioned registries, role-based workflow | Policy and anomaly oversight; service-level targets and audit sampling | Automated deterministic mappings with confidence, lineage, expiry, and drift detection | Automated gates; sampled routine review; mandatory human review for exceptions, new vocabulary, exposure, and Founder decisions |

## Required Processes

- supplier onboarding with data-quality score and approved mapping profile
- canonical SKU reservation before product creation
- schema-valid batch manifest and rollback snapshot
- media ingest with rights and slot validation
- content parity and placeholder detection
- controlled vocabulary service with alias versioning
- product-attribution relationship workflow
- readiness calculation and reason-code queue
- publication, sellability, and exposure approvals as separate workflows
- scheduled evidence expiry and revocation jobs
- change-impact revalidation when product facts or dictionaries change
- monthly dashboard review and quarterly governance audit

## Workload Controls

- Straight-through processing is allowed only for known suppliers, existing
  canonical values, complete evidence, and no conflicting data.
- New material, room, category, collection, or alias values always enter an
  exception queue.
- At 1000+, reviewers inspect exceptions and statistically meaningful samples;
  they do not manually re-key every product.
- Readiness is recalculated on every relevant change and nightly as a backstop.
- Exposure is automatically suspended when a blocking dependency is revoked or
  expires.

# Part IX: Readiness Dashboard Specification

## Primary Product View

| Column | Meaning |
| --- | --- |
| Product | Name plus immutable product ID |
| SKU | Canonical Veylune SKU and SKU state |
| Readiness | L0-L4 plus percentage within the current next level |
| Blockers | Stable blocking reason codes and age |
| Commerce | Price, tax, availability, fulfillment, sellability result |
| Media | Approved slots / 5, rights status, locale alt-text parity |
| Material | Primary material, family, confidence, evidence age |
| Category | Department, primary category, taxonomy status |
| Room | Approved primary/secondary rooms and next review |
| Collection | Active memberships, type, start/end |
| Consultation | Required/recommended/none plus trigger codes |
| Exposure | Approved surfaces, status, start/end, suspension reason |
| Founder | Eligible, pending, selected, declined, or revoked |
| Owner | Current accountable role/person |
| Last Audit | Last calculation, human review, and next due date |

## Dashboard Views

- **Executive:** counts and percentages by readiness level, exposure status,
  collection, and blocking domain.
- **Operations:** products blocked from the next level, owner, oldest blocker,
  and SLA breach.
- **Attribution:** unmapped values, low-confidence materials/rooms, expiring
  evidence, and dictionary drift.
- **Exposure:** surface-by-surface eligible, exposed, rejected, suspended, and
  reason codes.
- **Founder:** Level 4 candidates awaiting decision and selected products with
  review dates.
- **Supplier:** completeness, rejection rate, alias exceptions, rights issues,
  and remediation aging by supplier and batch.

## Core Metrics

- percentage of production products at each level
- first-pass Level 1 and Level 2 acceptance rate
- median days from L0 to L3
- products exposed below L3: target `0`
- unmapped public property values: target `0`
- expired evidence on exposed products: target `0`
- Founder Selection products without current founder decision: target `0`
- room/material revocation propagation time
- rejection and rework rate by supplier
- top blocking reason codes and median blocker age

## Minimum Dashboard Record

```text
product_id
veylune_sku
readiness_level
next_level
blocking_reason_codes[]
material_status
category_status
room_status
collection_status
consultation_mode
exposure_status
founder_status
owner
last_audited_at
next_review_at
```

# Part X: Operating Authority

| Decision | Accountable authority |
| --- | --- |
| Product record and readiness | Product governance |
| SKU | SKU governance |
| Price and sellability | Commerce/sellability governance |
| Categories and rooms | Taxonomy governance |
| Materials, finishes, colors | Property dictionary governance |
| Media and rights | Media governance |
| Permanent and commercial collections | Collection governance |
| Editorial collections | Editorial governance |
| Exposure surfaces | Exposure governance |
| Consultation thresholds | Commerce governance; product governance applies rules |
| Founder Selection | Founder authority |

## Entry Rule for the Veylune Revenue Engine

Every future product must possess:

- a canonical Veylune identity;
- complete bilingual commerce and fulfillment facts;
- a rights-cleared five-image media set;
- physical, care, and compliance facts;
- controlled material, category, and room attribution with evidence;
- explicit publication and sellability decisions;
- an objective consultation mode;
- a quality approval and rollback target;
- explicit approval for every public surface that will expose it.

That is Level 3. Level 4 adds founder eligibility and a separate founder
decision. Catalog growth is acceptable only when automation increases
validation capacity without relaxing these gates.
