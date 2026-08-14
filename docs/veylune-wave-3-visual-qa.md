# VEYLUNE STUDIO — Wave 3 Visual QA

**Status:** Complete  
**Direction:** Modern Continental / Architectural Warm Neutral / Governed Hybrid

## Council synthesis

The 22-role council review converged on four priorities: preserve the governed catalog boundary, make the experience resilient at mobile and zoomed widths, keep interaction states visible without visual noise, and bring controlled error states into the same premium language as the storefront.

## Verified surfaces

- Public homepage at 390, 640, 768, 1280, and 1440 CSS-pixel widths.
- Private governed catalog preview at mobile and desktop widths.
- Four-column desktop preview grid and single-column mobile preview grid.
- Private consultation page.
- Controlled public 404 for unavailable governed destinations.
- Keyboard focus, touch-target sizing, horizontal overflow, and reduced-motion rules.

## Implemented polish

- Added 44px interaction targets for primary mobile actions, header controls, mobile navigation, and footer links.
- Added explicit focus treatment to preview navigation and horizontal product rails.
- Added horizontal rail containment and reduced-motion behavior.
- Added narrow-width heading resilience for homepage and preview mastheads.
- Replaced the raw controlled 404 response with a responsive, branded, no-index error experience and a safe return-to-studio action.

## Regression evidence

- Homepage: no content overflow at the tested widths.
- 200% zoom proxy: the 640px layout remains contained without horizontal overflow.
- Governed furniture preview: 19 inactive draft records; one column at 390px and four columns at 1280px.
- Controlled error route: 390px content width, 44px return action, and no horizontal overflow.
- Theme compilation and PHP syntax validation pass.

## Boundary note

Draft products remain inactive, non-sellable, non-indexable, and visible only through the token-gated development preview. Calma and Aurelia remain quarantined.
