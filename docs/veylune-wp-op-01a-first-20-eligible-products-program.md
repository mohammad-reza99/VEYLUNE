# WP-OP-01A First 20 Eligible Products Program

## Operational Decision

The path from 2 to 20 exposure-ready products is:

```text
2 current eligible products
+ 2 remediated Veylune products
+ 16 net-new governed production products
= 20 eligible products
```

The 16 `SWDEMO` records in the current database are quarantine records, not
production candidates. Promoting them would violate product identity, supplier
provenance, taxonomy, material authority, and demo-residue governance.

This program does not change storefront design, architecture, navigation,
homepage, discovery behavior, readiness requirements, or exposure rules. It
uses the existing contracts, validators, registries, lifecycle states, and
surface approvals.

## Evidence Basis

Audit date: June 6, 2026.

Evidence reviewed:

- the 20 live Shopware product records and their parent relationships
- EN/DE translations, SEO metadata, custom fields, prices, availability,
  visibility, PDP routes, media counts, categories, properties, and options
- `ProductExposureService` surface registry and rejection rules
- product readiness, quality, sellability, media, content, taxonomy, material,
  supplier, SKU, batch, and rollback contracts
- demo quarantine inventory
- Product Readiness & Attribution Program

## Status Definitions

- **Eligible:** currently passes the existing runtime exposure gate.
- **Contract-complete:** passes cumulative Level 0 through Level 3 requirements.
- **Partial:** evidence exists but is not complete or normalized.
- **Blocked:** missing or invalid governed evidence.
- **Not applicable:** no decision can be made because the record is
  quarantined or the prerequisite level is incomplete.

# A. Full Catalog Audit

## Audit Summary

| Cohort | Records | Runtime eligible | Production candidates | Contract-complete L3 |
| --- | ---: | ---: | ---: | ---: |
| Existing Veylune products | 4 | 2 | 4 | 0 |
| Shopware demo parents | 6 | 0 | 0 | 0 |
| Shopware demo variants | 10 | 0 | 0 | 0 |
| Total | 20 | 2 | 4 | 0 |

The operational baseline remains 2 eligible because the existing Revenue Engine
recognizes Aurelia and Calma. The remediation program must bring those two into
full contract compliance before scaling their pattern.

## Complete Inventory

| Product | Readiness | Exposure | Material | Category | Room | Collection | Consultation | Missing requirements |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `VLS-SOF-001` Aurelia Modular Sofa | L0; partial L2/L4 evidence | Eligible: Founder, New Arrivals, Furniture, Living Room | Partial: Upholstery Fabric exists; runtime key `fabric` conflicts with canonical `upholstery_fabric`; evidence/confidence absent | Partial: database `Sofas`; registry `furniture`; canonical governed relationship absent | Partial: Living Room valid; Gallery Space and Lounge unmapped | Runtime Founder and New Arrivals approved; relationship governance incomplete | Likely required because module and fabric selection affect order; trigger record absent | Canonical SKU; supplier/batch; DE content/SEO; dimensions/weight; delivery time/class; returns; five-image standard, currently 3; per-image rights/alt approval; normalized finish/color/room; evidence and review records; publication/sellability/quality records |
| `VLS-SOF-003` Calma Travertine Table | L0; partial L2/L4 evidence | Eligible: Founder, New Arrivals, Furniture, Dining & Kitchen, Living Room, Dining Room | Partial: Travertine exists; family `stone`, evidence/confidence, and governed finish absent | Partial: database `Dining Tables`; registry `furniture` and `dining-kitchen`; canonical governed relationship absent | Partial: Living Room valid; registry adds Dining Room; Gallery Space and Lounge unmapped | Runtime Founder and New Arrivals approved; relationship governance incomplete | Required if stone/slab/finish approval is part of order; otherwise recommended; trigger record absent | Canonical SKU; supplier/batch; complete DE title/description/SEO; dimensions/weight; delivery time/class; returns; five-image standard, currently 2; per-image rights/alt approval; normalize `honed`, color and rooms; evidence/review records; publication/sellability/quality records |
| `VLS-SOF-002` Nocturne Floor Lamp | L0 | Rejected | Blocked: no governed properties | Partial: database `Floor Lamps`; registry `lighting`; no governed canonical relationship | Partial proposal only: registry says Living Room; no evidence or approval | None | Undetermined until electrical/configuration facts exist | Canonical lighting SKU; supplier/batch; correct false sofa description; DE content; EN/DE SEO; dimensions/weight; delivery and returns; five-image standard, currently 3; material/finish/color; electrical and compliance facts; care; room evidence; consultation result; publication/sellability/quality and exposure approvals |
| `VLS-SOF-004` Atelier Stone Vessel | L0 | Rejected | Blocked: name claims stone but no governed material evidence | Partial: database `Decor Objects`; registry `decor-objects`; no governed canonical relationship | Partial proposal only: registry says Living Room; no evidence or approval | None | Undetermined until scale, weight, handling, variation, and customization facts exist | Canonical decor SKU; supplier/batch; verified stone species or documented broad `stone`; replace placeholder copy; DE content; EN/DE SEO; dimensions/weight; delivery and returns; five-image standard, currently 4; finish/color; room evidence; consultation result; publication/sellability/quality and exposure approvals |
| `SWDEMO10001` Main product | L0 quarantine | Ineligible | Invalid for Veylune: demo Plastic | Invalid demo electronics category | None | None | Not applicable | Demo identity/provenance; noncanonical SKU; inactive; no delivery time; one image; no governed Veylune facts, attribution, lifecycle, quality, or exposure record |
| `SWDEMO100013` Main product with reviews | L0 quarantine | Ineligible | Invalid: food ingredients are not material authority | Invalid demo sweets category | None | None | Not applicable | Demo identity/provenance; noncanonical SKU; inactive; no physical facts/delivery time; one image; no governed Veylune facts, attribution, lifecycle, quality, or exposure record |
| `SWDEMO10002` Main product with advanced prices | L0 quarantine | Ineligible | None | Invalid demo electronics category | None | None | Not applicable | Demo identity/provenance; noncanonical SKU; inactive; one image; no delivery time; no governed Veylune material, room, collection, lifecycle, quality, or exposure record |
| `SWDEMO10005` Variant product parent | L0 quarantine | Ineligible | Invalid for Veylune: demo Cotton | Invalid demo Women category | None | None | Not applicable | Demo identity/provenance; noncanonical SKU; inactive; incomplete physical facts; one image; no delivery time; no governed Veylune attribution/lifecycle/quality/exposure |
| `SWDEMO10005.1` Blue / M variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Demo child SKU; no own translation, price, media, dimensions, manufacturer, tax, delivery, visibility, governed attribution, lifecycle, quality, or exposure |
| `SWDEMO10005.2` Blue / XL variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10005.1` |
| `SWDEMO10005.3` Red / M variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10005.1` |
| `SWDEMO10005.4` Red / XL variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10005.1` |
| `SWDEMO10005.5` White / M variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10005.1` |
| `SWDEMO10005.6` White / XL variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10005.1` |
| `SWDEMO10006` Main product, free shipping | L0 quarantine | Ineligible | Invalid for Veylune context: demo Leather | Invalid demo Men category | None | None | Not applicable | Demo identity/provenance; noncanonical SKU; inactive; incomplete physical facts; one image; no delivery time; no governed Veylune attribution/lifecycle/quality/exposure |
| `SWDEMO10007` Main product with properties | L0 quarantine | Ineligible | Invalid for Veylune context: demo Cotton/Polyester | Invalid demo Men category | None | None | Not applicable | Demo identity/provenance; noncanonical SKU; inactive; no dimensions/weight/delivery; one image; no governed Veylune room, collection, lifecycle, quality, or exposure |
| `SWDEMO10007.1` Size S variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Demo child SKU; no own translation, price, media, dimensions, manufacturer, tax, delivery, visibility, governed attribution, lifecycle, quality, or exposure |
| `SWDEMO10007.2` Size L variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10007.1` |
| `SWDEMO10007.3` Size M variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10007.1` |
| `SWDEMO10007.4` Size XL variant | L0 quarantine | Ineligible | Inherited demo data; invalid | Inherited invalid demo category | None | None | Not applicable | Same blocking demo child deficiencies as `SWDEMO10007.1` |

# B. Remediation Matrix

## Priority Meaning

- **P0:** stabilize currently exposed products against the full contract.
- **P1:** complete known Veylune products that can create the next eligibility
  increase.
- **P2:** preserve quarantine and replace with a real governed candidate.
- **P3:** cleanup only; does not contribute to the eligibility target.

| Product | Current level | Missing item groups | Priority | Operational disposition |
| --- | --- | --- | --- | --- |
| Aurelia Modular Sofa | L0, runtime eligible | Identity, supplier/batch, DE parity, physical, fulfillment, media +2, normalized attribution, decision records | P0 | Remediate in place; retain current surface scope only after revalidation |
| Calma Travertine Table | L0, runtime eligible | Identity, supplier/batch, DE parity, physical, fulfillment, media +3, normalized attribution, decision records | P0 | Remediate in place; retain current surface scope only after revalidation |
| Atelier Stone Vessel | L0 | Identity, supplier/batch, factual content, physical, fulfillment, media +1, material, room, consultation, all approvals | P1 | First new eligibility candidate |
| Nocturne Floor Lamp | L0 | Identity, supplier/batch, corrected content, physical, fulfillment, media +2, material, electrical compliance, room, consultation, all approvals | P1 | Second new eligibility candidate |
| `SWDEMO10001` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO100013` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10002` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005.1` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005.2` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005.3` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005.4` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005.5` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10005.6` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10006` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10007` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10007.1` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10007.2` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10007.3` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |
| `SWDEMO10007.4` | L0 quarantine | Entire Veylune contract | P2/P3 | Do not remediate; preserve quarantine, source one replacement candidate |

# C. Fastest Path and Quick Wins

## Ranked Work

### Quick Wins

1. **Aurelia Modular Sofa**
   - Already exposed and has substantial content, material, room, collection,
     consultation, price, media, visibility, and product-story evidence.
   - Work is normalization and contract completion, not product discovery.
2. **Calma Travertine Table**
   - Already exposed and has strong material and story evidence.
   - Main effort is bilingual completion, physical/fulfillment data, media, and
     normalized relationship records.

These products do not increase the runtime count above 2, but stabilizing them
is the mandatory first action. Scaling on top of legacy exceptions would
reproduce the current data debt.

### Medium Effort

3. **Atelier Stone Vessel**
   - Closest rejected product by media count and category clarity.
   - Becomes the first count increase if stone evidence is available.
4. **Nocturne Floor Lamp**
   - Requires material recovery plus electrical/compliance facts and correction
     of materially false product copy.

Completing both raises the known production cohort from 2 to 4 eligible.

### High Effort

5. **Sixteen net-new supplier-backed products**
   - These are required to reach 20.
   - The existing 16 demo records are not candidates.
   - Select products with complete source specifications and media rights before
     products with attractive imagery but weak facts.

## Candidate Selection Score for the Net-New 16

Select candidates in descending score:

| Criterion | Weight |
| --- | ---: |
| Active supplier and complete supplier/BOM evidence | 20 |
| Five rights-cleared images already available | 20 |
| Complete dimensions, weight, care, compliance, lead time, delivery and returns | 20 |
| Material maps to an existing canonical value without a new alias | 15 |
| Category and room map to existing published values | 10 |
| EN/DE source content or translation-ready factual source | 10 |
| Standard product with no required configuration | 5 |

Reject from the first batch any candidate with unresolved media rights,
unverified primary material, missing compliance facts, or an inactive supplier.

# D. Material Attribution Recovery

## Nocturne Floor Lamp

No material may be inferred from the product name, images, price, or category.

Required evidence:

1. supplier specification or bill of materials;
2. identification of base, stem/body, shade/diffuser, cable, and fittings;
3. percentage or functional significance sufficient to choose one primary
   material;
4. finish and color specification;
5. reviewer confirmation that public copy matches the facts.

Required governed record:

```text
primary_material: <canonical material key>
material_family: <canonical family>
secondary_materials: [all visible, structural, care, or safety-significant materials]
finish: <canonical finish>
color: <canonical color>
material_confidence: documented | verified
material_evidence: <supplier specification or sample reference>
material_approved_by: <property dictionary reviewer>
material_reviewed_at: <date>
```

If the supplier reports powder-coated steel and a glass diffuser, for example,
the valid structure would normally be `primary_material=metal`,
`secondary_materials=[glass]`, with the exact finish recorded separately. This
is an example only; it is not an attribution decision.

Additional readiness actions:

- replace `VLS-SOF-002` with a reserved canonical lighting SKU such as the next
  available `VLS-LIG-######`;
- replace the incorrect modular-sofa description;
- add DE title, description, SEO title, and meta description;
- add dimensions, weight, assembly, care, electrical rating, plug/voltage,
  certification, lead time, delivery, and returns data;
- add two approved images to reach five;
- approve `floor_lamps` under the published Lighting taxonomy;
- retain Living Room only after functional and scale evidence;
- add Bedroom or Workspace only if light output, controls, scale, and electrical
  use support those rooms;
- apply consultation triggers objectively:
  - `required` for hardwiring, custom electrical specification, installation, or
    configurable finish;
  - `recommended` for non-obvious placement or project specification;
  - otherwise `none`.

## Atelier Stone Vessel

The word `Stone` is not evidence of a specific stone.

Required evidence:

1. supplier specification, invoice, quarry/atelier statement, or approved
   physical inspection;
2. exact stone species where known;
3. finish and color;
4. weight and dimensional stability;
5. care and variation statement.

Attribution rule:

- use `travertine`, `marble`, or another approved specific value only when
  documented or verified;
- use broad `stone` only when the evidence supports natural stone but does not
  support a more specific claim;
- use no stone-led public exposure while confidence is inferred or unverified.

Required governed record uses the same fields as Nocturne. Any insert, coating,
metal base, or protective treatment that affects appearance, structure, or care
must be recorded as a secondary material or finish.

Additional readiness actions:

- replace `VLS-SOF-004` with the next canonical `VLS-DEC-######`;
- replace generic placeholder copy with factual EN/DE content and SEO;
- add dimensions, weight, care, handling, lead time, delivery, and returns;
- add one approved image to reach five;
- approve the canonical vessel/decor category relationship;
- approve Living Room only with placement and scale evidence;
- add Hallway only if footprint, stability, circulation, and use are suitable;
- mark consultation `required` if finish, stone selection, variation approval,
  specialist handling, or freight access must be confirmed; otherwise apply
  `recommended` or `none` from recorded triggers.

# E. Exposure Expansion Plan

## Known Four-Product Outcome

This is the conservative surface result after the four known Veylune products
complete Level 3. It preserves existing approved relationships and does not
invent new room or founder decisions.

| Surface | Current eligible | Guaranteed after four-product remediation | Conditional maximum |
| --- | ---: | ---: | ---: |
| Founder Selection | 2 | 2 | 4, only after separate Level 4 and founder decisions |
| New Arrivals | 2 | 2 | 4, only if Nocturne and Atelier have governed first-availability dates inside the active window |
| Furniture | 2 | 2 | 2 |
| Lighting | 0 | 1 | 1 |
| Decor Objects | 0 | 1 | 1 |
| Living Room | 2 | 4 | 4 |
| Dining Room | 1 | 1 | 1 |
| Bedroom | 0 | 0 | 1 if Nocturne evidence supports it |
| Workspace | 0 | 0 | 1 if Nocturne evidence supports it |
| Hallway | 0 | 0 | 1 if Atelier evidence supports it |
| Outdoor | 0 | 0 | 0 |

## First-20 Assortment Sourcing Brief

Exact surface counts for 20 cannot be certified until the 16 real candidates
are named and audited. The following is a sourcing brief, not permission to
force attribution:

| Surface | First-20 sourcing target | Governance condition |
| --- | ---: | --- |
| Founder Selection eligible | 4-6 | Level 4 evidence; founder selection remains separate |
| Founder Selection selected | 2-4 | Founder decision only |
| New Arrivals | 16-20 at launch | Governed first-availability date and active window |
| Furniture | 8 | Canonical category evidence |
| Lighting | 6 | Canonical category and electrical compliance |
| Decor Objects | 6 | Canonical category and material evidence |
| Living Room | 12+ | Functional and scale evidence |
| Dining Room | 4+ | Functional and scale evidence |
| Bedroom | 4+ | Functional, scale, and safety evidence |
| Workspace | 4+ | Functional and task/scale evidence |
| Hallway | 3+ | Footprint, stability, and circulation evidence |
| Outdoor | 0 unless sourced intentionally | Weather, UV, moisture, corrosion, safety, and care evidence |

Room coverage is achieved by sourcing suitable products, never by assigning
extra rooms to existing products for coverage or SEO.

# F. First 20 Eligible Products Execution Plan

## Gate 0: Freeze the Baseline

- export the 20-record audit and current surface counts;
- retain the 16 demo records in quarantine;
- reserve canonical replacement SKUs for the four Veylune products;
- record rollback targets before identity or attribution changes;
- create one blocker record per missing requirement.

Exit condition: baseline is reproducible and every field has an owner.

## Gate 1: Stabilize the Existing Two

Process Aurelia and Calma independently:

1. complete supplier and source-batch lineage;
2. assign canonical SKU without losing route history;
3. complete EN/DE content and SEO parity;
4. add physical, delivery, returns, care, and availability facts;
5. complete the five-image media standard;
6. normalize materials, finishes, colors, categories, and rooms;
7. create collection, consultation, publication, sellability, quality, and
   exposure decision records;
8. run product readiness, content, media, taxonomy, property, commerce,
   supplier, and rendered-surface checks.

Exit condition: 2 runtime eligible and 2 contract-complete Level 3.

## Gate 2: Recover Nocturne and Atelier

- complete material recovery before editorial work;
- complete category and room relationships;
- complete product-specific compliance and consultation decisions;
- submit each product through the same Level 0-to-Level 3 chain;
- approve only exact surfaces supported by evidence.

Exit condition: at least 4 contract-complete Level 3 products.

## Gate 3: Select the Net-New 16

- obtain a real candidate manifest from active suppliers;
- score candidates using the readiness score above;
- choose 16 primary candidates plus 4 reserves;
- avoid candidates requiring new canonical vocabulary in the first batch unless
  assortment necessity justifies the exception work;
- divide into two batches of 8 to contain rollback and review load.

Exit condition: 20 selected candidates have source evidence; 16 primary
candidates have no known blocking provenance or rights issue.

## Gate 4: Validate Batch A, Products 5-12

For each of 8 products:

1. reserve canonical SKU;
2. map supplier SKU and source batch;
3. validate all Level 1 commerce/media/content facts;
4. map material, finish, color, category, and room to controlled values;
5. resolve consultation mode from trigger codes;
6. run staging dry-run and readiness validators;
7. apply import only after batch and rollback approval;
8. review publication, sellability, quality, and exact exposure surfaces.

Exit condition: 12 eligible products total. Failed candidates are replaced from
the reserve list; gates are not waived.

## Gate 5: Validate Batch B, Products 13-20

Repeat Gate 4 for the second batch of 8. Do not reuse Batch A approvals or
evidence.

Exit condition:

- at least 20 Level 3 products;
- zero products exposed below Level 3;
- zero inferred/unverified materials on exposed products;
- zero unmapped public material, finish, color, category, or room values;
- every exposed surface has an explicit approval record;
- every non-`none` consultation decision has trigger codes and EN/DE support
  copy;
- Founder Selection contains only separately selected Level 4 products.

# G. Scaling Roadmap

| Stage | Catalog action | Batch model | Review model | Exit gate |
| --- | --- | --- | --- | --- |
| 2 to 4 | Remediate Nocturne and Atelier after stabilizing Aurelia and Calma | Individual products | 100% field and relationship review | 4 Level 3 products |
| 4 to 20 | Onboard 16 real products plus 4 reserves | Two batches of 8 | 100% Level 3 review; exceptions closed before apply | 20 Level 3 products |
| 20 to 50 | Add 30 products selected from existing controlled values | Three batches of 10 | Automated scalar validation plus 100% exposure review | 50 Level 3; no vocabulary debt |
| 50 to 100 | Add 50 products through the proven simulation shape | Five batches of 10 | Automated readiness gates, exception review, sampled routine QA, 100% new-value and exposure approval | 100 Level 3; rollback rehearsal passed |

At 20 products, maintain a weekly blocker review. At 50, introduce supplier
quality scoring and attribution exception queues. At 100, make the existing
batch validator a mandatory pre-apply control and report acceptance/rework rates
by supplier, material, category, and blocking code.

# H. Risks and Controls

| Risk | Impact | Control |
| --- | --- | --- |
| No 16 real candidate records currently exist | The numerical target cannot be reached by remediation alone | Require a named 16+4 supplier candidate manifest before committing the 20-product completion gate |
| Existing eligible products are not contract-complete | Legacy exceptions become the scaling template | Complete Gate 1 before onboarding Batch A |
| Material evidence unavailable for Nocturne/Atelier | Products remain blocked at L1 | Obtain supplier BOM/specification or approved physical verification; do not infer |
| Canonical SKU migration affects PDP routes | Broken URLs or identity duplication | Reserve new SKU, capture route snapshot, define redirect/canonical handling, retain rollback target |
| EN/DE parity backlog | L1/L2 failure and inconsistent consultation | Translate factual source after facts freeze; run content parity validator |
| Media count exists but rights/alt/crop evidence does not | False media readiness | Audit every asset, not only product media count |
| Room coverage pressure drives SEO attribution | Material and room authority degradation | Use room coverage as sourcing criteria; taxonomy governance approves every relationship |
| Founder Selection used to make catalog look fuller | Founder curation dilution | Keep Level 4 and founder decision separate; no target of 20 founder-selected products |
| New Arrivals becomes permanent | Stale merchandising | Require first-availability date and automatic end date |
| Consultation applied subjectively | Inconsistent buying support | Store objective trigger codes and threshold version |
| New supplier aliases create dictionary sprawl | Unbounded materials and facets | Prefer existing values; route every new value/alias through dictionary governance |
| Demo products remain in operational counts | Misleading readiness rate | Report production denominator and quarantine denominator separately |
| Eight-product batch hides individual failures | Partial unsafe publication | Product-level reason codes inside batch; only passing products proceed |

# Completion Standard

WP-OP-01A is operationally complete only when:

```text
production_products_level_3 >= 20
exposed_products_below_level_3 = 0
unverified_exposed_materials = 0
unmapped_public_attributions = 0
unapproved_surface_relationships = 0
founder_selected_without_level_4 = 0
consultation_decisions_without_triggers = 0
```

The contract remains fixed. Products advance only when their facts, evidence,
attributions, decisions, and surface approvals satisfy it.
