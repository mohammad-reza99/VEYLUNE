# Veylune Staging Deployment Rehearsal

Status: Local staging validation passed
Captured: 2026-09-02
Immutable tag target: `veylune-rc-20260902.1`

## Dependency and migration checkpoint

- Shopware moved from `6.7.10.0` to the official `6.7.13.1` security release.
- The dependency lock was regenerated with zero Composer security advisories.
- A recoverable DDEV database snapshot named `veylune-pre-rc-20260902` was created before migrations.
- All 37 pending non-destructive core migrations completed.
- Destructive migrations were deliberately not executed; they belong to a separately approved post-deployment cleanup window.
- Container lint and the Shopware 6.7.13.1 storefront build completed.
- The account-entry payload regression was removed by keeping sign-in compact and handing full registration to `/account/register`; login HTML is 63,532 bytes while the dedicated registration form remains available.
- Desktop 1440x1000 and mobile 390x844 browser smoke passed across the nine release surfaces with no document overflow, duplicate IDs, missing image alternatives, unnamed visible controls, or landmark regressions.
- Account handoff, login validation, mobile navigation focus/Escape, mobile filter containment, and Discover sort submission passed after the upgrade.

## Rehearsal contract

The staging rehearsal:

1. Requires every release include-candidate file to be committed.
2. Re-runs locked dependency validation, zero-advisory security audit, governance, production-readiness, and performance gates.
3. Builds a temporary archive from the immutable ref.
4. Rejects `.env`, `.ddev`, `.github`, `.codex-tmp`, `.orig`, `var`, `vendor`, `node_modules`, and generated `public/theme` paths in that archive.
5. Prints the archive SHA-256 and file count.
6. Replays the rollback plan as a non-destructive dry-run.

No remote push, external deployment, traffic change, database restore, or destructive migration is performed.

## Command

```bash
VEYLUNE_RC_REF=veylune-rc-20260902.1 bash bin/veylune-staging-rehearsal
```

## Remaining external sign-off

- Injected staging and production environment configuration.
- Real staging database and media backups with a tested restore.
- Chrome or Edge, Firefox, Safari, iOS Safari, and Android Chrome verification.
- Production cache, CDN, worker, mail, search, observability, and Core Web Vitals monitoring.
