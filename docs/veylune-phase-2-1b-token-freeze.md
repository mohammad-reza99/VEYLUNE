# Veylune Phase 2.1B Token Freeze

Captured: 2026-09-14

Status: complete

Exact next package: 2.1C Topbar and Announcement Hierarchy

## Outcome

Veylune now has one canonical global token source: `abstracts/_tokens.scss`. The former marketplace `:root` block was removed from `component/_marketplace-design-system.scss`; compatibility names now resolve to canonical tokens without changing their computed values.

The contract covers ten required categories: color, typography, spacing, radius, elevation, border, iconography, motion, grid, and shared controls. It preserves the purple-neutral identity and current shell appearance while giving later consolidation packages stable primitives.

After the production storefront build, the five-route and three-viewport shell suite was captured again. All 15 candidate screenshots were byte-for-byte identical to the Phase 2.1A PNG references, with zero SHA-256 mismatches and zero interaction issues.

## Reference decision

The live Wayfair shell remains the interaction-hierarchy and information-density reference: utility/service links, dominant search, menu/account/cart actions, category dropdown navigation, and a separate promotional layer. Veylune does not copy Wayfair assets or claims. Its governed brand command color remains `#7b189f`, hover color `#5c1277`, neutral text `#211e22`, and focus color `#1364f1`.

## Ownership rules

1. Global design primitives are declared only in `abstracts/_tokens.scss`.
2. Component-local variables are allowed when they describe local geometry or state and alias governed global tokens where a global semantic value exists.
3. New shared color, typography, spacing, radius, elevation, border, icon, motion, grid, or control values require a contract update before use.
4. Marketplace compatibility variables remain aliases during migration; they are not a second source of truth.
5. Any intentional visual token change requires a new computed-style and screenshot baseline.

## Debt ceilings

The Phase 2.1B audit prevents growth beyond the pre-freeze active-SCSS baseline:

| Metric | Ceiling |
| --- | ---: |
| Color literals outside the canonical token file | 1829 |
| Literal font-family declarations | 235 |
| Literal shadow declarations | 98 |
| Literal transition-time declarations | 81 |
| `!important` declarations | 130 |

These ceilings do not classify the existing debt as finished. Package 2.1G will reduce it during owner-based cascade migration. From this checkpoint forward, unexplained growth fails governance.

## Exit gate

`php bin/veylune-token-contract-audit` verifies all ten categories, unique canonical definitions, Veylune identity values, compatibility aliases, import order, absence of a second marketplace token owner, and literal-debt ceilings.

The next exact implementation package is 2.1C: consolidate the topbar and announcement hierarchy onto the frozen tokens, preserve truthful service copy, create the intended three-part type rhythm, and verify responsive containment.
