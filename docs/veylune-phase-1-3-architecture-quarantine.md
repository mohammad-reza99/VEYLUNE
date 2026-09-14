# Veylune Phase 1.3 - Legacy Artifact Quarantine and Architecture Enforcement

Captured: 2026-09-14

## Outcome

Legacy recovery files and dormant sources no longer live beside active storefront code. They remain recoverable in a tracked archive. A deterministic architecture guard now blocks their return, detects unowned active partials, holds specificity debt at or below the current ceiling, and verifies the public category route contract.

## Quarantine result

| Artifact class | Count | Active after quarantine | Archive destination |
| --- | ---: | ---: | --- |
| `.orig` files | 41 | 0 | `archive/veylune-phase-1-legacy/orig` |
| Dormant JavaScript controllers | 1 | 0 | `archive/veylune-phase-1-legacy/dormant` |
| Dormant SCSS partials | 1 | 0 | `archive/veylune-phase-1-legacy/dormant` |
| Temporary audit scripts | 1 | 0 | `archive/veylune-phase-1-legacy/temp` |

The dormant JavaScript file was `veylune-plp.js`. It was not imported by `main.js`; `veylune-plp-v2.js` remains the sole active PLP controller.

The dormant SCSS file was `_marketplace-header-home-index.scss`. It contained 352 lines but was not imported by `base.scss`. Removing it from the active source tree changes no compiled CSS.

## Active architecture after quarantine

- Active SCSS files: 137.
- Active SCSS logical lines: 30,711.
- Base imports: 135, all owned exactly once.
- Unimported active SCSS partials: 0.
- Active JavaScript files: 20, all owned exactly once.
- Active `.orig` files: 0.
- Total active-SCSS `!important` ceiling: 130.
- Debt-named active SCSS files: 39.

`overrides.scss` is an intentional Shopware entrypoint and is explicitly treated as an entrypoint rather than an underscore partial.

## Route ownership resolution

The public surface contract already classified ten `/categories/*` routes as current, but `StorefrontRouteOwnershipPolicy` did not map `frontend.veylune.discovery.category`. The same policy also declared the category surface activation-pending. This allowed the live routes through an ownership gap.

The resolved contract is:

- `frontend.veylune.discovery.category` maps to `SURFACE_CATEGORIES`.
- `SURFACE_CATEGORIES` is `governed_public`.
- Category ownership remains `category_publication_policy`.
- Existing publication and taxonomy prerequisites remain recorded.
- The route is no longer accidentally public through a `null` ownership result.

## Enforcement

`bin/veylune-architecture-guard-audit` fails when:

- an `.orig` file appears under active `bin/` or Veylune theme source;
- a quarantine snapshot is missing;
- an active SCSS partial is not imported;
- the `!important` count rises above 130;
- the debt-named SCSS file count rises above 39;
- dormant `veylune-plp.js` returns to the active JavaScript directory or main entrypoint;
- the category route loses its governed-public owner.

The guard runs automatically inside `bin/veylune-governance-check`.

## Exit gate

- Recoverable quarantine exists: PASS.
- Active `.orig` and temporary residue are removed: PASS.
- Dormant JavaScript and SCSS are outside active compilation: PASS.
- Component ownership audit passes: PASS.
- Architecture guard passes: PASS.
- Public category route ownership conflict is resolved: PASS.
- Storefront build and full governance gate must pass before checkpoint.

## Exact next step

Phase 1.4 - Phase 1 Exit Gate and Architecture Freeze.

Phase 1.4 will verify every Phase 1 contract against one commit, close any remaining unknown diff or owner gap, freeze the architecture boundary, and prepare the exact Phase 2.1 design-token and global-shell consolidation backlog.
