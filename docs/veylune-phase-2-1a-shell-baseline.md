# Veylune Phase 2.1A Global Shell Baseline

Captured: 2026-09-14

Status: complete

Exact next package: 2.1B Token Freeze

## Scope and reference boundary

Phase 2.1A freezes evidence before the global shell is consolidated. The live Wayfair homepage was used only as a hierarchy, density, discovery, and commerce reference. Veylune retains its purple-neutral identity, typography, assets, and truthful service language.

The reference shell currently separates utility/service links, a primary menu-account-cart-search row, category navigation with named dropdowns, and a promotional strip. Veylune already has the same major responsibilities, but its height, responsive compression, and mobile discovery need deliberate token-led refinement.

## Reproducible evidence

- Five public routes: home, furniture category, discover search, account login, and empty cart.
- Three viewports: desktop 1440x1000, tablet 834x1112, and mobile 390x844.
- Fifteen HTTP 200 captures and fifteen screenshot artifacts.
- Zero console errors, page errors, failed non-image responses, or document-level horizontal overflows.
- No private-preview route or marker in any capture.
- Computed geometry, type, color, border, spacing, and action-link evidence is stored in `shell-baseline.json`.

## Frozen shell measurements

| Viewport | Header | Utility row | Primary bar | Search width | Department rail |
| --- | ---: | ---: | ---: | ---: | ---: |
| Desktop | 190px | 31px | 121px | 921.38px | 37px |
| Tablet | 122px | hidden | 121px | 424.92px | hidden |
| Mobile | 166.36px | hidden | 165.36px | 358px | hidden |

These values describe the current baseline, not the final design target. The audit prevents silent drift until the later owner package intentionally updates the contract.

## Interaction evidence

- Desktop mega menu opens on pointer intent, closes on pointer leave and Escape, and restores trigger focus.
- Desktop and tablet search suggestions expose expanded state and close on Escape.
- Tablet keeps the inline navigation visible and the mobile toggle hidden at 834px.
- Mobile drawer opens with an explicit accessibility state, locks the document, closes on Escape, and restores focus.

The first capture reported false focus failures because the harness clicked a navigational link and synthetically clicked an unfocused mobile toggle. The corrected harness now models keyboard focus before activation and reports zero interaction failures.

## Registered design findings

1. Desktop density: the complete shell consumes 190px before page content. Package 2.1C/2.1D owns the refinement.
2. Mobile density: the three-row mobile shell consumes 166.36px. Package 2.1F owns compression and sticky behavior.
3. Mobile axis discovery: the 390px capture begins the visible axis row at ROOM. Package 2.1F must verify first-item visibility, overflow affordance, and horizontal discovery.

These are visible design deltas, not hidden test failures. Each has an owner and target package.

## Exit gate

Phase 2.1A is complete only while `php bin/veylune-shell-baseline-audit` passes. The gate validates the route-by-viewport matrix, screenshots, computed shell presence and height, public-state containment, runtime errors, overflow, and breakpoint-specific interactions.

The next exact implementation package is 2.1B Token Freeze: declare and govern color, typography, spacing, radius, elevation, border, icon, motion, grid, and control tokens before changing the shell.
