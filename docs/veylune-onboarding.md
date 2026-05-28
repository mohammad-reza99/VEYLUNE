# Veylune Engineering Onboarding

This guide gives a new engineer the minimum safe mental model for maintaining
the governed Edition runtime.

## What To Understand First

1. Public runtime is fail-closed.
2. Route registry configuration is deployment-sensitive.
3. Semantic copy is governed infrastructure, not freeform content.
4. Distributed and topology checks are command-only.
5. Public pages must not expose route networks, governance diagnostics, products,
   CMS entities, relationship renderers, navigation expansion, or commerce.
6. `bin/veylune-governance-check` is the normal confidence gate.

## Architecture Philosophy

The system favors containment over feature expansion. A valid route may render a
minimal scalar skeleton. An invalid route returns a quiet `404`.

The most important distinction is request runtime versus command-only
verification. Request runtime answers one question: may this single governed
reference render now? Command-only checks answer broader questions about
semantic consistency, topology pressure, discovery, and regression safety.

## Core Files

- `EditionsController.php`: public route entry and fail-closed denial
- `EditionReferenceRegistry.php`: route readiness and scalar payload assembly
- `edition_references.php`: approved Edition reference configuration
- `SemanticRegistry.php`: semantic and authoring validation
- `semantic_registry.php`: controlled vocabulary and workflow policy
- `GovernanceAuditService.php`: shared command-only audit orchestration
- `bin/veylune-governance-check`: aggregate CI-ready verification
- `docs/veylune-quiet-premium-operations.md`: internal media and content
  preparation workflow

## Registry Structure

`edition_references.php` contains governed records. Each record needs stable
routes, detail-destination contract metadata, semantic version metadata,
localized scalar fields, readiness gates, and rollback metadata.

`semantic_registry.php` contains vocabulary, implication, authoring lifecycle,
ownership, and rollback policy. It is internal infrastructure, not a CMS.

## Forbidden Patterns

Do not add:

- route directories
- navigation links to governed detail routes
- sitemap inclusion for governed detail routes
- product or CMS entity rendering on governed skeletons
- relationship, recommendation, collection, archive, or sequence language
- public audit, topology, rollback, authoring, or classification metadata
- preview/debug unlock systems
- route-local copy ownership

## Runtime Mistakes That Matter Most

- Returning diagnostic bodies for denied governed detail routes
- Moving distributed or topology-pressure scans into request runtime
- Rendering raw registry records instead of guarded scalar payloads
- Adding public links that create discoverability expansion
- Weakening semantic checks to make copy pass
- Treating simulated topology candidates as public route records

## Operational Routine

Before deployment-sensitive changes:

```bash
bin/veylune-governance-check
```

If it fails, fix the first failure and re-run the full command. Do not deploy
with a failing governance check.

For real content or media preparation, use
`docs/veylune-quiet-premium-operations.md` before touching registry records.
Facts and media readiness should be reviewed before semantic fields or route
metadata change.

## Advanced Concepts

The advanced parts are semantic parity, topology-pressure simulation, and
distributed-runtime neutrality. These are intentionally command-only. A new
engineer should understand their outputs before changing their internals.

## Simplification Philosophy

Do not add new governance layers unless an existing check cannot express a real
production risk. Prefer fewer commands, shared audit orchestration, stable
registry shapes, and explicit runtime boundaries.
