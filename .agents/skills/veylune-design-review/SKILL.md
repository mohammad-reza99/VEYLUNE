---
name: veylune-design-review
description: Review and verify Veylune storefront design changes, including Twig, SCSS, JavaScript interactions, responsive layouts, and reusable commerce components. Use whenever a task changes visual hierarchy, typography, spacing, color, product presentation, navigation, overlays, forms, cart or checkout UI, focus behavior, or mobile/tablet behavior.
---

# Veylune Design Review

Review the implemented storefront, not a detached mockup. Treat existing Veylune tokens and established components as the default design system.

## Review sequence

1. Identify the customer journey and every template, style, and script changed by the implementation.
2. Reuse tokens from `custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/scss/abstracts/_tokens.scss`; call out any new literal that should be a token.
3. Check hierarchy, spacing rhythm, typography, color, media treatment, and component consistency at desktop, tablet, and mobile widths.
4. Exercise default, hover, focus-visible, active, disabled, loading, empty, error, and success states that exist for the component.
5. Verify keyboard order, accessible names, dialog or menu focus handling, Escape/close behavior, contrast, and reduced motion.
6. Confirm interactive markup uses native elements and Shopware contracts before adding custom JavaScript.
7. Compile the theme and run the most relevant Playwright and axe checks. Inspect screenshots for important reusable or revenue-critical surfaces.

## Acceptance rules

- Preserve the premium editorial identity: restrained color, intentional scale, strong product imagery, and clear commerce actions.
- Do not copy another brand's proprietary page or component verbatim.
- Do not redesign working surfaces without a customer or maintenance benefit.
- Avoid generic gradients, excessive cards, decorative pills, noisy section boundaries, and animation without interaction value.
- Mobile content must remain inside the viewport; menus, filters, sort controls, drawers, and dialogs must be dismissible without pointer precision.
- New styles must not rely on escalating specificity or unexplained `!important` rules.
- Report what was verified, what remains manual, and any missing content or business evidence separately.
