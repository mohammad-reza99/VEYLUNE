# VEYLUNE STUDIO - Style Council Audit

**Audit date:** 14 August 2026  
**Scope:** rendered local storefront, current theme system, and prior Veylune design specifications  
**Decision owner:** Mohammadreza, Founder  
**Status:** analysis only; no visual implementation authorized in this work package

## Executive verdict

Veylune has a recognizable visual seed, but not yet a complete premium commerce style. The warm ivory, charcoal, bronze, serif-led editorial voice, fine rules, and restrained motion vocabulary are coherent. The rendered storefront, however, currently communicates an elegant prototype more strongly than an authoritative design house.

The council's shared conclusion is:

> Keep the quiet editorial identity, but rebuild the visible experience around product proof, photographic authorship, usable hierarchy, and route integrity. Do not solve the current weakness by adding decoration.

### Current score

| Dimension | Score | Verdict |
| --- | ---: | --- |
| Brand distinctiveness | 68/100 | Recognizable, but too dependent on generic editorial-luxury conventions |
| Art direction | 24/100 | Almost no live product or campaign imagery is rendered |
| Typography | 61/100 | Elegant hierarchy, weak operational typography and generic fallback faces |
| Color/materiality | 73/100 | Coherent and appropriate; needs stronger tonal discipline and image interaction |
| Layout/rhythm | 39/100 | Clean grid, but dead space and empty modules read as missing content |
| Commerce clarity | 18/100 | Primary discovery routes return 404 and product proof is absent |
| Responsive readiness | 46/100 | Breakpoint rules exist, but the live experience lacks enough content proof for acceptance |
| Accessibility confidence | 55/100 | Skip links and landmarks are strong; small labels and muted bronze need formal contrast/touch testing |
| Performance confidence | 64/100 | Minimal live imagery reduces load, but this is not a valid premium-media performance proof |
| Overall rendered premium readiness | **42/100** | Strong direction, incomplete and commercially non-credible execution |

## Problem

The current style system confuses restraint with absence. On the homepage at 1280 x 720:

- the page renders only one brand image and no product imagery;
- the hero is 268 px high and contains type plus a flat atmospheric background, not a designed product/world image;
- the New Arrivals region occupies 486 px while exposing no products;
- the main content is 1,409 px high, but much of its area contains no useful visual or commercial information;
- Shop, Rooms, Collections, and Consultation navigation destinations resolve to public 404 states;
- Journal renders, but contains no editions and repeats consultation messaging;
- the header is visually calm, but it is static rather than the sticky behavior declared in parts of the theme system;
- the live storefront relies on Georgia and Times New Roman as its display identity.

This is not primarily a palette problem. It is a product-presence, route-integrity, art-direction, and hierarchy problem.

## Evidence

### Rendered evidence

| Evidence | Observation | Consequence |
| --- | --- | --- |
| Homepage image inventory | Logo only; natural size 1264 x 333, rendered about 166 x 44 | No product, room, material, or campaign authority |
| Hero headline | 64 px Georgia, about 60 px line height | Strong editorial moment, but unsupported by visual proof |
| New Arrivals | Heading and View all action with no rendered cards | The label creates an unfulfilled promise |
| Founder Selection | Heading and View all action with no rendered cards | Founder curation has no visible evidence |
| Shop by Room | Two text-only links | Functional taxonomy, not inspirational room discovery |
| Consultation block | 606 px region | Service message receives disproportionate space versus products |
| Primary destinations | Furniture, Living Room, Founder Selection, and Consultation return 404 | Brand polish is invalidated by broken journeys |
| Journal | Text-only shell with repeated consultation CTA | Editorial authority is asserted but not demonstrated |

### System evidence

The theme tokens are internally coherent: ivory `#f8f5ef`, limestone `#ebe5da`, bronze `#8b765a`, charcoal `#211f1b`, restrained radii, long easing curves, and editorial tracking. This is a credible foundation.

The system also contains two competing levels of ambition:

1. a rich cinematic homepage language with large media frames, grain, gradients, and extensive vertical spacing;
2. a compact live commerce shell with no media and empty discovery modules.

The visual system therefore over-specifies atmosphere while under-proving merchandise.

## Risk

| Priority | Risk | Business effect |
| --- | --- | --- |
| P0 | Broken primary navigation | Immediate loss of trust and inability to shop |
| P0 | Empty product modules | Premium claims appear fictional or unfinished |
| P0 | No photographic art direction in the live experience | Veylune cannot demonstrate taste, sourcing quality, or product value |
| P1 | Excessive dead space | Restraint is interpreted as failed rendering |
| P1 | Generic system serif as brand voice | Identity remains imitable and insufficiently ownable |
| P1 | Founder Selection without products | Founder authority is weakened rather than strengthened |
| P1 | Editorial-to-commerce imbalance | Visitors understand mood but not what to explore or buy |
| P2 | Very small uppercase utility typography | Readability and accessibility risk |
| P2 | Muted bronze and fine rules | Possible contrast loss on lower-quality displays |
| P2 | Consultation repeated across sparse surfaces | Brand can feel defensive or inaccessible |

## Options

### Option A - Preserve the current minimal shell

Keep the palette, typography, whitespace, and text-first surfaces; populate them later.

- Advantage: lowest design-system disruption.
- Cost: the current shell remains visually under-authored and commerce credibility arrives too late.
- Council view: rejected.

### Option B - Convert Veylune into conventional dense luxury commerce

Increase grids, cards, badges, navigation, filters, and promotions aggressively.

- Advantage: immediate commercial legibility.
- Cost: destroys the quiet, gallery-led point of view and makes Veylune interchangeable.
- Council view: rejected.

### Option C - Quiet Commerce Gallery

Retain the ivory/charcoal/bronze base and editorial restraint, but make authored product imagery the dominant medium. Use compact commerce hierarchy, fewer but stronger statements, working routes, controlled density, and a distinctive type system.

- Advantage: preserves brand character while making it believable and usable.
- Cost: requires coordinated content, merchandising, design-system, route, and performance work.
- Council view: recommended unanimously after scope conflict resolution.

## Council positions

Only seats relevant to this decision were activated; the remaining council functions are represented in cross-checks where their risk domain applies.

### Gary Friedman - luxury brand and retail authority

The visual language needs scale, confidence, and product theatre. The current palette is credible, but sparse empty modules cannot carry luxury. Recommendation: fewer sections, larger authored scenes, stronger product families, and no public surface without visual proof.

### Delia Lachance - interiors commerce and inspiration

Room discovery must be shoppable and emotionally specific. Living and Dining cannot be text rows only. Recommendation: room stories with coordinated products, scale cues, material relationships, and direct paths to the objects shown.

### Lisa Chi - growth and commercial product strategy

Every premium moment must still clarify the next action. Recommendation: product or category proof inside the first viewport, clear price/availability states, and measurement of discovery-to-PDP movement without promotional clutter.

### Teresa Torres - continuous discovery

Do not decide the full style from internal taste alone. Recommendation: validate three representative prototypes with target users: homepage first screen, four-column discovery grid, and PDP hero/information hierarchy. Test comprehension, trust, desirability, and next-click intent.

### Baymard Institute - ecommerce usability benchmark

The critical failure is not aesthetic: core navigation routes fail, product modules are empty, and expected commerce information is absent. Route integrity, product-list density, filters, price/availability clarity, and mobile tap behavior must become acceptance gates.

### Kristina Halvorson - content strategy

The copy has a consistent restrained tone, but abstract language is overused and repeated. Recommendation: every statement must perform one job: orientation, differentiation, evidence, or action. Remove repeated consultation framing and avoid internal-state language.

### Brad Frost - design systems

The token layer is a good start, but the rendered result suggests component variants and route states are not governed by one acceptance system. Recommendation: formalize primitives for type, spacing, media ratios, product cards, editorial modules, empty states, and action hierarchy; eliminate one-off cinematic spacing.

### Leonie Watson / W3C WAI - accessibility

Landmarks and skip links are positive. Risks remain in small uppercase text, wide letter spacing, fine rules, muted colors, icon-only controls, and mobile targets. Recommendation: WCAG 2.2 AA contrast, visible focus, 44 x 44 px target checks, 200% text zoom, reduced motion, and keyboard route testing.

### Architecture and Shopware seat

Style acceptance cannot be separated from route and content-state correctness. Recommendation: design empty, loading, unavailable, and populated states explicitly; no production navigation item may target a 404 or unapproved shell.

### Steve Souders - performance

The current low image count is not a performance success because it also removes the product experience. Recommendation: use responsive AVIF/WebP, explicit dimensions, priority only for the true LCP image, lazy loading below the fold, and a measured image budget.

### Kent Beck - quality and verification

Turn the style direction into executable checks. Recommendation: screenshot baselines at desktop/tablet/mobile, route smoke tests, empty-module assertions, accessibility checks, and visual-regression tolerances for the header, product grid, PDP, cart, and checkout.

## Conflict

The principal conflict was between gallery restraint and commerce density.

- Luxury/art-direction seats wanted fewer, larger, more cinematic moments.
- Commerce/usability seats wanted earlier product proof, denser comparison, and clearer actions.
- Performance/accessibility seats opposed unbounded imagery, tiny labels, and decorative motion.

The resolution is not a 50/50 compromise. Veylune should use **cinematic scale for authored scenes** and **compact density for browse and decision surfaces**. One visual mode must not be stretched across every component.

## Recommendation

Adopt **Quiet Commerce Gallery** as the governing style direction.

### Style principles

1. Product is the hero; atmosphere supports it.
2. Every large area earns its space through imagery, product choice, or useful information.
3. Editorial statements are rare and specific.
4. Browse surfaces are compact enough for comparison.
5. Detail surfaces slow down and become material-rich.
6. Bronze is an accent, not a substitute for contrast.
7. Motion communicates transition and hierarchy, never luxury by itself.
8. Empty public modules do not render.
9. No visual approval is valid while the linked journey is broken.
10. Founder curation is proven through selection, sequencing, and commentary, not the label alone.

### Proposed visual ratio

| Layer | Share | Role |
| --- | ---: | --- |
| Product and material imagery | 50% | Desire, proof, authorship |
| Commerce and discovery UI | 25% | Comparison, navigation, decisions |
| Editorial brand content | 15% | Point of view and differentiation |
| Service/trust content | 10% | Consultation, delivery, care, provenance |

### Typography direction

- Replace the generic Georgia fallback as the final brand display face with a licensed or self-hosted editorial serif selected for multilingual coverage, readable numerals, and strong italic forms.
- Keep a neutral, highly legible sans for utilities, prices, forms, cart, and checkout.
- Limit uppercase tracked labels to short eyebrow text.
- Use a modular responsive scale with explicit minimums; operational text should not fall below 14 px.
- Give price, availability, dimensions, and delivery information stronger hierarchy than poetic description on decision surfaces.

### Color direction

- Preserve ivory, charcoal, limestone, and restrained bronze.
- Reduce the number of translucent ivory variants; use clear surface levels.
- Introduce one darker material tone for image captions, PDP information, and high-confidence actions.
- Validate all text/background pairs to WCAG 2.2 AA.
- Avoid using low-contrast bronze for essential actions or metadata.

### Photography direction

- Establish four repeatable image types: clean product cutout, material macro, contextual room scene, and human-scale detail.
- Require consistent shadow direction, color temperature, crop logic, and background family.
- Use room imagery to prove spatial judgment, not as generic lifestyle decoration.
- Every Founder Selection item needs a visible reason for selection.
- Avoid stock-looking mixed authorship across adjacent cards.

### Layout direction

- Desktop browse: four product columns; tablet: three; mobile: two where legibility permits.
- Use one cinematic scene near the top, followed immediately by visible product proof.
- Remove modules that have no products or approved editorial asset.
- Reduce long vertical gaps; whitespace should frame content, not replace it.
- Make room and collection navigation image-led but compact.
- Preserve a quiet footer, but shorten it until the site has enough service content to justify multiple columns.

## Implementation impact

### Wave 0 - credibility gate

- Repair all primary routes and remove navigation to non-public shells.
- Suppress empty New Arrivals, Founder Selection, room, and journal modules.
- Define populated, empty, unavailable, and loading component states.

### Wave 1 - visual foundation

- Finalize display and utility type families.
- Normalize color surfaces, contrast pairs, spacing, and section-density tokens.
- Create canonical product-card, room-card, collection-card, CTA, and information-row components.
- Replace generic hero treatment with one art-directed product/room scene.

### Wave 2 - commerce proof

- Render minimum viable product density across homepage and categories.
- Add product photography standards and asset QA.
- Establish PDP hierarchy for gallery, name, price, availability, dimensions, materials, delivery, and inquiry/buy action.

### Wave 3 - validation and polish

- Run desktop/tablet/mobile visual regression.
- Complete keyboard, focus, zoom, reduced-motion, and contrast checks.
- Measure LCP, CLS, image weight, and route stability.
- Run user validation against comprehension, trust, desirability, and task completion.

## Acceptance criteria

The style direction is not complete until all criteria below pass:

- zero primary navigation links return 404;
- no empty public product, room, collection, or editorial module is rendered;
- homepage first viewport contains an authored visual plus a clear discovery action;
- at least four products are visible when the first desktop product grid enters view;
- every product card shows image, name, concise type/material, price or explicit inquiry state, and one clear action;
- Founder Selection contains real products and a visible curatorial rationale;
- typography uses approved hosted fonts with fallbacks and stable metrics;
- all essential text meets WCAG 2.2 AA contrast;
- keyboard navigation, visible focus, 200% zoom, reduced motion, and 44 x 44 px touch targets pass;
- desktop, tablet, and mobile screenshot baselines are approved;
- hero/LCP media is responsive, dimensioned, and within the agreed performance budget;
- cart, checkout, account, and error states use the same design system as editorial surfaces;
- founder approves the final homepage, PLP, PDP, and checkout reference screens as one coherent family.

## Founder decision

**Recommended decision:** approve Option C, Quiet Commerce Gallery, as the direction for detailed design and implementation.

Founder approval is still required for three taste-level choices before implementation:

1. the final serif personality: classical editorial, modern continental, or sharper fashion-led;
2. the image mood: warm residential, architectural neutral, or darker collectible-gallery;
3. the commerce posture: direct purchase first, consultation first, or governed hybrid by product class.

Until those choices are approved, the team may repair P0 route and empty-state failures, but should not lock the final typography, campaign art direction, or PDP action hierarchy.

## Next step

After founder direction approval, create the **Veylune Style Constitution and four reference screens**: homepage, product listing, product detail, and checkout. Those references become the implementation and visual-regression source of truth.
