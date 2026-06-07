# VEYLUNE STUDIO

## WP-OP-01B — Governed Assortment Blueprint

**Status:** Architecture and acquisition blueprint
**Scope:** Audit and planning only
**Operating horizon:** 20 → 50 → 100 → 1000+ eligible products
**Implementation authority:** None

---

## 1. Executive Decision

Veylune should scale one governed assortment, not a collection of supplier catalogs.

The six approved departments remain the permanent top-level structure:

1. Furniture
2. Lighting
3. Decor & Objects
4. Textiles & Rugs
5. Dining & Kitchen
6. Outdoor

Every product must have exactly one primary product type within one of these departments. Room, material, collection, style, supplier, and editorial relationships are governed attributes and discovery relationships; they must not create competing primary taxonomies.

The complete product-type registry is approved now. Public category pages should be published only when they have sufficient eligible assortment depth. This allows Veylune to classify 20 products consistently and scale beyond 1000 without redesigning the category tree.

### Governing outcomes

- No additional top-level department is required.
- The internal product-type registry is stable from Stage A onward.
- A product has one primary department and one primary product type.
- Dining tables belong to Dining & Kitchen; dining chairs remain Furniture.
- Outdoor furniture belongs to Outdoor; indoor furniture does not become Outdoor through collection assignment.
- Outdoor-rated lighting remains Lighting, with Outdoor room relevance.
- Bedroom textiles remain Textiles & Rugs, with Bedroom room relevance.
- Founder Selection is an approval state, never a product type.
- Sparse product types remain valid internally but are not automatically exposed as public category pages.

---

# PART I — Final Product-Type Taxonomy

## 2. Taxonomy Operating Rules

### 2.1 Classification contract

Each product must possess:

- one `department_key`;
- one `product_type_key`;
- one governed primary category derived from those keys;
- zero or more governed room relationships;
- one primary material and governed secondary materials;
- zero or more collection memberships;
- one consultation mode;
- one readiness and exposure state.

The product type answers **what the product is**. Room, material, collection, style, use case, and supplier must not alter that answer.

### 2.2 Public category publication threshold

An approved product type may exist in the registry while hidden from navigation. A dedicated public category page requires:

- at least four Level 3 Exposure Ready products;
- at least two additional governed products in the 90-day pipeline;
- distinct customer intent not already served by a sibling page;
- complete filter, copy, image, and canonical-route governance;
- Taxonomy Owner approval.

A new child type below the taxonomy defined here requires at least 24 eligible products in the parent, at least eight eligible products in each proposed child, and evidence of distinct customer intent. Supplier terminology alone is not evidence.

### 2.3 Duplication controls

- One product cannot have two primary product types.
- “Dining furniture,” “bedroom furniture,” and “workspace furniture” are discovery groupings, not product types.
- Materials never become product types.
- Styles never become product types.
- “New,” “best seller,” “exclusive,” and “founder selected” are governed states or collections.
- `decorative_objects` is a controlled residual type and may not exceed 15% of Decor & Objects. Exceeding the limit triggers taxonomy review, not automatic category creation.

## 3. Approved Product-Type Registry

### 3.1 Furniture

| Product Type | Registry Key | Scope |
| --- | --- | --- |
| Sofas | `sofas` | Fixed, modular, sectional, and daybed-scale upholstered seating |
| Lounge Chairs | `lounge_chairs` | Indoor occasional and statement seating |
| Dining Chairs | `dining_chairs` | Chairs designed for dining-table use |
| Office Chairs | `office_chairs` | Desk and task seating |
| Benches & Stools | `benches_stools` | Benches, ottomans, counter stools, and low stools |
| Coffee Tables | `coffee_tables` | Primary low tables for living spaces |
| Side Tables | `side_tables` | Occasional, bedside, and compact companion tables |
| Consoles | `consoles` | Narrow display and hallway tables |
| Desks | `desks` | Writing, executive, and compact work desks |
| Beds | `beds` | Bed frames and upholstered beds |
| Storage | `storage` | Cabinets, sideboards, shelving, and chests |

**Boundary decision:** Dining tables are not duplicated here. They belong to Dining & Kitchen. Dining chairs remain Furniture because their construction, comparison behavior, and assortment governance align with seating.

### 3.2 Lighting

| Product Type | Registry Key | Scope |
| --- | --- | --- |
| Floor Lamps | `floor_lamps` | Freestanding ambient, reading, and architectural lamps |
| Table Lamps | `table_lamps` | Portable desk, bedside, and occasional lamps |
| Pendant Lights | `pendant_lights` | Suspended single and multi-light fixtures |
| Wall Lighting | `wall_lighting` | Sconces and fixed wall-mounted fixtures |

Ceiling-mounted products that are not pendants remain out of acquisition scope until assortment evidence justifies a controlled child type. They must not be placed in Pendant Lights merely for convenience.

### 3.3 Decor & Objects

| Product Type | Registry Key | Scope |
| --- | --- | --- |
| Vessels | `vessels` | Decorative vessels, vases, and non-tableware containers |
| Sculptural Objects | `sculptural_objects` | Freestanding art objects and materially led forms |
| Mirrors | `mirrors` | Wall, floor, and tabletop mirrors |
| Trays | `trays` | Decorative and valet trays not governed as serveware |
| Decorative Objects | `decorative_objects` | Controlled residual for bookends, candleholders, and objects without a stable dedicated type |

Functional food-service objects belong to Dining & Kitchen even when visually decorative.

### 3.4 Textiles & Rugs

| Product Type | Registry Key | Scope |
| --- | --- | --- |
| Rugs | `rugs` | Area, runner, and outdoor-rated rugs |
| Throws | `throws` | Decorative and functional throws |
| Cushions | `cushions` | Filled cushions and governed cushion-cover products |

Room-specific textiles use room attribution. “Bedroom textiles” and “outdoor textiles” must not become duplicate product types.

### 3.5 Dining & Kitchen

| Product Type | Registry Key | Scope |
| --- | --- | --- |
| Dining Tables | `dining_tables` | Indoor dining tables and materially equivalent dining-scale tables |
| Tableware | `tableware` | Plates, bowls, cups, and place-setting objects |
| Serveware | `serveware` | Serving boards, bowls, platters, and food-service trays |
| Kitchen Objects | `kitchen_objects` | Governed preparation, storage, and countertop objects |

Dining chairs remain Furniture. This department may present them through secondary discovery relationships but may not own a duplicate chair type.

### 3.6 Outdoor

| Product Type | Registry Key | Scope |
| --- | --- | --- |
| Outdoor Seating | `outdoor_seating` | Outdoor-rated lounge chairs, sofas, benches, and dining seating |
| Outdoor Tables | `outdoor_tables` | Outdoor-rated dining, coffee, and side tables |
| Planters & Objects | `planters_objects` | Outdoor-rated planters and materially durable decorative objects |

Outdoor classification requires documented outdoor suitability. Visual styling or supplier naming is insufficient.

---

# PART II — Assortment Coverage Gap Analysis

## 4. Baseline

The known governed pipeline contains four products:

- Aurelia Modular Sofa: eligible; Sofas
- Calma Travertine Table: eligible; Dining Tables
- Nocturne Floor Lamp: remediation required; Floor Lamps
- Atelier Stone Vessel: remediation required; Vessels

“Current” below distinguishes eligible coverage from remediation coverage. Targets are primary product-type counts, not room or collection memberships.

## 5. Coverage Gap Map

| Product Type | Current Coverage | Stage A Target (20) | Stage A Acquisition Gap | Stage C Target (100) | 1000+ Planning Count | Gap Priority |
| --- | ---: | ---: | ---: | ---: | ---: | --- |
| Sofas | 1 eligible | 1 | 0 | 6 | 45 | Low |
| Lounge Chairs | 0 | 0 | 0 | 5 | 45 | High |
| Dining Chairs | 0 | 0 | 0 | 4 | 35 | High |
| Office Chairs | 0 | 1 | 1 | 3 | 25 | Critical |
| Benches & Stools | 0 | 1 | 1 | 3 | 35 | Critical |
| Coffee Tables | 0 | 0 | 0 | 4 | 40 | High |
| Side Tables | 0 | 0 | 0 | 3 | 45 | High |
| Consoles | 0 | 1 | 1 | 2 | 25 | Critical |
| Desks | 0 | 1 | 1 | 2 | 25 | Critical |
| Beds | 0 | 1 | 1 | 2 | 20 | Critical |
| Storage | 0 | 0 | 0 | 2 | 30 | High |
| Floor Lamps | 1 remediation | 2 | 1 | 5 | 45 | Medium |
| Table Lamps | 0 | 1 | 1 | 5 | 50 | Critical |
| Pendant Lights | 0 | 1 | 1 | 4 | 45 | Critical |
| Wall Lighting | 0 | 1 | 1 | 4 | 40 | Critical |
| Vessels | 1 remediation | 2 | 1 | 4 | 35 | Medium |
| Sculptural Objects | 0 | 1 | 1 | 4 | 35 | Critical |
| Mirrors | 0 | 1 | 1 | 3 | 25 | Critical |
| Trays | 0 | 1 | 1 | 3 | 30 | Critical |
| Decorative Objects | 0 | 0 | 0 | 4 | 45 | Medium |
| Rugs | 0 | 1 | 1 | 5 | 45 | Critical |
| Throws | 0 | 0 | 0 | 2 | 25 | High |
| Cushions | 0 | 0 | 0 | 3 | 30 | High |
| Dining Tables | 1 eligible | 1 | 0 | 4 | 30 | Low |
| Tableware | 0 | 0 | 0 | 2 | 30 | High |
| Serveware | 0 | 0 | 0 | 2 | 25 | High |
| Kitchen Objects | 0 | 0 | 0 | 2 | 15 | Medium |
| Outdoor Seating | 0 | 1 | 1 | 3 | 35 | Critical |
| Outdoor Tables | 0 | 1 | 1 | 3 | 25 | Critical |
| Planters & Objects | 0 | 0 | 0 | 2 | 20 | Medium |

The 1000-product planning counts total 1000 and are directional portfolio controls, not purchasing quotas.

### 5.1 Priority interpretation

**Critical:** Required to make the Stage A room and department destinations credible.
**High:** Required during the move from 20 to 50 products to remove obvious assortment omissions.
**Medium:** Adds depth or completes a department after core commerce coverage exists.
**Low:** Already represented in the eligible baseline; depth still increases later.

### 5.2 Stage B target allocation

The 50-product portfolio should use this control:

| Department | Stage A | Stage B | Stage C | 1000+ |
| --- | ---: | ---: | ---: | ---: |
| Furniture | 6 | 19 | 36 | 370 |
| Lighting | 5 | 10 | 18 | 180 |
| Decor & Objects | 5 | 9 | 18 | 170 |
| Textiles & Rugs | 1 | 5 | 10 | 100 |
| Dining & Kitchen | 1 | 4 | 10 | 100 |
| Outdoor | 2 | 3 | 8 | 80 |
| **Total** | **20** | **50** | **100** | **1000** |

Stage B does not require every type to be populated. It requires every department to be credible and every critical or high-priority gap to have an approved acquisition decision.

---

# PART III — Discovery Coverage Mapping

## 6. Product-Type Discovery Matrix

Material entries indicate the most useful governed authority contributions, not decorative claims.

| Product Type | Principal Room Reach | Material Reach | Founder Potential | Consultation Default | Leverage |
| --- | --- | --- | --- | --- | --- |
| Sofas | Living | Fabric, leather, wood | High | Recommended | High |
| Lounge Chairs | Living, Bedroom, Workspace | Fabric, leather, wood, metal | High | Recommended | Very high |
| Dining Chairs | Dining, Workspace | Wood, metal, leather, fabric | Medium | Optional | High |
| Office Chairs | Workspace | Leather, fabric, wood, metal | Medium | Recommended | High |
| Benches & Stools | Hallway, Bedroom, Living, Dining | Wood, fabric, leather, metal | High | Optional | Very high |
| Coffee Tables | Living | Travertine, marble, stone, wood, glass, metal | High | Recommended | Very high |
| Side Tables | Living, Bedroom, Hallway | Stone, wood, metal, glass | High | Optional | Very high |
| Consoles | Hallway, Living, Dining | Travertine, marble, wood, metal | High | Recommended | Very high |
| Desks | Workspace, Bedroom | Wood, metal, leather | High | Recommended | Very high |
| Beds | Bedroom | Fabric, leather, wood | High | Recommended | High |
| Storage | Living, Dining, Bedroom, Workspace, Hallway | Wood, metal, glass | Medium | Recommended | Very high |
| Floor Lamps | Living, Bedroom, Workspace, Hallway | Metal, stone, glass, fabric | High | Optional | Very high |
| Table Lamps | Living, Bedroom, Workspace, Hallway | Ceramic, glass, metal, stone | High | Optional | Very high |
| Pendant Lights | Dining, Living, Hallway | Metal, glass, stone | High | Required | Very high |
| Wall Lighting | Living, Bedroom, Hallway, Workspace | Metal, glass, stone | High | Required | Very high |
| Vessels | Living, Dining, Bedroom, Hallway | Travertine, marble, stone, ceramic, glass | High | Optional | Very high |
| Sculptural Objects | Living, Dining, Workspace, Hallway | Stone, wood, metal, glass, ceramic | High | Optional | Very high |
| Mirrors | Hallway, Bedroom, Living | Glass, metal, wood, stone | High | Recommended | Very high |
| Trays | Hallway, Living, Bedroom, Dining | Travertine, marble, stone, wood, metal | Medium | Optional | High |
| Decorative Objects | Living, Dining, Bedroom, Workspace, Hallway | All governed hard materials | Medium | Optional | Medium |
| Rugs | Living, Dining, Bedroom, Workspace, Hallway, Outdoor | Wool, fabric | High | Recommended | Very high |
| Throws | Living, Bedroom, Outdoor | Wool, fabric | Medium | Optional | High |
| Cushions | Living, Bedroom, Outdoor | Fabric, wool, leather | Medium | Optional | High |
| Dining Tables | Dining, Workspace | Travertine, marble, stone, wood, metal, glass | High | Recommended | Very high |
| Tableware | Dining | Ceramic, glass, stone, metal | Medium | Optional | Medium |
| Serveware | Dining, Living, Outdoor | Wood, stone, marble, ceramic, metal, glass | High | Optional | High |
| Kitchen Objects | Dining | Wood, stone, ceramic, metal, glass | Low | Optional | Low |
| Outdoor Seating | Outdoor | Metal, wood, stone, fabric | High | Recommended | Very high |
| Outdoor Tables | Outdoor | Travertine, stone, wood, metal, glass | High | Recommended | Very high |
| Planters & Objects | Outdoor, Hallway | Stone, ceramic, metal, wood | High | Recommended | High |

### 6.1 Highest-leverage acquisition types

The strongest cross-surface types are:

1. Side Tables
2. Consoles
3. Benches & Stools
4. Floor and Table Lamps
5. Mirrors
6. Vessels and Sculptural Objects
7. Rugs
8. Storage
9. Dining Tables
10. Outdoor Seating and Tables

They combine category demand with multiple credible rooms, governed material visibility, and Founder Selection potential. Reach alone does not qualify a product; each individual room and material relationship still requires evidence.

---

# PART IV — First 16 Governed Products Blueprint

## 7. Acquisition Blueprint

These are net-new products beyond the four-product known pipeline. Names are working acquisition archetypes, not product records.

| ID | Product | Product Type | Department | Governed Rooms | Primary / Secondary Materials | Founder Potential |
| --- | --- | --- | --- | --- | --- | --- |
| P01 | Orbis Counterweighted Floor Lamp | Floor Lamps | Lighting | Living, Workspace | Metal / Travertine | Yes |
| P02 | Halo Ribbed-Glass Pendant | Pendant Lights | Lighting | Dining, Hallway | Glass / Metal | Yes |
| P03 | Lumen Ceramic Table Lamp | Table Lamps | Lighting | Bedroom, Living, Workspace | Ceramic / Fabric, Metal | Candidate |
| P04 | Axis Alabaster Wall Sconce | Wall Lighting | Lighting | Hallway, Bedroom | Stone / Metal | Yes |
| P05 | Tectona Travertine Vessel | Vessels | Decor & Objects | Hallway, Living, Dining | Travertine / None | Yes |
| P06 | Meridian Cast-Metal Sculpture | Sculptural Objects | Decor & Objects | Living, Workspace, Hallway | Metal / Stone | Yes |
| P07 | Arc Full-Length Oak Mirror | Mirrors | Decor & Objects | Bedroom, Hallway | Wood / Glass | Yes |
| P08 | Strata Marble Valet Tray | Trays | Decor & Objects | Hallway, Bedroom, Living | Marble / Leather | Candidate |
| P09 | Serein Upholstered Platform Bed | Beds | Furniture | Bedroom | Upholstery Fabric / Wood | Yes |
| P10 | Tactile Hand-Knotted Wool Rug | Rugs | Textiles & Rugs | Living, Bedroom, Workspace | Wool / None | Yes |
| P11 | Linea Solid-Oak Writing Desk | Desks | Furniture | Workspace, Bedroom | Wood / Leather, Metal | Yes |
| P12 | Forma Leather and Oak Desk Chair | Office Chairs | Furniture | Workspace | Leather / Wood, Metal | Candidate |
| P13 | Portico Travertine Console | Consoles | Furniture | Hallway, Living | Travertine / Metal | Yes |
| P14 | Stillwater Upholstered Oak Bench | Benches & Stools | Furniture | Hallway, Bedroom, Dining | Wood / Upholstery Fabric | Candidate |
| P15 | Terra Outdoor Lounge Chair | Outdoor Seating | Outdoor | Outdoor | Wood / Outdoor Fabric, Metal | Yes |
| P16 | Monolith Outdoor Stone Table | Outdoor Tables | Outdoor | Outdoor | Stone / Metal | Yes |

### 7.1 Selection rationale

The 16 products close every Stage A critical gap, represent all six departments, create credible entry points for all six governed rooms, and establish all ten canonical material families. Each product contributes to at least three governed discovery dimensions: product type, room, material, or approved collection potential.

### 7.2 Coverage scoring

Scoring uses:

- Category reach: 0–3
- Room reach: 0–3
- Material authority: 0–3
- Founder potential: 0–3
- Revenue utility: 0–3

| Rank | Product | Category | Room | Material | Founder | Revenue | Score / 15 |
| ---: | --- | ---: | ---: | ---: | ---: | ---: | ---: |
| 1 | Portico Travertine Console | 3 | 3 | 3 | 3 | 3 | 15 |
| 2 | Orbis Counterweighted Floor Lamp | 3 | 3 | 3 | 3 | 3 | 15 |
| 3 | Arc Full-Length Oak Mirror | 3 | 3 | 3 | 3 | 3 | 15 |
| 4 | Axis Alabaster Wall Sconce | 3 | 3 | 3 | 3 | 3 | 15 |
| 5 | Tactile Hand-Knotted Wool Rug | 3 | 3 | 3 | 3 | 3 | 15 |
| 6 | Linea Solid-Oak Writing Desk | 3 | 3 | 3 | 3 | 3 | 15 |
| 7 | Halo Ribbed-Glass Pendant | 3 | 3 | 3 | 3 | 2 | 14 |
| 8 | Tectona Travertine Vessel | 3 | 3 | 3 | 3 | 2 | 14 |
| 9 | Meridian Cast-Metal Sculpture | 3 | 3 | 3 | 3 | 2 | 14 |
| 10 | Stillwater Upholstered Oak Bench | 3 | 3 | 3 | 2 | 3 | 14 |
| 11 | Lumen Ceramic Table Lamp | 3 | 3 | 3 | 2 | 3 | 14 |
| 12 | Serein Upholstered Platform Bed | 3 | 2 | 3 | 3 | 3 | 14 |
| 13 | Terra Outdoor Lounge Chair | 3 | 2 | 3 | 3 | 3 | 14 |
| 14 | Monolith Outdoor Stone Table | 3 | 2 | 3 | 3 | 3 | 14 |
| 15 | Forma Leather and Oak Desk Chair | 3 | 2 | 3 | 2 | 3 | 13 |
| 16 | Strata Marble Valet Tray | 3 | 3 | 3 | 2 | 2 | 13 |

Scores rank acquisition leverage only. They do not confer exposure or Founder Selection eligibility.

### 7.3 Acquisition batches

**Batch 1 — Immediate coverage**

- Portico Travertine Console
- Orbis Counterweighted Floor Lamp
- Halo Ribbed-Glass Pendant
- Arc Full-Length Oak Mirror
- Tactile Hand-Knotted Wool Rug
- Linea Solid-Oak Writing Desk

This batch opens Lighting depth and makes Hallway, Workspace, and Bedroom materially more credible.

**Batch 2 — Room completion**

- Axis Alabaster Wall Sconce
- Lumen Ceramic Table Lamp
- Serein Upholstered Platform Bed
- Forma Leather and Oak Desk Chair
- Stillwater Upholstered Oak Bench

This batch completes the basic Bedroom and Workspace purchase systems and expands consultation-relevant furniture.

**Batch 3 — Authority and outdoor**

- Tectona Travertine Vessel
- Meridian Cast-Metal Sculpture
- Strata Marble Valet Tray
- Terra Outdoor Lounge Chair
- Monolith Outdoor Stone Table

This batch strengthens Decor & Objects, material authority, Founder Selection supply, and the first credible Outdoor stream.

No batch may be published as a group merely because it was acquired together. Every product must independently reach Level 3.

---

# PART V — Material Authority Coverage

## 8. Material Portfolio Targets

Primary material is the dominant construction or customer-decision material. Secondary materials must be structurally or experientially relevant. Packaging, minor fasteners, and decorative material language do not qualify.

| Canonical Material | Known Pipeline | Stage A Minimum | Stage B Minimum | Stage C Target | 1000+ Primary Share | Acquisition Priority |
| --- | --- | ---: | ---: | ---: | ---: | --- |
| Travertine | Calma verified | 3 | 4 | 8 | 6% | High |
| Marble | None | 1 | 3 | 6 | 6% | Critical |
| Stone | Atelier pending | 2 | 4 | 8 | 9% | Critical |
| Wood | None confirmed | 4 | 10 | 22 | 24% | Critical |
| Metal | Nocturne pending | 3 | 8 | 16 | 20% | Critical |
| Glass | None | 1 | 4 | 8 | 8% | Critical |
| Ceramic | None | 1 | 4 | 8 | 7% | Critical |
| Upholstery Fabric | Aurelia verified | 2 | 6 | 12 | 12% | High |
| Wool | None | 1 | 3 | 7 | 5% | Critical |
| Leather | None | 1 | 2 | 5 | 3% | Critical |

The Stage C target totals 100 primary-material assignments. The 1000+ shares total 100% and act as drift controls, not aesthetic quotas.

## 9. Material Governance Controls

- Only canonical material names may drive discovery.
- Specific stones may retain their specific name and map upward to Stone.
- “Natural,” “premium,” “luxury,” “artisan,” and color names are not materials.
- Supplier declarations require documentary evidence or physical verification.
- Composite, veneer, finish, and solid-material claims must remain distinguishable.
- Material confidence must be `verified`, `documented`, `observed`, or `unverified`.
- Only `verified` or `documented` primary materials qualify for material-led exposure.
- `observed` may support remediation but not authority claims.
- `unverified` blocks Level 3 exposure.
- Material mappings are versioned; changing a canonical family requires Material Authority Owner approval.

The first 16 close all Stage A material-family gaps. No acquisition should be approved solely to satisfy a percentage if the product lacks Veylune identity and commercial utility.

---

# PART VI — Room Coverage Strategy

## 10. Commercial Credibility by Room

| Room | Required Product Systems | Stage A Result |
| --- | --- | --- |
| Living Room | Sofa or lounge seating; coffee/side surface; floor/table lighting; rug; object or mirror | Credible entry stream, but coffee and side tables remain Stage B gaps |
| Dining Room | Dining table; dining seating; pendant; tableware/serveware; object or console | Credible anchor stream, but dining chairs and tabletop depth remain Stage B gaps |
| Bedroom | Bed; side surface; bedside/wall lighting; rug; mirror; bench or textile | Commercially credible after the first 16, with side tables and soft-textile depth still required |
| Workspace | Desk; office chair; task/ambient lighting; storage; rug or object | Commercially credible after the first 16, with storage as the principal Stage B gap |
| Hallway | Console or bench; mirror; wall/pendant lighting; tray/vessel/object; runner potential | Commercially credible after the first 16 |
| Outdoor | Outdoor seating; outdoor table; optional lighting/textile; planter/object | Minimum viable destination after the first 16; depth and planters remain required |

### 10.1 Room assignment rules

- A room assignment must reflect normal intended use, dimensions, safety, durability, and installation context.
- A product may have one primary room and multiple secondary rooms.
- Secondary rooms require the same evidence standard as the primary room.
- Search demand and SEO opportunity are not assignment evidence.
- Outdoor requires documented outdoor suitability.
- Dining assignment requires dining-scale function, not styling.
- Bedroom assignment requires credible bedroom function or placement.
- Room relationships are reviewed when product dimensions, construction, installation, or supplier evidence changes.
- A relationship is revoked when evidence no longer supports use, returns identify incompatibility, or governance review finds promotional overreach.

At 1000+ products, no room stream should depend on broad editorial tagging. It should be generated from governed room relationships and filtered by exposure state.

---

# PART VII — Founder Selection Pipeline

## 11. Qualified Candidates

### Strong candidates

- Orbis Counterweighted Floor Lamp
- Halo Ribbed-Glass Pendant
- Axis Alabaster Wall Sconce
- Tectona Travertine Vessel
- Meridian Cast-Metal Sculpture
- Arc Full-Length Oak Mirror
- Serein Upholstered Platform Bed
- Tactile Hand-Knotted Wool Rug
- Linea Solid-Oak Writing Desk
- Portico Travertine Console
- Terra Outdoor Lounge Chair
- Monolith Outdoor Stone Table

These products can express material legibility, restrained form, spatial presence, and lasting commercial relevance. They also strengthen categories or rooms that would otherwise appear operational rather than curated.

### Candidates requiring stronger evidence

- Lumen Ceramic Table Lamp
- Strata Marble Valet Tray
- Forma Leather and Oak Desk Chair
- Stillwater Upholstered Oak Bench

These are commercially useful but can easily become generic. Founder approval should require a distinctive proportion, construction decision, material resolution, or supplier provenance visible in the product itself.

## 12. Founder Selection Gate

A product may enter Founder Selection only when:

1. it is Level 3 Exposure Ready;
2. its material claims are verified or documented;
3. it has a clear Veylune identity rationale;
4. it contributes something not already overrepresented in the selection;
5. it is not included merely because it is new, expensive, or commercially convenient;
6. the Founder or delegated Curatorial Owner records approval and rationale;
7. it has a review or expiry date.

Founder Selection should remain selective:

| Stage | Suggested Active Range |
| --- | ---: |
| 20 products | 4–8 |
| 50 products | 8–12 |
| 100 products | 12–18 |
| 1000+ products | 30–50 |

These ranges are dilution controls, not quotas. Fewer products are acceptable; weak additions are not.

---

# PART VIII — Consultation Coverage Strategy

## 13. Objective Consultation Rules

### Consultation Required

Required when any condition is true:

- professional electrical installation is required;
- wall or ceiling structure determines safe installation;
- the product is made to order or materially configurable after purchase;
- access, assembly, weight, or placement must be validated before order acceptance;
- outdoor suitability depends on site exposure or anchoring;
- the product cannot be safely or accurately purchased from standard PDP data alone.

Typical types: Pendant Lights, Wall Lighting, configurable Sofas, oversized Mirrors, exceptional stone furniture, and anchored outdoor products.

### Consultation Recommended

Recommended when the PDP can support direct purchase but expert guidance materially reduces fit or expectation risk:

- large furniture with access constraints;
- scale-sensitive products;
- natural-material variation is commercially significant;
- seating comfort or upholstery selection affects the decision;
- room planning or product pairing materially affects suitability.

Typical types: Sofas, Lounge Chairs, Office Chairs, Coffee Tables, Consoles, Desks, Beds, Storage, Rugs, Dining Tables, Outdoor Seating, and Outdoor Tables.

### Consultation Optional

Optional when:

- dimensions and use are straightforward;
- no installation or configuration decision exists;
- standard shipping and returns apply;
- material variation is adequately disclosed;
- purchase risk is low.

Typical types: most Vessels, Sculptural Objects, Trays, Decorative Objects, Throws, Cushions, Tableware, Serveware, and Kitchen Objects.

### 13.1 Per-product assignment for the first 16

| Product | Consultation Mode | Governing reason |
| --- | --- | --- |
| Orbis Floor Lamp | Optional | Portable; standard placement and power |
| Halo Pendant | Required | Ceiling installation and drop-height decision |
| Lumen Table Lamp | Optional | Portable and dimensionally simple |
| Axis Wall Sconce | Required | Electrical and wall installation |
| Tectona Vessel | Optional | Low-complexity decorative object |
| Meridian Sculpture | Optional | Standard scale; upgrade if exceptional weight |
| Arc Mirror | Recommended | Placement, anchoring, and access risk |
| Strata Tray | Optional | Low-risk standard purchase |
| Serein Bed | Recommended | Mattress fit, access, assembly, and upholstery |
| Tactile Rug | Recommended | Scale, placement, and material expectation |
| Linea Desk | Recommended | Scale, access, and workspace fit |
| Forma Desk Chair | Recommended | Ergonomic and material suitability |
| Portico Console | Recommended | Stone variation, weight, access, and scale |
| Stillwater Bench | Optional | Standard dimensions; recommend if configurable |
| Terra Outdoor Chair | Recommended | Exposure, care, and placement |
| Monolith Outdoor Table | Required | Weight, access, placement, and site suitability |

Consultation mode is product data. It cannot be inferred from price, Founder Selection status, or supplier preference.

---

# PART IX — Scaling Blueprint

## 14. Stage A — 20 Eligible Products

### Taxonomy readiness

- All 30 product types exist in the controlled registry.
- Only populated departments and sufficiently credible discovery streams are exposed.
- Sparse product types remain metadata, not standalone public destinations.

### Material coverage

- All ten canonical material families represented.
- Every exposed product has governed primary and secondary materials.
- No unverified primary material enters exposure.

### Room coverage

- All six room destinations have an anchor assortment.
- Living and Dining retain priority for Stage B depth.
- Bedroom, Workspace, Hallway, and Outdoor become credible through the first 16.

### Founder coverage

- Four to eight active selections.
- Every selection has a written rationale and review date.

### Consultation coverage

- Every product has Required, Recommended, or Optional status.
- Required consultations have an operational response path before exposure.

## 15. Stage B — 50 Eligible Products

### Taxonomy readiness

- Critical gaps closed.
- High-priority types represented or governed in the active pipeline.
- Dedicated public product-type pages appear only at the publication threshold.

### Material coverage

- No canonical material depends on a single product.
- Wood, metal, stone, glass, ceramic, and upholstery have cross-department representation.
- Material confidence is audited quarterly.

### Room coverage

- Each room contains anchor, supporting, and finishing product systems.
- Storage, side tables, coffee tables, dining chairs, tabletop, throws, and cushions receive priority.

### Founder coverage

- Eight to twelve active selections.
- Selection balance reviewed across department, room, material, supplier, and price.

### Consultation coverage

- Consultation demand tracked by type and trigger.
- Repeated questions become PDP data requirements, not automatic consultation expansion.

## 16. Stage C — 100 Eligible Products

### Taxonomy readiness

- All 30 approved types represented or formally deferred with rationale.
- Department and type allocations follow the Stage C portfolio control.
- Child-category proposals must satisfy the 24/8 depth rule.

### Material coverage

- Primary-material targets total 100.
- Each authority material appears in more than one product type where commercially credible.
- Unsupported supplier language is rejected before ingestion.

### Room coverage

- Every room supports complete purchase journeys, not isolated products.
- Secondary room assignments undergo sample audit.
- Outdoor has seating, table, object, textile, and optional lighting relationships without taxonomy duplication.

### Founder coverage

- Twelve to eighteen active selections.
- Additions require a portfolio contribution test; removals are routine.

### Consultation coverage

- Rules are applied automatically where objective triggers exist, then reviewed by exception.
- Consultation outcomes feed dimensions, delivery, material, and installation data quality.

## 17. Stage D — 1000+ Eligible Products

### Taxonomy readiness

- Six departments remain unchanged.
- Product types remain canonical master data.
- Child types are introduced only through the depth and intent gate.
- Supplier categories map into Veylune types and never publish directly.
- Public navigation remains demand-led and may show fewer nodes than the registry contains.

### Material coverage

- Portfolio drift monitored against primary-material shares.
- Canonical names, aliases, evidence, and confidence are managed as versioned authority data.
- Material-led pages exclude products below the confidence threshold.

### Room coverage

- Room streams are generated from governed relationships.
- Assignment precision is sampled monthly and fully audited annually.
- Low-performing SEO pages do not trigger broader room tagging.

### Founder coverage

- Thirty to fifty active selections.
- Selection remains a narrow curatorial layer, not a best-seller feed.
- Supplier concentration and material repetition are explicit review dimensions.

### Consultation coverage

- Objective triggers derive the default mode.
- Exceptions require reason, owner, and review date.
- Capacity planning uses Required and Recommended product exposure, not total catalog size.

## 18. Governance Workload

| Scale | Governance model | Review cadence |
| --- | --- | --- |
| 20 | Named owners may review every product manually | Pre-publication and monthly portfolio review |
| 50 | Checklists, controlled registries, and exception logs required | Weekly intake; monthly attribution audit |
| 100 | Batch ingestion with independent QA sampling | Weekly exceptions; monthly samples; quarterly portfolio review |
| 1000+ | Rules-assisted validation with human approval for exceptions and curation | Continuous validation; monthly risk audit; quarterly taxonomy and founder review |

Automation may validate completeness, canonical values, duplicate mappings, and thresholds. It must not approve material truth, room relevance, or Founder Selection without accountable human authority.

---

# PART X — Assortment Governance Risks

## 19. Risk Register

| Risk | Failure mode | Prevention control |
| --- | --- | --- |
| Category inflation | Every supplier term becomes a category | Controlled registry; publication threshold; Taxonomy Owner approval |
| Taxonomy duplication | Dining, outdoor, room, or style copies the product tree | One primary type; rooms and collections remain relationships |
| Style fragmentation | Trend labels become permanent branches | Styles remain governed attributes or editorial pages |
| Residual-type growth | Decorative Objects becomes an ungoverned catch-all | 15% department cap and quarterly contents review |
| Weak material attribution | Visual guesses become authority claims | Evidence and confidence gate before Level 3 |
| Material alias drift | Similar names fragment discovery | Canonical dictionary with versioned aliases |
| SEO-driven room assignment | Products are assigned to every searchable room | Evidence-based room rules and revocation audit |
| Supplier-driven assortment | Supplier availability overrides portfolio need | Acquisition brief scores coverage before supplier fit |
| Supplier taxonomy leakage | Imported categories appear publicly | Mandatory mapping into Veylune registry |
| Founder dilution | Selection becomes a large promotional feed | Active-range control, rationale, expiry, and removal |
| Consultation overreach | Consultation becomes a generic luxury signal | Objective trigger rules and exception review |
| Consultation underreach | Complex products expose without support | Required-mode block in exposure eligibility |
| Sparse public pages | Thin types reduce trust and discovery quality | Four-product plus pipeline publication threshold |
| Coverage-score gaming | Low-authority products are acquired for reach | Level 3 and identity gates remain independent |
| Collection duplication | Collections replace taxonomy or rooms | Collection purpose, owner, entry rule, and expiry required |
| Founder/supplier concentration | Curation appears commercially biased | Portfolio review by supplier, type, room, and material |

## 20. Decision Rights

| Decision | Accountable owner |
| --- | --- |
| Add or retire department | Executive governance; exceptional architecture decision |
| Add, merge, or retire product type | Taxonomy Owner with Commerce approval |
| Publish a product-type category | Taxonomy Owner and Commerce Owner |
| Approve canonical material or alias | Material Authority Owner |
| Approve or revoke room relevance | Catalog Governance Owner |
| Approve collection membership | Named Collection Owner |
| Approve Founder Selection | Founder or delegated Curatorial Owner |
| Set consultation exception | Commerce Operations Owner |
| Approve Level 3 exposure | Exposure Governance Owner |

No supplier may hold a governance approval right.

---

# DELIVERABLE SUMMARY

## A. Final Product-Type Taxonomy

Six immutable departments and 30 governed product types are defined. Primary classification is unique; room, material, collection, and style are controlled relationships.

## B. Coverage Gap Map

The baseline has two eligible types and two remediation types. Fourteen critical Stage A gaps are closed by the first 16 products. High-priority Stage B gaps concentrate on living, dining, storage, tabletop, and textile depth.

## C. Discovery Coverage Matrix

Every type has defined room reach, material reach, Founder potential, consultation default, and discovery leverage. Cross-surface value does not override attribution evidence.

## D. First 16 Governed Products

The acquisition blueprint delivers four lighting products, four decor products, six furniture/textile products, and two outdoor products while preserving the approved department model.

## E. Material Coverage Plan

All ten authority materials receive Stage A representation and explicit 50-, 100-, and 1000-product controls. Material confidence remains an exposure gate.

## F. Founder Selection Pipeline

Twelve strong candidates and four conditional candidates are identified. Selection requires Level 3 readiness, material evidence, portfolio contribution, accountable approval, and review.

## G. Scaling Roadmap

The same registry supports 20, 50, 100, and 1000+ products. Public taxonomy depth grows only when product depth and customer intent justify it.

## H. Governance Risks

The blueprint controls category inflation, duplication, weak attribution, SEO pressure, supplier influence, consultation misuse, and Founder Selection dilution.

---

## Final Acceptance Standard

The assortment architecture is successful when Veylune can add suppliers and products without changing the six-department model; every product resolves to one stable product type; discovery relationships remain evidence-based; material claims remain authoritative; consultation is objectively assigned; and Founder Selection stays intentionally narrow.

Product count alone is not progress. Governed coverage is progress.
