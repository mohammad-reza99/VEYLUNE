# Veylune Operational Playbooks

These playbooks are executable operating procedures. They do not add governance
doctrine or authorize public expansion.

## Deployment Procedure

1. Review changed files for public-surface risk:
   - controller changes
   - Twig changes
   - `edition_references.php`
   - `semantic_registry.php`
   - audit service or command changes
2. Run the aggregate governance check:

   ```bash
   bin/veylune-governance-check
   ```

3. Treat any failure as deployment blocking.
4. Confirm no new public routes, navigation links, sitemap entries, product
   surfaces, relationship renderers, or debug outputs were added.
5. Deploy only after the aggregate command passes.

## Rollback Procedure

1. Identify the failing gate:
   - runtime route
   - semantic audit
   - authoring audit
   - distributed runtime
   - topology pressure
   - discoverability
   - public observability
2. Revert the smallest deployment unit that introduced the failure.
3. If registry copy caused the failure, restore the previous semantic version and
   rollback target pair.
4. Re-run:

   ```bash
   bin/veylune-governance-check
   ```

5. Confirm denied states remain non-diagnostic.

Rollback continuity is less important than containment. If copy continuity and
fail-closed behavior conflict, fail-closed behavior wins.

## Failed Audit Recovery

1. Re-run the specific failed command:

   ```bash
   ddev exec php bin/console veylune:semantic:audit
   ddev exec php bin/console veylune:semantic:authoring-audit
   ddev exec php bin/console veylune:runtime:distributed-audit
   ddev exec php bin/console veylune:runtime:topology-pressure-audit
   ```

2. Fix the underlying registry, copy, topology, or fixture issue.
3. Do not silence violations by weakening forbidden terms, implication rules, or
   parity checks.
4. Re-run the aggregate command.

## Semantic Corruption Recovery

Symptoms:

- semantic audit fails
- governed route starts returning `404`
- CI reports forbidden vocabulary, implication drift, missing semantic version,
  or missing rollback target

Procedure:

1. Restore the last approved scalar fields and semantic version metadata.
2. Confirm EN/DE parity is intact.
3. Run `veylune:semantic:audit`.
4. Run the aggregate governance check.

## Topology Corruption Recovery

Symptoms:

- distributed or topology-pressure audit fails
- public output implies grouping, sequence, archive, route count, catalog,
  recommendation, or route network

Procedure:

1. Remove the topology-signaling language or route pattern.
2. Confirm simulated candidates remain internal-only.
3. Run distributed and topology-pressure audits.
4. Run the aggregate governance check.

## Discoverability Leakage Response

Symptoms:

- governed detail reference appears on homepage, `/editions`, sitemap, or
  navigation output
- route count or route-network language appears publicly

Procedure:

1. Remove the public link, sitemap entry, route directory, or reference string.
2. Clear generated cache/sitemap artifacts if they contain stale references.
3. Re-run the aggregate governance check.
4. Confirm public discovery remains constrained to direct known URLs only.

## CI Failure Recovery

1. Read the first `[FAIL]` line from `bin/veylune-governance-check`.
2. Re-run the narrow failed command if one is named.
3. Fix the smallest cause.
4. Re-run the full aggregate command.

Do not bypass the aggregate command for deployment-sensitive changes.

## Governance Regression Response

If a previously blocked condition becomes accepted:

1. Stop deployment.
2. Inspect `SemanticRegistry` or `GovernanceAuditService` changes first.
3. Restore the blocking behavior.
4. Add or tighten a regression fixture only if an existing fixture cannot express
   the failure.
5. Re-run the aggregate command.
