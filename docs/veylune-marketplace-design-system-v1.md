# Veylune Marketplace Design System v1

## Objective

This layer establishes an original Veylune marketplace UI foundation informed by proven, high-density home-commerce conventions. It is not a pixel clone and does not use Wayfair source code, proprietary assets, copy, trademarks, or unlicensed fonts.

## Token contract

- Primary: `#6f2da8`; hover: `#562181`; soft surface: `#f1e9f7`
- Ink: `#2f2b32`; muted: `#6f6874`; border: `#d9d5dc`
- Page: `#ffffff`; alternate surface: `#f7f5f8`
- Promotion: `#c8244f`; success: `#18794e`; rating: `#9a5b00`
- Typography: licensed/system sans stack led by Inter
- Radius: 6, 8, and 12 pixels; pill only for circular and chip controls
- Controls: 44-pixel button and 48-pixel form-control minimums
- Focus: visible purple ring with offset

## Component contract

- Buttons: primary, secondary, tertiary, disabled
- Inputs: bordered white controls with explicit hover and focus states
- Icons: `currentColor`, 20-pixel glyph inside a 44-pixel target
- Commerce cards: 4:5 media, compact metadata, strong price hierarchy, promotion badge, hover elevation
- Surfaces: white panels, light neutral borders, restrained shadow

Reusable Veylune classes are prefixed with `veylune-marketplace-`. Shopware core classes receive matching visual behavior so current pages adopt the system without template duplication.

## Accessibility guardrails

- Keyboard focus remains visible.
- Interactive targets are at least 44 pixels where the component controls dimensions.
- Motion is removed when the user requests reduced motion.
- Color is not intended to be the only carrier of state; template-level labels remain required.

## Deferred work

The marketplace header, mega navigation, search interaction, merchandising modules, filters, PDP purchase stack, checkout, account, supplier operations, and personalization belong to later implementation phases. This document only governs the shared visual foundation.
