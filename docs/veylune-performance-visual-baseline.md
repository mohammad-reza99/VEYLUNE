# Veylune Performance and Visual Regression Baseline

Status: Active
Captured: 2026-09-02
Environment: Shopware DDEV development mode

## Reference geometry

The live Wayfair desktop storefront and Veylune were measured in the same browser session at the same effective desktop viewport.

| Surface | Wayfair live | Veylune baseline |
| --- | ---: | ---: |
| Search field | 564 x 48 px | 636 x 46 px |
| Primary discovery row | 34 px | 42 px |
| Department row | 36 px | 37 px |
| Logo | 150 x 45 px | 130 x 38 px |
| Homepage merchandising row | 56 px reference | 52 px |

Veylune keeps its own Living Index labels, restrained imagery, typography, and purple palette. The structural parity target is the same three-level commerce rhythm: search and identity, primary discovery, then direct department access. The homepage merchandising rail is not sticky and sits immediately before the hero.

## Active asset baseline

| Asset | Current | Enforced budget |
| --- | ---: | ---: |
| Compiled CSS raw | 1,242,788 bytes | 1,300,000 bytes |
| Compiled CSS gzip | 170,972 bytes | 180,000 bytes |
| Shopware storefront JS raw | 167,379 bytes | 190,000 bytes |
| Shopware storefront JS gzip | 52,046 bytes | 60,000 bytes |
| Veylune JS raw | 83,493 bytes | 100,000 bytes |
| Veylune JS gzip | 21,235 bytes | 25,000 bytes |
| Homepage hero WebP | 203,946 bytes | 230,000 bytes |

The hero is preloaded, has intrinsic dimensions, uses eager loading, and now carries `fetchpriority="high"`. Below-fold room and product media remain lazy-loaded with intrinsic dimensions.

## Route payload baseline

These are raw HTML payload and DDEV development TTFB observations, not production Core Web Vitals.

| Route family | HTML bytes | Observed DDEV TTFB |
| --- | ---: | ---: |
| Homepage | 68,243 | 0.781 s |
| Department | 63,492 | 0.503 s |
| Collection | 67,749 | 0.533 s |
| Room | 65,229 | 0.494 s |
| Discover | 92,250 | 0.600 s |
| Selection | 57,458 | 0.436 s |
| Account login and registration | 255,923 | 0.700 s |
| Contact Studio | 83,464 | 0.511 s |

The account route is intentionally assigned a separate budget because Shopware renders login and registration in one response. Production Web Vitals must be captured after deployment with cache, compression, and debug tooling configured like production.

## Multi-page visual matrix

The following surfaces were inspected in the in-app browser after a fresh storefront build. Desktop checks use 1440 x 1000 px and mobile checks use a real 390 x 844 px viewport.

| Surface | Desktop structure | Mobile structure | Horizontal overflow | Broken images | Empty links |
| --- | --- | --- | ---: | ---: | ---: |
| Homepage | Three-level header, merchandising rail, hero | Compact header, rails hidden, hero CTA stack | 0 | 0 | 0 |
| Furniture department | Living Index department shell | Single-column discovery flow | 0 | 0 | 0 |
| Discover results | Sidebar, sort, result grid | In-viewport filter details, sort, two-card grid | 0 | 0 | 0 |
| Account entry | 0.82 / 1.48 login-register grid | 375 px single-column form stack | 0 | 0 | 0 |
| Contact Studio | Project brief composition | Single-column brief and controls | 0 | 0 | 0 |

The mobile navigation drawer was also opened at 390 px. Its measured panel is 359 x 844 px, every navigation target has a non-empty route, and the panel remains inside the viewport.

## Render-work optimization

The homepage hero remains immediately renderable. Object studies, material story, access status, project close, and Discover cards after the first six use `content-visibility: auto` with a stable intrinsic-size fallback. This reduces below-fold paint work while preserving document height, native semantics, and lazy-loaded media behavior.

## Build residue

`public/theme` currently contains historical compiled hash directories. They are not loaded by the active storefront and are therefore excluded from runtime budgets. Cleanup is a separate destructive housekeeping operation and must only run with an explicit, recoverable deployment procedure.

## Repeatable verification

Run:

```bash
bash bin/veylune-performance-baseline
```

The command resolves the active compiled asset hashes from the rendered homepage, enforces route and asset budgets, verifies the visual baseline markers, and rejects empty homepage links. It is also part of the full Veylune governance suite.
