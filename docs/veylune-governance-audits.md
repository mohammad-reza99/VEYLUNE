# Veylune Governance Audit Architecture

This document describes the current operational audit structure for governed
Edition runtime. It is production-hardening documentation, not a governance
expansion plan.

## Runtime Boundary

Public request handling remains intentionally lean:

1. `EditionsController::guardedDetail()` validates route shape and locale.
2. `EditionReferenceRegistry::resolveDetailRouteState()` checks the local
   route contract, publication gate, and semantic readiness.
3. Invalid state returns an empty `404`.
4. Valid state renders only the scalar payload from
   `buildGuardedRenderingPayload()`.

Cross-route topology scans, topology-pressure simulations, authoring fixtures,
and distributed parity audits are command-only. They must not be moved into the
request path.

## Shared Orchestration

`VeyluneTheme\Governance\GovernanceAuditService` owns shared audit orchestration:

- semantic deployment audit delegation
- semantic authoring audit fixtures
- distributed runtime parity and topology checks
- topology-pressure simulation fixtures
- structural parity scanning
- semantic density scanning
- route topology validation
- adjacency and storefront-emergence term scanning
- route-network visibility scanning

Commands must format output and return exit status only.

## Commands

`veylune:semantic:audit`

- Runs semantic deployment readiness for approved Edition references.
- Delegates to `GovernanceAuditService::auditSemanticReferences()`.

`veylune:semantic:authoring-audit`

- Verifies that a valid authoring contribution passes and an invalid
  contribution is blocked.
- Delegates to `GovernanceAuditService::auditSemanticAuthoringWorkflow()`.

`veylune:runtime:distributed-audit`

- Verifies the current two-route governed runtime.
- Delegates to `GovernanceAuditService::auditDistributedRuntime()`.

`veylune:runtime:topology-pressure-audit`

- Simulates additional route candidates internally and verifies topology
  pressure resistance.
- Delegates to `GovernanceAuditService::auditTopologyPressure()`.

`veylune:publication-state:audit`

- Verifies explicit publication-state enforcement, suspension withdrawal,
  rollback restoration, archive terminal behavior, and EN/DE parity.
- Delegates to `GovernanceAuditService::auditPublicationStates()`.

## Fixture Ownership

Internal audit fixtures live in `GovernanceAuditService`.

Fixtures are not public content. They must not be registered as governed routes,
exposed through navigation, added to sitemaps, or rendered by controllers.

## Runtime Guarantees

The consolidation keeps these guarantees intact:

- fail-closed route denial
- scalar-only public payloads
- semantic deployment gating
- authoring contribution containment
- two-route topology neutrality
- topology-pressure resistance
- explicit publication-state enforcement
- discoverability containment
- command-only cross-route scanning

## Future Consolidation Notes

Recommended next consolidation work:

- move semantic failure-injection script into a formal test or remove it
- reduce duplication inside `edition_references.php` with shared defaults
- keep audit commands stable while service internals are simplified
- avoid adding new governance commands unless an existing command cannot express
  the operational need

## Automated Verification

`bin/veylune-governance-check` is the CI-ready aggregate verification entrypoint.
It runs from the project root and executes runtime-sensitive checks through
DDEV.

The command verifies:

- PHP syntax for Veylune plugin source and internal scripts
- Symfony container linting
- semantic, authoring, distributed-runtime, and topology-pressure audit commands
- isolated semantic and authoring regression fixtures
- repeated audit-command determinism across three runs
- governed EN/DE detail route reachability
- empty or non-diagnostic denial for unknown, malformed, guessed, and simulated
  references
- rendered browser-surface containment for governed detail skeletons
- homepage, `/editions`, and sitemap discoverability containment
- public absence of topology, route-network, audit, rollback, authoring,
  approval, classification, and debug metadata

Run it before deployment-sensitive changes:

```bash
bin/veylune-governance-check
```

The command exits non-zero on the first failed gate. Failures are deployment
blocking until the underlying registry, route, copy, topology, or public-output
regression is corrected.

### Regression Harness Boundary

`custom/plugins/VeyluneTheme/bin/governance-regression-check.php` is an
internal-only deterministic fixture runner. It intentionally exercises forbidden
vocabulary, synonym bypass, implication drift, multilingual divergence, missing
semantic version, missing rollback target, invalid authoring transitions,
route-local ownership, and deployment bypass cases.

The harness does not mutate `edition_references.php`, does not register new
routes, and does not expose simulated topology-pressure candidates publicly.

### Troubleshooting

If `bin/veylune-governance-check` fails:

1. Read the first `[FAIL]` line; the aggregate command stops at the first
   blocking regression.
2. Re-run the specific command named in the failure when applicable.
3. For HTTP containment failures, inspect only the temporary output described by
   the failing route during the current run; public diagnostics must not be
   added to route responses.
4. For semantic fixture failures, fix the registry or audit service behavior
   without weakening forbidden-language, implication, parity, or rollback gates.
5. For determinism failures, remove nondeterministic ordering, timestamps, or
   environment-dependent output from the relevant audit command.

Cross-route, topology-pressure, and discoverability tests remain command-only.
They must not be moved into `EditionsController` or other request-path services.

The repository does not currently carry a Playwright dependency. Browser-surface
verification is therefore implemented as deterministic rendered-HTML assertions
inside `bin/veylune-governance-check` instead of introducing a new JavaScript
test dependency during production maturity work.
