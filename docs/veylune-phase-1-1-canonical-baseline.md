# Veylune Phase 1.1 Canonical Baseline

Status date: 2026-09-14

## Outcome

Phase 1.1 establishes one recoverable evidence boundary and one canonical inventory before additional PDP, media, or visual work. It does not delete, revert, publish, or activate products.

## Git boundary

- Branch at capture: `main`
- HEAD: `30673455978e07b3d1fd54f5e44f8f6c19d7423a`
- `origin/main`: `395b02662ffdb1d7c9ac065142e12f49e92caec1`
- Ahead: 2
- Behind: 0
- Pre-Phase-1.1 working-tree entries: 68
- Tracked diff files: 58
- Untracked files: 10
- Deleted files: 3
- Status fingerprint: `e505c9b001333d05b4094a2a9ee4f7bffb9f5f69494c8e408ce7732bb7e7aa85`
- Tracked binary-diff fingerprint: `e0f584662752e4b3169c5858ac0e2f68a5020c5a98ab559c5ec07e4cb23911a2`
- Untracked-list fingerprint: `322133637c4e1663dd1ce206e95025c40cd6020a16386f5107c84c988aeb76f5`

No existing user change was deleted or reverted during this baseline.

## Runtime evidence

### Public storefront

- 47 audited routes and 94 desktop/mobile surfaces.
- 90 returned HTTP 200.
- Desktop classification: 42 current, 3 mixed, 0 legacy, 2 broken.
- Mixed: `/editions`, `/journal`, `/inspiration`.
- Decision required: direct empty-session `/checkout/confirm` and `/wishlist` currently return 404.
- These two 404 outcomes are activation-pending policy denials, not random missing router definitions. Their final user-facing behavior is still provisional.
- The latest public artifact also records broken image evidence on `/`, `/discover`, and `/discover?q=room`.

### Private preview

- 67 routes and 134 desktop/mobile surfaces.
- All 134 returned HTTP 200 with no recorded document overflow, JavaScript error, or failed response.
- Modernized: catalog home, 6 categories, 5 rooms, and 2 collections.
- Remaining: 50 PDPs and the mixed private account surface; cart and checkout need final unification.

### Interaction evidence

- Latest interaction audit covered 41 routes and 82 desktop/mobile surfaces with zero recorded issue surfaces.
- Header links, registration shipping toggle, privacy overlays, Selection persistence, Discover search/filter behavior, and project-brief state had passing focused evidence.
- These reports are a baseline only and must be rerun after each owning component changes.
- Current action evidence is representative, not exhaustive: most header links were extracted but not individually clicked, and real contact delivery, authentication, product-populated commerce, governed PDP, and authenticated account states remain uncovered.

## Frontend ownership risk

- 138 SCSS files and about 31,061 lines.
- 135 imports in `base.scss`.
- 21 JavaScript source files.
- 99 Twig templates.
- 40 `.orig` files remain available for later recoverable cleanup.

The main engineering risk is not missing CSS volume. It is overlapping ownership and late cascade correction. Phase 1.2 must assign one owner to each shared component before consolidation.

## Media boundary

- Draft product records: 50.
- Dedicated product assets in theme code: 19.
- Products without a dedicated asset: 31.
- Current product media source: theme asset files and code/CSS mapping.
- Target product media source: Shopware Admin Media associations.

Stable brand assets may remain in code. Product, category, room, collection, and CMS media must become Admin-managed business content.

## Commercial boundary

- Launch-cohort products at Level 3: 0 of 10.
- Accepted supplier evidence: 0 of 120 required cells in the captured readiness baseline.
- Draft or preview behavior is not proof of sellability.

## Route and security conflicts

- Public category routes are live and allowlisted, but are missing from `StorefrontRouteOwnershipPolicy::surfaceForRoute()` while the declared category surface remains activation-pending.
- Permanent and Editorial collections have both short and long render paths instead of one canonical render path plus redirect.
- Private preview is dev-only and token-gated, but does not yet enforce the same dev-host allowlist as the retired vision routes.
- The authorized preview response has no verified CSP or Permissions-Policy in the captured header evidence.
- A literal preview token was found in the tracked governance script. Phase 1.1 removes the literal and changes the test to require `VEYLUNE_DRAFT_PREVIEW_TOKEN` from the environment.
- Because the old token exists in Git history and prior artifacts, it must be rotated before any external preview exposure.

## Canonical files

- `docs/veylune-master-roadmap-v2.md`: human execution roadmap.
- `config/veylune-roadmap-status.json`: machine-readable phase and evidence status.
- `config/veylune-surface-contract.json`: machine-readable public/private route, action, and media contract.
- `bin/veylune-roadmap-baseline-audit`: deterministic contract validation.

## Deferred decisions

1. `/checkout/confirm`: direct empty-session requests should redirect to cart or enter a documented guarded flow, not remain an unexplained route result.
2. `/wishlist`: activate a real wishlist or remove all public wishlist promises.
3. `/journal` and `/inspiration`: give each a distinct destination or an intentional clean redirect.
4. Symfony MCP-related dependency changes in the dirty baseline require explicit ownership review before production release.
5. Public category route ownership conflict must be resolved before route activation rules can be trusted.
6. Preview host allowlisting and success-response security headers need closure.
7. `.orig`, temporary scripts, and stale generated theme residue remain untouched until a recoverable cleanup phase.

## Exit gate

Phase 1.1 is complete when:

- The two JSON contracts validate.
- The master roadmap and this baseline are present in the repository.
- PHP, Twig, container, Composer, storefront build, and diff checks pass.
- A recoverable checkpoint records the reviewed working state without exposing secrets.

## Next step

Phase 1.2 - Component Ownership and Cascade Reduction Plan.

## Completion record

- Canonical roadmap and machine-readable surface contracts validate.
- The complete governance gate passes against the running DDEV storefront.
- PHP, Twig, container, Composer, storefront build, and diff checks pass; Composer reports exact-version warnings only.
- The tracked preview-token literal was removed. Rotation of the historical token remains mandatory before external preview exposure.
- Recoverable checkpoint: `veylune-phase-1.1-baseline-20260914`.
