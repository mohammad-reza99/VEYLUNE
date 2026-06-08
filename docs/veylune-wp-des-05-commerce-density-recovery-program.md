# VEYLUNE STUDIO - WP-DES-05

## Commerce Density Recovery Program

**Date:** 7 June 2026  
**Phase:** Implementation planning only  
**Current perception:** 70% luxury editorial / 30% luxury commerce  
**Target perception:** 30% luxury editorial / 70% luxury commerce

## Executive Finding

Veylune currently communicates taste before it communicates purchasable
selection. The visual language is calm and premium, but the ratio of page
height to product exposure is commercially ineffective.

At a 1440 x 900 desktop viewport:

- the homepage is 7,214 px tall but exposes only two unique products;
- homepage product cards are approximately 669 x 1,224 px;
- destination product cards are approximately 649 x 1,199 px;
- the homepage first viewport contains no product;
- the first three homepage viewport bands introduce only two unique products;
- room and category pages repeatedly devote more than one viewport to the same
  two oversized cards.

The recovery principle is:

> Retain premium restraint inside each component, while placing substantially
> more useful components inside every viewport.

Luxury should come from image quality, hierarchy, restraint, alignment, and
selection. It should not come from oversized cards or low information density.

---

# PART I - Discovery Density Audit

## Measurement Method

The rendered storefront was measured at 1440 x 900. The page was divided into:

1. first viewport: 0-900 px;
2. first scroll: 900-1,800 px;
3. second scroll: 1,800-2,700 px.

A meaningful discovery opportunity is a visible product, category, room,
collection, material choice, or actionable commerce destination. Elements
crossing two bands are not new opportunities in the second band.

## Current Density

| Page | First Viewport | First Scroll | Second Scroll | Main Failure |
| --- | ---: | ---: | ---: | --- |
| Homepage | 2 hero actions, 0 products | 1 category and first sight of 2 products | Same 2 products continue | Almost three viewports produce only two products |
| Furniture | 4 shortcuts, 0 products | 4 materials and first sight of 2 products | Same 2 products continue | Product grid begins around 1,717 px |
| Living Room | 1 category shortcut, 0 products | First sight of 2 products | Same 2 products continue | Room proposition has almost no browsing depth |
| Founder Selection | First sight of 2 products | Same 2 products continue | Same 2 products continue | Cards consume most of the page |

## Scroll Reward

Current scroll reward is weak because page movement does not consistently
introduce new choices.

| Page | Page Height | Unique Products | Approx. Height per Unique Product |
| --- | ---: | ---: | ---: |
| Homepage | 7,214 px | 2 | 3,607 px |
| Furniture | 3,943 px | 2 | 1,972 px |
| Living Room | 2,927 px | 2 | 1,464 px |
| Founder Selection | 2,539 px | 2 | 1,270 px |

The target is not a literal product every fixed number of pixels. The practical
target is that every viewport after the hero introduces either:

- four or more products;
- four or more compact discovery choices; or
- one strong merchandising story accompanied by at least four products.

## Westwing Gap

Westwing's current homepage presents broad category access, multiple commercial
campaigns, room-oriented discovery, and product sequences with names and
prices. Its room and product-type pages move quickly into repeated product
cards, filters, sorting, and adjacent category choices.

Veylune currently presents one or two large visual statements where Westwing
typically presents several actionable choices. The relevant gap is not visual
decoration. It is browsing velocity:

| Measure | Current Veylune | Recovery Target | Westwing Pattern |
| --- | ---: | ---: | --- |
| Desktop products per complete row | 2 | 4 | Dense multi-column product grid |
| Products visible when a grid begins | 2 | 4, with next row implied | Multiple immediately comparable products |
| Product entry on homepage | After category block, around second viewport | First viewport edge or immediately after hero | Product and campaign access appears early |
| New choices per post-hero viewport | Usually 0-2 | 4-8 | Continuous category, campaign, and product choices |
| Repeated card height | About 1,200 px | About 520-650 px | Compact commerce cards |

The Westwing figures above describe observed interface patterns and target
ranges, not a pixel-for-pixel replication.

---

# PART II - Product Exposure Audit

## Current Exposure

| Surface | Unique Products Visible | Grid Columns | Exposure Assessment |
| --- | ---: | ---: | --- |
| Homepage New Arrivals | 2 | 2 | Severely under-exposed |
| Furniture | 2 | 2 | Not commercially browseable |
| Living Room | 2 | 2 | Not a credible room browse |
| Dining Room | 1 | 2-column treatment | Too little comparison |
| Founder Selection | 2 | 2 | Curation is visible but too shallow |
| New Arrivals | 2 | 2 | Does not communicate arrival velocity |

The same two products do most of the work across the storefront. Repetition is
acceptable when context changes, but it cannot substitute for visible breadth.

## Required Exposure Targets

| Surface | Minimum Initial Exposure | Preferred Mature Exposure |
| --- | ---: | ---: |
| Homepage product module | 4 products | 8 products across two compact rows or a carousel |
| Homepage total product impressions | 8 | 16-24 |
| Category initial grid | 8 products | 12-16 before editorial interruption |
| Room initial grid | 6 products | 8-12 |
| Collection initial grid | 6 products | 8-12 |
| Desktop row | 4 products | 4 products |
| Tablet row | 3 products | 3 products |
| Mobile row | 2 products | 2 products |

These are rendered-experience targets. A surface should show fewer products
only when every item has unusually high commercial importance and the next
choice remains visible without a long scroll.

## Under-Exposed Areas

1. The homepage hero contains no product proof.
2. Categories appear before products but occupy a full-width, low-density card.
3. New Arrivals uses two cards at half-page width.
4. Room and collection modules use large cards for only two links.
5. Category pages delay product entry with an oversized introduction,
   shortcuts, and materials block.
6. Room pages delay products despite having very little pre-grid information.
7. Collection pages start products earlier, but card size prevents comparison.
8. Product descriptions inside cards consume vertical space better reserved
   for additional products.
9. Large media ratios and long card bodies prevent a second row from becoming
   visible.
10. Repeated editorial headings consume space without adding choices.

---

# PART III - Commerce Density Recovery

## Density Targets

### Desktop

- Use four product columns between 1280 and 1440 px.
- Target product-card width: 300-330 px.
- Target complete product-card height: 520-650 px.
- Keep image ratio near 4:5, but remove nonessential body height.
- Show four complete products or four products plus part of the next row when
  the grid enters the viewport.
- Use 20-28 px horizontal grid gaps.
- Use 48-72 px between a section heading and its next section, not 100+ px.

### Tablet

- Use three product columns.
- Keep essential product information visible without interaction.
- Avoid changing to two columns until card width would fall below about 220 px.

### Mobile

- Use two product columns for browse surfaces.
- Use compact titles, prices, and availability states.
- Avoid full-width product cards except for a deliberately featured item.
- Target 10-16 px gaps and 16-20 px page insets.

## Product Card Content Order

Every standard browse card should communicate, in this order:

1. product image;
2. product name;
3. concise product type or material;
4. price;
5. availability or consultation state;
6. restrained secondary action.

Standard cards should not contain:

- paragraph-length descriptions;
- repeated studio labels;
- oversized editorial badges;
- a large standalone action area;
- metadata that forces price below the fold of the card.

## Stronger Discovery Surfaces

- Product rows should become the default content unit.
- Category, room, and collection links should become compact navigation
  modules, not hero-scale cards.
- Editorial modules should sit between product groups and should lead directly
  back into products.
- Every large image should either contain product references or be followed
  immediately by a product row.
- Section headings should identify the commercial choice in one line.

---

# PART IV - Homepage Commerce Recovery

## Recommended Content Ratio

Measured by visible page area:

| Content Type | Current Approximation | Target |
| --- | ---: | ---: |
| Products | 20% | 45% |
| Categories | 10% | 15% |
| Rooms | 10% | 12% |
| Collections | 10% | 10% |
| Editorial and brand content | 50% | 18% |

Measured by actionable opportunities:

| Opportunity Type | Target Share |
| --- | ---: |
| Product actions | 60% |
| Category actions | 15% |
| Room actions | 10% |
| Collection actions | 10% |
| Editorial/service actions | 5% |

## Recommended Homepage Sequence

The recommended density sequence is:

1. Compact hero with one dominant commerce action.
2. Four-product New Arrivals row visible at the first viewport boundary.
3. Compact category access.
4. Eight-product primary merchandising grid.
5. Compact room access.
6. Four-product room-led row.
7. Compact collection access.
8. Four-product Founder Selection row.
9. One restrained editorial or service module.

## Homepage Targets

- First product visible no later than 750-950 px from page top.
- At least four products introduced by 1,600 px.
- At least eight products introduced by 2,700 px.
- No non-product module taller than one desktop viewport.
- No discovery card taller than 180 px unless it contains product imagery and a
  direct commercial purpose.
- Reduce total homepage height while increasing product impressions.

---

# PART V - Card System Audit

## Product Cards

**Current**

- Approximately 649-669 px wide.
- Approximately 1,199-1,224 px tall.
- Two columns on desktop.
- Long descriptions and multiple metadata layers.
- The same card remains visible across more than one viewport band.

**Target**

- 300-330 px wide on desktop.
- 520-650 px total height.
- Four columns.
- One or two lines for title.
- One short metadata line.
- Price and availability visible without scrolling inside the card.
- Description removed from standard browse cards.

## Room Cards

**Current**

- Approximately 669 x 240 px on the homepage.
- Two cards occupy a full row.
- Large blank surfaces carry little information.

**Target**

- Four compact room entries per desktop row when four or more are present.
- 140-180 px height for text-led cards.
- 220-280 px height only when useful room imagery exists.
- Room name, concise cue, and item-count or product preview.

## Collection Cards

**Current**

- Approximately 669 x 240 px.
- Two cards consume a full row.
- Collection promise is not reinforced by visible products in the same module.

**Target**

- Three or four columns depending on count.
- 160-220 px text-led height or 240-320 px image-led height.
- Pair each collection access row with a compact product row.
- Use hierarchy, not physical scale, to communicate importance.

## Category Cards

**Current**

- The sole visible category card spans approximately 1,360 x 170 px.
- It reads as a large banner rather than efficient discovery.

**Target**

- Compact horizontal category strip or cards.
- 100-150 px height.
- Several category or product-type choices visible without scrolling.
- When only one category is active, use a compact text link group rather than a
  full-width empty-feeling card.

---

# PART VI - Westwing Extraction

## Patterns to Adopt

1. **Immediate breadth:** categories and commercial campaigns appear early.
2. **Four-column comparison:** products can be compared without large eye
   movements or excessive scrolling.
3. **Repeated product re-entry:** users repeatedly encounter products after
   each discovery or inspiration module.
4. **Visible price hierarchy:** product name, brand, current price, and previous
   price are quickly scannable.
5. **Adjacent discovery:** room, type, material, and campaign choices remain
   near product grids.
6. **Browsing momentum:** each viewport supplies several next actions.
7. **Merchandising interruption:** campaign imagery breaks grids without
   replacing them.

## Veylune Translation

- Use fewer promotional signals than Westwing.
- Retain warmer whitespace and quieter typography.
- Use four-column grids but larger gaps and less badge density.
- Prefer one strong merchandising message over several competing promotions.
- Keep product imagery consistent and material-led.
- Use curated ordering rather than visual scarcity.

Veylune should borrow Westwing's commercial cadence, not its promotional
volume.

---

# PART VII - Apple Extraction

## Patterns to Adopt

1. One clear purpose per section.
2. Short headings with direct action language.
3. Consistent alignment and container behavior.
4. Strong image quality with minimal framing.
5. Predictable component hierarchy.
6. Secondary information visually recedes.
7. Large moments are exceptional rather than universal.

## Density Translation

Apple-like clarity should simplify each product card, not reduce the number of
products shown. The combined rule is:

> Westwing determines how often a user encounters a purchasable choice. Apple
> determines how clearly each choice is presented.

This produces dense browsing without marketplace noise.

---

# PART VIII - Recovery Roadmap

## Wave A - Product Discovery Recovery

**Impact:** Critical  
**Purpose:** Put products into the browsing path sooner and more often.

1. Move the first homepage product row to the hero boundary.
2. Present four desktop product cards per row.
3. Place product grids before secondary material or editorial sections on
   destination pages.
4. Ensure the first category, room, and collection viewport introduces product
   imagery or reaches it at the viewport boundary.
5. Remove long product-card descriptions.
6. Establish 4/3/2 desktop-tablet-mobile product grids.

**Completion targets**

- Four products introduced by the end of the first homepage scroll.
- Four products visible when any destination grid begins.
- Product cards no taller than approximately 650 px.

## Wave B - Commerce Density Recovery

**Impact:** High  
**Purpose:** Increase choices per viewport without increasing visual noise.

1. Compress category, room, and collection cards.
2. Reduce section-heading and inter-module vertical spacing.
3. Replace one-item full-width discovery cards with compact link treatments.
4. Keep product title, price, and availability within one compact information
   block.
5. Remove duplicated labels and oversized card actions.
6. Limit standard editorial modules to 18% of homepage area.

**Completion targets**

- Four to eight meaningful choices in each post-hero viewport.
- Homepage product impressions increase from 2 to at least 8.
- Total homepage height decreases despite increased product exposure.

## Wave C - Merchandising Recovery

**Impact:** High after Waves A and B  
**Purpose:** Turn density into guided commercial intent.

1. Establish distinct product rows for New Arrivals, room-led selection, and
   Founder Selection.
2. Use one featured product slot only when supported by a compact adjacent row.
3. Attach product rows to room and collection modules.
4. Introduce restrained merchandising labels such as New, Selected, Material
   Focus, and Available Now.
5. Use image-led editorial content as a bridge into products.
6. Review product ordering so the first row communicates range, price spread,
   material range, and visual contrast.

**Completion targets**

- At least three commercially distinct product stories on the homepage.
- No editorial block without a nearby product continuation.
- The first two product rows do not feel visually repetitive.

## Impact Order

| Rank | Action | Impact | Effort | Reason |
| ---: | --- | --- | --- | --- |
| 1 | Four-column product grids | Critical | Medium | Immediately doubles comparison density |
| 2 | Compact product-card body | Critical | Medium | Removes the primary cause of excessive height |
| 3 | Earlier homepage product row | Critical | Medium | Establishes commerce in the first journey |
| 4 | Products before secondary destination content | High | Medium | Makes category and room pages commercially legible |
| 5 | Compact room and collection cards | High | Small-Medium | Improves discovery choices per viewport |
| 6 | Reduce vertical section spacing | High | Small | Improves scroll reward across all pages |
| 7 | Repeat product rows after discovery modules | High | Medium | Maintains browsing momentum |
| 8 | Simplify headings and labels | Medium | Small | Improves scan speed |
| 9 | Merchandising labels and ordering | Medium | Medium | Adds commercial intent after density is solved |
| 10 | Featured product/editorial bridges | Medium | Medium | Adds controlled brand expression |

---

# PART IX - Experience Projection

The implemented Wave 1 recovery has a projected rendered baseline of 42/100.
WP-DES-05 does not re-score unrelated experience areas; it projects only the
gain available from commerce-density recovery.

| Stage | Projected Score | Commerce / Editorial Perception | Reason |
| --- | ---: | --- | --- |
| Current post-Wave 1 | 42/100 | 30 / 70 | Operationally cleaner, still severely under-dense |
| After Wave A | 52/100 | 48 / 52 | Products enter sooner and desktop comparison doubles |
| After Wave B | 61/100 | 62 / 38 | Cards and spacing deliver materially higher scroll reward |
| After Wave C | 68/100 | 70 / 30 | Product exposure becomes intentionally merchandised |

The score should not be projected beyond the high 60s from density changes
alone. Image quality, product variety, interaction quality, and complete
commerce journeys affect the remaining experience.

---

# Final Recommendation

Veylune should stop using physical scale as the primary signal of luxury.
Oversized product and discovery cards currently suppress the very commerce
experience they are intended to elevate.

The smallest high-impact recovery set is:

1. four product columns on desktop;
2. 520-650 px standard product cards;
3. no paragraph descriptions in browse cards;
4. first product row at the homepage hero boundary;
5. product grids before secondary destination content;
6. compact category, room, and collection cards;
7. four to eight new choices per post-hero viewport;
8. at least eight homepage product impressions;
9. editorial content limited to approximately 18% of homepage area;
10. repeated, clearly differentiated product merchandising rows.

This changes the experience from an editorial presentation containing products
into a luxury commerce platform directed by editorial judgment.

## Benchmark Sources

- Westwing homepage: https://www.westwing.de/
- Westwing living-room destination:
  https://www.westwing.de/wohnzimmer-moebel/menu/
- Westwing sofa listing: https://www.westwing.de/sofas/
