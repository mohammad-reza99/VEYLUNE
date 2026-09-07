# Veylune Production Readiness Closure

Status: Active
Captured: 2026-09-02
Environment: Shopware DDEV development mode

## Closed release surfaces

The release audit covers the homepage, Furniture department, Founder Selection, Living Room, Discover, Selection, account entry, Contact Studio, zero-result search, and the controlled 404 response.

| Contract | Runtime result |
| --- | --- |
| Document semantics | One `main`, one `h1`, document language, no missing image `alt`, no empty links |
| Desktop accessibility | No unnamed visible controls or duplicate IDs on the six primary audited surfaces |
| Overlay keyboard safety | Search, mega menu, and mobile navigation are inert while closed; open overlays trap focus and restore it on close. Mobile Escape returned focus to the Menu button in the runtime test |
| Form errors | Login fields receive `aria-invalid` and linked feedback; Contact Studio focuses a `role=alert` summary with direct field links |
| Mobile targets | Header axes, search suggestions, filters, sort Apply, result titles, recovery links, and breadcrumbs have at least a 44 px target dimension |
| Responsive reflow | Homepage, Discover, account, and Contact Studio have no horizontal overflow at 390 px or the 720 px effective 200 percent reflow checkpoint |
| Motion | A global reduced-motion fallback removes non-essential transition and animation duration |

## Controlled error states

Zero-result search remains inside the Living Index and offers broader recovery paths. The standalone 404 now uses the same sans-serif hierarchy, purple command color, white surface, and square controls as the public storefront. It remains lightweight and does not load the full storefront shell.

The 404 response enforces:

- HTTP 404;
- `noindex,nofollow` in HTML and `X-Robots-Tag`;
- `Cache-Control: no-store, private`;
- HSTS, frame denial, MIME sniffing denial, strict referrer policy, permissions policy, COOP, CORP, and a restrictive inline-style-only CSP;
- one safe return route to `/`.

## Performance interpretation

The automated gate warms each core DDEV route and applies a development TTFB guard. This is a regression signal, not a substitute for production Core Web Vitals. The existing active-asset and HTML budgets remain authoritative and run inside the release gate.

| Warmed DDEV route | Observed worst of two | Gate budget |
| --- | ---: | ---: |
| Homepage | 273 ms | 1,800 ms |
| Discover | 298 ms | 1,800 ms |
| Account | 265 ms | 1,800 ms |
| Contact Studio | 183 ms | 1,800 ms |
| Controlled 404 | 36 ms | 1,200 ms |

Production deployment still requires real-user LCP, INP, and CLS monitoring with production cache, CDN, compression, and debug settings.

## Repeatable gate

Run:

```bash
bash bin/veylune-production-readiness
```

The full governance command also runs this gate.
