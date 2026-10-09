# Veylune engineering guide

## Scope

This repository is a Shopware 6.7 storefront. `custom/plugins/VeyluneTheme` currently contains the active presentation layer plus some catalog, governance, and local-test services. Prefer compatibility-preserving changes and do not move responsibilities across extensions without regression coverage.

## Non-negotiable boundaries

- Public catalog routes must read products through `sales_channel.product.repository` and therefore respect active state, sales-channel visibility, availability, pricing, and Shopware context.
- Draft catalog data belongs only on token-protected `__veylune-preview` routes. Preview responses must remain `noindex`, `nofollow`, `no-store`, and must never create real orders or payments.
- Use native Shopware cart, checkout, customer, order, search, and variant contracts where they fit. Do not create a second commerce engine.
- Never delete or rewrite supplier, product, order, or customer data as part of an ordinary code change.
- Keep real payment activation, production deployment, secrets, and destructive migrations outside autonomous local work.

## Storefront conventions

- Reuse the tokens and components under `custom/plugins/VeyluneTheme/src/Resources/app/storefront/src/scss`.
- Run `$veylune-design-review` for changes that affect storefront layout, interaction, typography, color, responsive behavior, or reusable UI components.
- Preserve keyboard access, visible focus, accessible names, reduced-motion behavior, and mobile containment.
- Avoid new `!important` rules unless the cascade cannot be corrected safely and the reason is documented beside the rule.
- Load JavaScript only where its DOM contract exists; keep modules idempotent and safe when markup is absent.

## Verification

Run the narrowest relevant checks while iterating, then the integration gate before committing. Node is provided by DDEV, so run Node commands with `ddev exec node ...`.

Core commands:

```bash
ddev composer validate --no-check-publish
ddev exec node tools/audit/veylune-active-source-contract.cjs
ddev exec php custom/plugins/VeyluneTheme/bin/governance-regression-check.php
bash tools/audit/veylune-secondary-route-audit.sh
```

For PHP changes, lint every changed PHP file. For storefront changes, compile the theme and run the relevant browser and accessibility checks. A passing route or syntax check alone is not launch-readiness evidence.

## Git safety

- Preserve existing work and inspect `git status` before editing.
- Do not use destructive reset, clean, force push, or broad file replacement.
- Keep commits focused and include only verified changes created for the active task.
- Record external business blockers separately from code failures.
