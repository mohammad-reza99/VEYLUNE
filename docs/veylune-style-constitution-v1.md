# Veylune Style Constitution v1

**Direction:** Modern Continental / Architectural Warm Neutral / Governed Hybrid  
**Approved for:** reference design and implementation planning  
**Date:** 14 August 2026

## Brand promise

Veylune presents collectible interiors with architectural clarity, material warmth, and calm commercial confidence.

## Governing principles

1. Product is the hero; atmosphere supports it.
2. Cinematic scale belongs to authored scenes, not every section.
3. Browse surfaces favor comparison; detail surfaces favor contemplation.
4. Whitespace frames evidence and never replaces it.
5. Founder authority is demonstrated through selection and commentary.
6. Every public route and action must work before visual approval.
7. Empty commercial modules do not render.
8. Essential commerce information is direct, visible, and unpoetic.

## Visual formula

- 70% architectural neutral
- 20% warm residential materiality
- 10% dark collectible-gallery drama

## Color roles

| Role | Token | Value | Use |
| --- | --- | --- | --- |
| Canvas | Ivory | `#F8F5EF` | Primary page background |
| Raised canvas | Parchment | `#F1EADF` | Editorial and material panels |
| Structural line | Limestone | `#D8D0C3` | Dividers and quiet boundaries |
| Essential ink | Charcoal | `#211F1B` | All essential text and controls |
| Deep scene | Obsidian | `#151412` | Collectible-gallery moments |
| Accent | Bronze | `#8B765A` | Short labels, selected details, decorative rules |
| Interactive accent | Dark bronze | `#6F604B` | Focused accents that pass contrast validation |

Bronze must not carry essential meaning alone. Text/background pairs require WCAG 2.2 AA validation.

## Typography

### Display

Use a modern continental serif with high-quality italics, distinctive lowercase forms, readable numerals, and broad European language coverage. Final font licensing remains a Founder approval item.

### Utility

Use a neutral sans for navigation, product metadata, price, forms, cart, checkout, and system feedback.

### Rules

- One display statement per viewport.
- Uppercase tracking is restricted to short eyebrow labels.
- Operational text minimum: 14 px.
- Body measure: 45-70 characters.
- Price and availability outrank descriptive prose on decision surfaces.

## Photography

Four canonical image families:

1. product cutout;
2. material macro;
3. contextual architectural room;
4. human-scale detail.

All adjacent imagery must share crop logic, color temperature, shadow direction, and background family. Room scenes must prove scale and product relationships. Founder Selection requires a short visible selection rationale.

## Layout

- Desktop maximum content width: 1360 px.
- Browse grid: 4 columns desktop, 3 tablet, 2 mobile where titles and prices remain legible.
- Standard grid gap: 20-28 px desktop; 10-16 px mobile.
- One cinematic scene may precede a product grid; product proof follows immediately.
- Editorial interruptions may appear only between complete product groups.
- Empty states are private or operational unless they help a user recover.

## Components

### Header

Compact, sticky, and commerce-capable. Contains logo, primary discovery, search, account, and bag. No decorative second navigation row without measurable discovery value.

### Product card

Required order: image, name, concise type/material, price or explicit inquiry state, restrained secondary action. No paragraph description.

### Product detail

Required hierarchy: gallery, name, price, availability, primary action, dimensions/materials, delivery, story, related pieces.

### Checkout

Utility typography dominates. Editorial styling is limited to tone, color, spacing, and material detail. Progress, errors, totals, delivery, and payment remain explicit.

## Commerce posture

| Product class | Primary action | Secondary action |
| --- | --- | --- |
| Standard stocked | Add to bag | Product question |
| Configurable premium | Configure / select options | Private consultation |
| Collectible or high-ticket | Request availability | Private consultation |

The action model is determined by product data, never by visual styling alone.

## Motion

- Motion clarifies entry, state, and hierarchy.
- No looping decorative motion.
- Standard transition: 180-360 ms.
- Cinematic transition ceiling: 700 ms.
- `prefers-reduced-motion` removes nonessential transforms and fades.

## Accessibility and quality gates

- WCAG 2.2 AA contrast for essential content.
- Visible focus and logical keyboard order.
- 44 x 44 px mobile targets.
- 200% text zoom without loss of content or action.
- Responsive images with explicit dimensions.
- No primary route returns 404.
- No empty public commerce module renders.
- Desktop, tablet, and mobile reference screenshots pass visual regression.

## Canonical reference screens

The visual reference set covers:

1. Homepage: authored architectural scene followed by product proof.
2. PLP: compact discovery, filters, and four-column product comparison.
3. PDP: gallery-led material storytelling with governed purchase posture.
4. Checkout: quiet, explicit, low-friction transaction design.

These references govern visual implementation. Existing components that conflict with them require an explicit exception or migration plan.

