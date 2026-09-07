# VEYLUNE Vision System

**Decision package:** VS-01
**Status:** Council recommendation; Founder decision required
**Scope:** Creative and structural north star for the storefront, catalog, and service experience
**Supersedes:** Page-by-page styling as the primary design method

## Executive Decision

Veylune should adopt **The Living Index** as its governing experience concept.

The Living Index combines two capabilities that are usually separated:

- **Index:** structured breadth, search, taxonomy, comparison, and commerce.
- **Living:** rooms, materiality, atmosphere, authorship, and human guidance.

The system is not a Wayfair clone, a sparse luxury gallery, or an open
marketplace. It is a curated home-commerce platform whose breadth is legible,
whose products are evidenced, and whose visual character remains recognizably
Veylune.

The central rule is:

```text
Find with precision
-> understand in context
-> verify the object
-> buy with confidence
-> live with support
```

---

## Problem

The current storefront has accumulated multiple local style solutions without
one governing visual and structural idea.

The result has five systemic weaknesses:

1. Editorial restraint is applied to browse, decision, and transaction surfaces
   that require higher information density.
2. Marketplace conventions are added as isolated components instead of forming
   one discovery system.
3. Large type and generous spacing are used as signals of luxury even when
   product proof is absent.
4. Typography and color roles are inconsistent across old, preview, and
   marketplace-era components.
5. Page types look related, but do not express distinct customer tasks.

This creates a site that can appear polished in individual screenshots while
remaining weak as a complete commercial system.

---

## Evidence

### Current implementation evidence

- The core display stack still resolves to `Georgia, "Times New Roman", serif`.
- The original token system is organized around ivory, limestone, bronze, and
  charcoal, while the current marketplace header introduces a stronger purple
  command color outside that original semantic system.
- Legacy account, cart, checkout, collection, and editorial styles use the serif
  voice for operational information.
- Phase-specific closure styles overlap foundational component styles, creating
  cascade competition and inconsistent density.
- Existing product and room imagery is sufficient for a prototype, but not for
  full production assortment proof.
- Public routes are broader than the approved product inventory; page readiness
  and product readiness are therefore not yet aligned.

### Benchmark evidence

- Wayfair demonstrates breadth, high navigation density, promotional rhythm,
  and fast category access.
- Design Within Reach demonstrates premium product grids, brand authority,
  configurability, category depth, and style-led discovery.
- 1stDibs connects category, style, creator, editorial, seller trust, and
  protected purchase architecture.
- Archiproducts connects products, materials, brands, technical files, projects,
  and professional workflows inside one search universe.
- RH demonstrates that product assortment can become an authored spatial and
  service ecosystem rather than a conventional catalog.

Reference URLs:

- <https://www.wayfair.com/>
- <https://www.dwr.com/furniture>
- <https://www.1stdibs.com/>
- <https://www.archiproducts.com/en>
- <https://rh.com/>

---

## Risk

| Risk | Consequence | Required control |
| --- | --- | --- |
| Wayfair imitation | Veylune becomes visually interchangeable | Borrow capability, never brand expression |
| Gallery overreach | Product discovery and conversion remain weak | Restrict cinematic scale to Scene Mode |
| Density overreach | Premium identity collapses into generic marketplace UI | Use controlled density and authored image rhythm |
| Unsupported breadth | Empty departments expose operational immaturity | Separate capability-ready from publicly active |
| Token fragmentation | Every new page produces more CSS exceptions | Migrate to semantic tokens and owned components |
| Typography licensing delay | Prototype identity cannot become production identity | Select multilingual licensed/self-hosted families before implementation |
| Draft product leakage | Unapproved products appear purchasable | Preserve readiness and publication gates |
| Mobile compression | Desktop composition becomes unusable on mobile | Define mobile composition, not only breakpoints |
| Performance regression | Rich imagery damages LCP and interaction | Enforce asset and font budgets from prototype stage |

---

## Options

### Option A — The Living Index

**Thesis:** A structured, searchable home universe with authored spatial and
material layers.

Character:

- architectural grid;
- high browse clarity;
- strong discovery axes;
- compact commerce UI;
- cinematic room and campaign interruptions;
- evidence-rich product dossiers.

Best fit:

- large catalog growth;
- premium multi-brand commerce;
- customer and professional audiences;
- long-term product, room, material, and project discovery.

### Option B — Spatial Editions

**Thesis:** Veylune behaves like a continuously changing interior publication
whose stories are shoppable.

Character:

- full-bleed scenes;
- fashion-editorial pacing;
- seasonal editions;
- asymmetric composition;
- reduced visible catalog mechanics.

Best fit:

- campaign launches;
- curated capsules;
- limited assortment;
- strong content production capability.

Primary limitation:

- insufficient structural authority for a broad home marketplace.

### Option C — Material Atlas

**Thesis:** Veylune organizes the home through materials, construction,
provenance, and technical evidence.

Character:

- material-first discovery;
- specimen-like image treatment;
- precise technical typography;
- comparison and professional tools;
- strong evidence and specification layers.

Best fit:

- architects and interior designers;
- high-consideration products;
- professional and project workflows;
- supplier credibility.

Primary limitation:

- can feel technical and emotionally narrow for mainstream home shopping.

---

## Council Positions

Only roles directly relevant to this decision are activated. Other permanent
Council seats become mandatory during implementation and release review.

### Founder and premium platform vision

The direction must be ownable at brand level and must not reduce Veylune to a
Shopware theme or a Wayfair clone. The system needs one recognizable idea that
remains coherent as the assortment expands.

### Editorial commerce and art direction

Scene-led imagery should create desire, but every scene must expose a path to
objects. Art direction must define repeatable crop, light, temperature, and
material rules rather than relying on individual attractive images.

### Merchandising and assortment

The system must explain why products are adjacent, what customer mission a rail
serves, and when a module should be suppressed. Visual rhythm cannot substitute
for assortment logic.

### Continuous product discovery

The three directions should be tested as representative prototypes, not debated
only through internal preference. Comprehension, desirability, trust, and next
action should be observed on the same tasks.

### Ecommerce UX authority

Browse, search, filters, product comparison, PDP information, cart, and checkout
must follow evidence-based interaction patterns. Expression may change; expected
commerce behavior may not become ambiguous.

### Content strategy and governance

Each content unit must perform orientation, differentiation, evidence, or action.
Abstract luxury language cannot occupy structural space without helping a
customer decide.

### Design-system architecture

The four visual modes require shared foundations and explicit component
variants. They must not become four independent CSS systems.

### Accessibility authority

Dense Index Mode and expressive Scene Mode must both meet WCAG 2.2 AA. Small
uppercase text, weak purple contrast, fine rules, motion, and image-contained
text require explicit rejection tests.

### Architecture and domain model

The six discovery axes must map to stable domain concepts. Visual navigation
cannot invent relationships that do not exist in catalog and search data.

### Shopware platform authority

Page composition should use supported Storefront, CMS, DAL, and plugin extension
points. The vision does not authorize direct core patches or duplicated product
truth outside Shopware governance.

### Search and discovery authority

Object, Room, Material, Style, Maker, and Project must become indexed discovery
dimensions with measurable relevance, not decorative navigation labels.

### Quality, security, performance, and delivery

The selected direction requires screenshot contracts, route smoke tests, keyboard
acceptance, public/private exposure checks, performance budgets, and rollback.
Visual approval alone cannot authorize release.

---

## Conflict

Three genuine conflicts remain:

1. **Breadth vs authorship:** large catalogs reward repeatable dense patterns;
   premium brands require authored moments.
2. **Emotion vs evidence:** scenes create desire; product facts create confidence.
3. **Customer vs professional depth:** mainstream customers need simple language;
   professionals need technical and project evidence.

The resolution is not an average visual style. The resolution is a controlled
multi-mode system sharing one identity.

---

## Recommendation

Adopt **The Living Index** as the north star.

Use **Spatial Editions** as the Scene Mode for homepage, room, collection, and
campaign experiences.

Use **Material Atlas** as the evidence layer inside PDP, material, trade, and
professional experiences.

This creates one system with two supporting expressions instead of three
competing brands.

---

## Direction Scorecard

Scoring uses a 1-10 scale. Weighted totals are out of 100.

| Criterion | Weight | Living Index | Spatial Editions | Material Atlas |
| --- | ---: | ---: | ---: | ---: |
| Immediate impact | 20% | 9 | 10 | 8 |
| Veylune distinctiveness | 20% | 9 | 9 | 10 |
| Catalog scalability | 20% | 10 | 6 | 8 |
| Commerce clarity | 15% | 9 | 6 | 8 |
| Mobile strength | 10% | 9 | 7 | 7 |
| Supplier/professional credibility | 10% | 9 | 8 | 10 |
| Implementation feasibility | 5% | 8 | 6 | 7 |
| **Weighted total** | **100%** | **91.5** | **77.0** | **84.5** |

Decision:

- Living Index: governing system.
- Spatial Editions: bounded expressive mode.
- Material Atlas: bounded evidence mode.

---

## Structural Signature

### Six discovery axes

```text
OBJECT    What is it?
ROOM      Where does it live?
MATERIAL  What is it made from?
STYLE     What visual language does it belong to?
MAKER     Who created it?
PROJECT   What am I trying to complete?
```

These axes form a graph, not six unrelated navigation lists.

Example path:

```text
Living Room
-> Sofas
-> Upholstery Fabric
-> Curved Forms
-> European Makers
-> Complete the Room
```

### Four experience modes

#### Index Mode

Purpose:

- orient;
- search;
- compare;
- refine;
- move quickly.

Surfaces:

- department;
- category;
- search;
- brand directory;
- material directory;
- saved items.

Visual behavior:

- compact sans-serif typography;
- visible counts and state;
- disciplined four-column product rhythm on desktop;
- low-decoration controls;
- short transitions;
- limited editorial interruptions.

#### Scene Mode

Purpose:

- inspire;
- establish spatial relationships;
- introduce campaigns and rooms;
- make curation visible.

Surfaces:

- homepage;
- room;
- style;
- collection;
- campaign;
- editorial commerce.

Visual behavior:

- full-bleed or strongly framed photography;
- asymmetric but grid-anchored composition;
- display typography used sparingly;
- directly shoppable objects;
- slower, bounded motion.

#### Object Mode

Purpose:

- resolve product uncertainty;
- explain configuration;
- prove material and fit;
- establish price and delivery confidence.

Surfaces:

- PDP;
- product comparison;
- material/specification views.

Visual behavior:

- dominant product media;
- persistent decision summary;
- layered facts from essential to technical;
- evidence and provenance;
- one coherent commerce state.

#### Service Mode

Purpose:

- complete tasks safely;
- reduce cognitive load;
- recover from errors;
- support the customer after purchase.

Surfaces:

- cart;
- checkout;
- account;
- orders;
- returns;
- consultation;
- trade application.

Visual behavior:

- calm white surfaces;
- minimal expressive typography;
- strong progress and validation;
- explicit totals, delivery, and next action;
- no decorative campaign content inside critical transactions.

---

## Six Page-Family Systems

| Family | Primary task | Default mode | Required proof |
| --- | --- | --- | --- |
| Gateway | Choose a meaningful entry | Scene + Index | visible category and product paths |
| Index | Find and compare | Index | count, refine, sort, product evidence |
| Scene | Explore a context | Scene + Index | shoppable objects and spatial logic |
| Object | Decide on a product | Object | media, price, variants, fit, delivery |
| Project | Compose and request guidance | Scene + Object | saved context, products, service state |
| Transaction | Complete and manage purchase | Service | validation, totals, trust, recovery |

---

## Visual Grammar

### Color behavior

The current warm neutral palette remains a supporting material system. Purple
becomes the command system.

Proposed semantic roles for prototype validation:

| Role | Prototype value | Use |
| --- | --- | --- |
| Command 700 | `#7F187F` | primary action, active navigation, search action |
| Command 900 | `#4E0E52` | high-contrast command, dark interactive state |
| Command 100 | `#F3E8F4` | selected surface, soft emphasis |
| Ink 950 | `#171419` | primary text and strong structure |
| Ink 650 | `#5E5860` | secondary text |
| Canvas 0 | `#FFFFFF` | browse and transaction surface |
| Canvas 50 | `#F7F5F7` | index grouping |
| Warm 50 | `#F8F5EF` | scene and material context |
| Line 200 | `#DED9E0` | boundaries and controls |
| Sale 700 | `#B3264A` | price reduction only |

Rules:

- purple is not a generic section background;
- bronze is removed from essential interaction and retained only as a material
  or editorial accent;
- white is the default commerce canvas;
- warm ivory belongs primarily to Scene and Object evidence moments;
- all semantic pairs require WCAG 2.2 AA verification.

### Typography behavior

Three voices are required:

| Voice | Role | Surfaces |
| --- | --- | --- |
| Commerce Sans | navigation, products, filters, forms, price | Index and Service |
| Editorial Display | campaign, room, collection, authored statements | Scene |
| Evidence Sans/Mono | SKU, dimensions, lead time, specifications | Object and professional |

Rules:

- Georgia/Times is not accepted as the final production display identity;
- operational copy does not use the editorial serif by default;
- uppercase tracking is restricted to short labels;
- operational text remains at least 14px equivalent;
- numerals must be selected and tested for price and dimension clarity;
- final font selection requires EN/DE coverage, licensing, variable or efficient
  loading, and accessible rendering.

### Grid and density

Prototype grid:

| Range | Columns | Outer gutter | Gap |
| --- | ---: | ---: | ---: |
| 1440+ | 12 | 48px | 24px |
| 1024-1439 | 12 | 32px | 20px |
| 768-1023 | 8 | 24px | 16px |
| 0-767 | 4 | 16px | 12px |

Density rules:

- Index Mode prioritizes visible options and comparison;
- Scene Mode may consume scale only when it contains authored imagery and a
  clear path to products;
- Object Mode balances media and decision information in the first viewport;
- Service Mode prioritizes completion and error prevention;
- blank space never substitutes for missing content.

### Imagery

Four image contracts:

1. **Scene:** room-scale, spatial context, restrained styling.
2. **Object:** complete silhouette, neutral authored background.
3. **Evidence:** material, joinery, surface, mechanism, construction.
4. **Human scale:** hand, body, circulation, or use when scale is otherwise
   ambiguous.

Adjacent images require consistent light direction, temperature, crop logic,
and shadow behavior.

### Iconography

- one coherent outline family;
- 1.5-2px optical stroke range;
- 20px standard UI icon, 24px touch-control icon;
- icon-only actions require accessible names and 44px targets;
- no mixed Bootstrap, custom, and decorative icon languages in one journey.

### Motion

| Mode | Duration | Purpose |
| --- | ---: | --- |
| Index | 120-180ms | state, refine, navigation feedback |
| Scene | 240-420ms | bounded spatial transition |
| Object | 160-240ms | media, configuration, evidence reveal |
| Service | 100-160ms | validation, progress, confirmation |

All motion requires a reduced-motion equivalent. No motion is accepted solely
as a signal of luxury.

---

## Prototype Contract

The vision is not approved from this document alone. It must be proven across
five connected surfaces at desktop and mobile widths.

### Prototype 1 — Homepage Gateway

Must prove:

- Veylune identity within the first viewport;
- one strong Scene moment;
- immediate Index entry;
- product proof before prolonged editorial content;
- consistent command color;
- no empty merchandising modules.

### Prototype 2 — Global Header and Mega-Menu

Must prove:

- 15+ future departments without visual collapse;
- six discovery axes without duplication;
- search as a primary command;
- desktop and mobile hierarchy;
- keyboard and focus behavior.

### Prototype 3 — Department Gateway

Must prove:

- category breadth;
- visual shortcut hierarchy;
- campaign interruption;
- product proof;
- related Room, Material, Style, and Maker paths.

### Prototype 4 — Product Listing Index

Must prove:

- dense but premium product comparison;
- count, filter, sort, selected facets, and recovery;
- product card information hierarchy;
- mobile filter/sort containment;
- loading, empty, and no-result behavior.

### Prototype 5 — Product Object Dossier

Must prove:

- dominant media and complete silhouette;
- coherent commerce state;
- variant, price, delivery, dimensions, and material clarity;
- evidence and provenance;
- related room and product paths;
- consultation only where complexity justifies it.

---

## Implementation Impact

### Retain

- public route ownership and fail-closed product exposure;
- current responsive asset inventory for prototype use;
- working Shopware account, catalog, and commerce foundations;
- existing purple marketplace recognition;
- route and governance test infrastructure.

### Refactor

- token architecture into semantic roles;
- global typography roles;
- header information architecture;
- destination templates into Page-Family systems;
- product card variants into one owned component contract;
- phase-specific SCSS into layered foundations, components, compositions, and
  temporary migration shims.

### Replace

- generic Georgia/Times display identity;
- bronze-led operational hierarchy;
- one visual mode stretched across all pages;
- oversized editorial spacing without product proof;
- repeated bordered cards as the default grouping device.

### Retire

- public governance language;
- fake or disabled actions presented as active commerce;
- empty public merchandising modules;
- page-specific typography exceptions;
- decorative motion without state meaning.

### Blocked until evidence

- final font licensing;
- public product breadth;
- ratings and Best Seller claims;
- supplier/provenance claims;
- direct purchase states for unapproved products.

---

## Acceptance Criteria

### Vision acceptance

- The five prototypes visibly belong to one system without looking identical.
- A first-time user can distinguish inspiration, browse, product decision, and
  transaction modes.
- The experience is identifiable as Veylune when the logo is temporarily hidden
  in research testing.
- Catalog breadth remains legible at the planned scale.
- The system supports customer and professional depth without mixing both in
  every interface.

### Commerce acceptance

- Product/category proof appears in the first meaningful viewport.
- Every visible action has a real destination or state transition.
- Price, availability, delivery, and primary action remain unambiguous.
- Empty public modules suppress or convert to a purposeful discovery state.
- Index patterns support keyboard, touch, zoom, and mobile use.

### System acceptance

- New work uses semantic tokens rather than new raw color and type decisions.
- Components have explicit owner, states, responsive behavior, and content
  requirements.
- No production component depends on a page-specific override for its normal
  appearance.
- Visual regression baselines exist for the five prototype surfaces.
- CSS growth and specificity are measured during migration.

### Performance acceptance

- LCP target: <= 2.5s at the agreed test profile.
- INP target: <= 200ms.
- CLS target: <= 0.1.
- One true LCP image receives priority; below-fold media remains deferred.
- Fonts are self-hosted or licensed for compliant delivery, subset responsibly,
  and loaded without layout instability.

### Governance acceptance

- Draft and private products remain inaccessible from public prototypes.
- Public/private route separation remains tested.
- All five prototypes have desktop and mobile route smoke tests.
- Founder approval records the selected direction and any accepted exceptions.

---

## Founder Decision

### Council recommendation

Approve:

- **The Living Index** as the governing Veylune experience system;
- **Spatial Editions** as bounded Scene Mode;
- **Material Atlas** as bounded evidence and professional mode;
- the five-surface prototype contract as the next implementation package.

### Decision state

`Pending Founder approval`

### If approved

The next package is `VS-02 North-Star Prototype`, covering:

1. homepage gateway;
2. global header and mega-menu;
3. department gateway;
4. PLP index;
5. PDP object dossier;
6. desktop and mobile states;
7. visual, accessibility, performance, and route acceptance evidence.

No broad storefront restyle should begin before VS-02 demonstrates the system
across all five surfaces.
