# WP-03 Publication State Enforcement

## Scope

WP-03 adds an independent publication lifecycle gate for governed identity
records. It does not modify sitemap publication, retrieval mediation,
acquisition routing, commerce routing, Store API behavior, or headless
isolation.

Route registration and Shopware activation do not imply publication. The
existing Edition detail controller remains fail closed and may render only the
scalar payload returned by `EditionReferenceRegistry`.

## Decision Points

The current public-rendering decision path is:

1. `IdentityIngressAllowlistSubscriber` permits the existing governed Edition
   detail route names through identity ingress.
2. `EditionsController::guardedDetail()` validates route shape and locale.
3. `EditionReferenceRegistry::resolveDetailRouteState()` validates route,
   destination, CMS, locale, SEO, acquisition, archive-continuity, and semantic
   readiness.
4. `PublicationStatePolicy::resolve()` independently checks the governed
   `publicationState`.
5. `buildGuardedRenderingPayload()` returns scalar public content only when the
   resolved publication state is `published`.

Product Edition relationship metadata remains relationship-only. It does not
control public rendering.

## Publication States

| State | Publicly renderable | Rule |
| --- | --- | --- |
| `draft` | No | Work in progress |
| `review` | No | Awaiting governance review |
| `approved` | No | Approved but not explicitly published |
| `published` | Yes | Explicit publication state |
| `suspended` | No | Immediate fail-closed withdrawal |
| `archived` | No | Terminal retained record; archive continuity required |

## State Transitions

Allowed transitions:

- `draft` -> `review`, `archived`
- `review` -> `draft`, `approved`, `archived`
- `approved` -> `draft`, `review`, `published`, `archived`
- `published` -> `suspended`, `archived`
- `suspended` -> `approved`, `published`, `archived`
- `archived` -> no transitions

Rollback from an incorrect or unsafe public release is `published` ->
`suspended`. This removes public renderability immediately. A governed
restoration may transition `suspended` -> `published`.

## Verification

Run:

```bash
ddev exec php bin/console veylune:publication-state:audit
bin/veylune-governance-check
```

The publication-state audit verifies all six state probes, Shopware-style
activation isolation, suspension withdrawal, suspension rollback, archive
terminal behavior, and EN/DE publication parity for the governed records.
