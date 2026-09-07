# Veylune Release Candidate Freeze and Deployment Handoff

Status: Local RC ready; production deployment blocked
Captured: 2026-09-02
Source branch at capture: `main`
Pre-freeze source HEAD: `395b026`

## Freeze boundary

The local release candidate includes the governed Living Index storefront, responsive accessibility closure, controlled zero-result and 404 states, performance budgets, and the mobile Discover sort hit-target correction.

The release manifest deliberately excludes `.orig` files, the complete local `.ddev` tree, tracked `.env` defaults, generated `public/theme` residue, `var`, `vendor`, and `node_modules`. DDEV configuration is development-only and must not be copied into production environment configuration.

The handoff may create the reviewed local RC commit and annotated tag. It never performs a remote push, production deployment, traffic change, database restore, or destructive migration.

## Security release closure

The release candidate upgrades Shopware from `6.7.10.0` to the official `6.7.13.1` security release. The lock refresh also moves Twig to `3.28.0`, Symfony security-sensitive packages to patched `7.4.x` releases, Guzzle to `7.15.5`, and Composer library code to `2.10.3`. `composer audit --locked` reports zero advisories after the update.

The Shopware MCP transport bundle remains disabled. A pagination parameter required by the updated core service graph is defined explicitly without activating an HTTP or stdio MCP transport.

## Verified local matrix

| Surface | Desktop 1440 x 1000 | Mobile 390 x 844 | Reflow checkpoint 720 x 900 |
| --- | --- | --- | --- |
| Home | Pass | Pass | Pass |
| Furniture | Pass | Pass | Pass |
| Founder Selection | Pass | Pass | Pass |
| Living Room | Pass | Pass | Pass |
| Discover results | Pass | Pass | Pass |
| Selection | Pass | Pass | Pass |
| Account entry | Pass | Pass | Pass |
| Contact Studio | Pass | Pass | Pass |
| Controlled 404 | Pass | Pass | Pass |

The automated browser pass checks one `main`, one `h1`, language, image alternatives, duplicate IDs, visible control names, and document overflow. Account controls use associated `label[for]` names. Horizontally scrollable header rails are contained and do not create document overflow.

Interaction checks cover mobile navigation open/focus/Escape/restore, mobile filter containment, visible and non-overlapped sort Apply action, login validation associations, Contact Studio error summary, zero-result recovery, and controlled 404 response contracts.

This local browser runtime does not prove Firefox, Safari, or physical-device behavior. Real Chrome or Edge, Firefox, Safari, iOS Safari, and Android Chrome remain a production sign-off requirement.

## Production configuration contract

Run the production gate only with deployment environment variables injected:

```bash
bash bin/veylune-production-config-audit --production
```

It requires production mode, debug disabled, HTTPS `APP_URL`, non-development database and mail endpoints, draft preview disabled, and Shopware HTTP cache enabled. Values are validated without printing secrets.

The current DDEV environment is intentionally not production-safe: it uses `APP_ENV=dev`, Mailpit, and a local draft-preview override.

## Deployment checklist

1. Create an immutable RC commit and tag from the reviewed include-candidate manifest.
2. Build the same commit in CI with locked Composer dependencies and no development packages.
3. Pass `bin/veylune-production-config-audit --production` against injected production configuration.
4. Capture fresh production database and media backups; record owner, timestamp, storage location, restore test, and retention.
5. Preserve the currently deployed immutable artifact as the rollback target.
6. Put traffic into the approved maintenance or blue-green deployment path.
7. Run Shopware migrations and plugin lifecycle commands once, then compile the theme and clear/warm caches.
8. Smoke-test home, Furniture, Founder Selection, Living Room, Discover, Selection, account, Contact Studio, zero results, and controlled 404.
9. Complete the external browser/device matrix and accessibility keyboard pass.
10. Reopen traffic and monitor HTTP errors, queue failures, mail delivery, search, LCP, INP, and CLS.

## Rollback rehearsal

Run the safe rehearsal:

```bash
bash bin/veylune-rollback-readiness --dry-run
```

The command only verifies source references and prints the rollback sequence. It never switches git state, restores a database, changes traffic, or deletes files. A production rollback is blocked until a fresh backup, immutable prior artifact, named owner, and rollback window exist.

## Repeatable local RC gate

```bash
bash bin/veylune-release-candidate
```

This runs environment classification, source manifest safety, rollback dry-run, the full governance suite, production-readiness and performance gates, and `git diff --check`.

After the immutable tag exists, run:

```bash
VEYLUNE_RC_REF=veylune-rc-20260902.1 bash bin/veylune-staging-rehearsal
```

## Open release blockers

- Production secrets and infrastructure settings were not supplied, so the production configuration gate cannot be passed locally.
- A fresh production database/media backup and tested restore point do not yet exist in this workspace.
- The Firefox, Safari, and physical-device matrix requires those external runtimes.
- Production Core Web Vitals and operational alerting require the real cache, CDN, workers, and traffic environment.
- Approximately 3.58 GB of stale generated theme residue remains outside the active runtime path; cleanup needs explicit destructive-action authorization.
