# Veylune Runtime Architecture

This document is operational architecture documentation for the governed Edition
runtime. It describes what exists now. It does not authorize new route classes,
commerce, navigation, discoverability expansion, editorial systems, or topology
systems.

## System Overview

Veylune Studio currently exposes governed Edition detail surfaces through a
fail-closed Shopware storefront path. Public output is limited to scalar Edition
context from the approved Edition reference registry.

The system has three operational layers:

- request runtime: route shape, locale, readiness, payload assembly, Twig render
- registry governance: Edition reference configuration and semantic registry
- command-only verification: semantic, authoring, distributed-runtime,
  topology-pressure, and CI aggregate checks

Runtime code must stay small. Cross-route scans and topology-pressure simulations
must remain outside the public request path.

## Governance Overview

Governance is implemented as deployment-sensitive validation around controlled
configuration. The main enforcement points are:

- `EditionReferenceRegistry` for route readiness and guarded payload assembly
- `PublicationStatePolicy` for explicit identity-record publication state and
  transition enforcement
- `SemanticRegistry` for vocabulary, implication, authoring, and semantic parity
  validation
- `GovernanceAuditService` for command-only orchestration of shared audits
- `bin/veylune-governance-check` for CI-ready regression verification

Governance metadata is internal. Public routes must not expose audit status,
classification scores, rollback targets, topology state, or route-network state.

## Runtime Overview

The public runtime enters through `EditionsController::guardedDetail()`.

Request lifecycle:

1. Shopware matches `/editions/{reference}` or `/editionen/{reference}`.
2. The controller rejects unprefixed German detail routes when the locale is not
   German.
3. The controller rejects malformed references before registry lookup.
4. `EditionReferenceRegistry::resolveDetailRouteState()` checks route contract,
   readiness, explicit publication state, and semantic readiness.
5. Non-renderable states return an empty `404`.
6. `buildGuardedRenderingPayload()` assembles a scalar payload only.
7. Twig renders `edition-detail-skeleton.html.twig`.

Runtime response boundaries:

- valid governed route: `200` with scalar skeleton markup
- invalid route or invalid readiness: empty/non-diagnostic `404`
- public output: no products, CMS entities, relationship renderers, route
  network, audit metadata, or debug metadata

## Semantic Governance Overview

`semantic_registry.php` is the controlled vocabulary and authoring policy source.
It defines:

- approved and forbidden terms
- controlled synonyms
- implication classifications
- emotional, prestige, and escalation ceilings
- EN/DE locale mappings
- authoring lifecycle and contributor roles
- rollback and ownership metadata

`SemanticRegistry` reads the registry and returns `SemanticAuditResult` objects.
Copy changes are treated as governance events and deployment-sensitive checks.

## Distributed Runtime Overview

Two governed Edition references are currently registered. Distributed-runtime
checks verify that both routes preserve:

- structural parity
- semantic-density parity
- topology neutrality
- route-network invisibility
- adjacency-language absence

Distributed runtime checks are command-only. They do not run during public
requests.

## Topology Containment Overview

Topology containment prevents accumulated routes from implying browsing,
catalog, archive, recommendation, collection, or storefront behavior.

Topology-pressure simulation uses internal candidate fixtures inside
`GovernanceAuditService`. These candidates must not be added to the route
registry, sitemap, navigation, or public pages.

## Rollback Orchestration Overview

Rollback is operationally represented by:

- semantic rollback targets in governed records
- registry rollback metadata
- audit failures that block deployment
- fail-closed route readiness when semantic readiness fails
- CI aggregate verification that stops at the first blocking regression

Rollback does not require public diagnostics. Under failure, public behavior must
remain boring: denied routes return non-diagnostic `404` responses.

## Governance Audit Overview

Governance audit commands are thin wrappers around `GovernanceAuditService`:

- `veylune:semantic:audit`
- `veylune:semantic:authoring-audit`
- `veylune:runtime:distributed-audit`
- `veylune:runtime:topology-pressure-audit`
- `veylune:publication-state:audit`

The service owns fixture composition, parity checks, topology checks, and
implication checks. Commands format output and return exit status only.

## CI Governance Verification Overview

`bin/veylune-governance-check` is the deployment-blocking aggregate command.
It verifies syntax, container linting, audit command success, audit determinism,
semantic failure fixtures, runtime route behavior, rendered browser surface,
discoverability containment, sitemap containment, and public observability
containment.

Run:

```bash
bin/veylune-governance-check
```

## Runtime Vs Command-Only Boundary

Runs during request runtime:

- route shape validation
- locale validation
- route readiness resolution
- semantic readiness of the current record
- scalar payload assembly
- Twig skeleton rendering

Never runs during request runtime:

- distributed-runtime audit
- topology-pressure simulation
- authoring workflow fixtures
- audit determinism checks
- sitemap/discoverability scans
- CI aggregate orchestration
- simulated route candidate construction
