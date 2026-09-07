# Veylune Staging Environment Provisioning

Status: Local contract and artifact path ready; external target not supplied
Release candidate: `veylune-rc-20260902.1`

## Provisioning decision

Staging must reproduce production behavior without sharing production data,
credentials, mail delivery, or traffic. Shopware runs with `APP_ENV=prod`, debug
disabled, HTTPS, draft preview disabled, and HTTP cache enabled. Secrets are
injected by the deployment platform and never copied into the repository or
release artifact.

The release topology is immutable releases plus shared state and a `current`
symlink. Release, shared, and current paths must be absolute, distinct, and must
never resolve to the filesystem root.

## Environment contract

`config/veylune-staging.env.example` is the canonical list of required variable
names. Its values are deliberately non-deployable placeholders. Copy the names
to the staging secret manager, replace every placeholder, and set
`VEYLUNE_STAGING_CONFIRMED=1` only after independently confirming that the host
and URL are not production.

Validate injected configuration without printing secret values:

```bash
bin/veylune-staging-readiness --environment
```

After SSH host identity is trusted, run the read-only topology probe:

```bash
bin/veylune-staging-readiness --probe
```

The probe checks access and directory topology. It does not upload files,
change a symlink, execute migrations, or alter traffic.

## Immutable artifact

Build the staging source artifact outside the worktree:

```bash
artifact_dir="$(mktemp -d)"
VEYLUNE_RC_REF=veylune-rc-20260902.1 \
VEYLUNE_ARTIFACT_DIR="$artifact_dir" \
bin/veylune-build-staging-artifact
```

The builder accepts only a valid annotated tag object, applies export-ignore rules,
rejects local, secret-bearing, generated, and backup paths, and emits a manifest
containing the release commit, archive SHA-256, file count, and Composer lock
SHA-256. Dependencies are installed from `composer.lock` in the controlled
staging build step; `vendor` is never copied from a developer machine.

## External inputs still required

- staging HTTPS URL and SSH host identity
- non-production database and mail endpoints
- secret-manager values
- release, shared, and current filesystem paths
- backup owner and tested rollback reference
- infrastructure owner approval for the first real deployment

Until those inputs are injected, the preflight fails closed and no remote
deployment is attempted.
