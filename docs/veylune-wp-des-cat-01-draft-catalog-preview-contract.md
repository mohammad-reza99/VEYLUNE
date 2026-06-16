# VEYLUNE STUDIO

## WP-DES-CAT-01 — Draft Catalog Preview Contract

**Status:** Development-preview implementation  
**Catalog source:** `WP-CAT-04-DRAFT-50`  
**Public commerce authority:** None

## Access Contract

Preview access requires all three conditions:

1. `APP_ENV=dev`
2. `VEYLUNE_DRAFT_PREVIEW_ENABLED=1`
3. the request query parameter `token` exactly matches
   `VEYLUNE_DRAFT_PREVIEW_TOKEN`

Any failed condition returns HTTP 404. Committed environment defaults keep the
preview disabled with an empty token. The local development override is ignored
by Git.

Preview responses include:

```text
X-Robots-Tag: noindex, nofollow, noarchive, nosnippet
Cache-Control: private, no-store, max-age=0
X-Veylune-Preview: WP-CAT-04-DRAFT-50
```

## Runtime Boundary

Preview uses dedicated `/__veylune-preview/catalog` routes. It does not modify
the public homepage, category, room, collection, product, search, cart,
checkout, feed, or sitemap routes.

The preview service reads inactive draft products through the administrative
DAL repository and converts them to read-only view models. Cards contain no
Shopware product IDs, PDP links, cart forms, availability statements, stock
claims, supplier claims, or delivery claims.

## Allowed Environments

Only the Symfony `dev` environment may render preview pages. `prod`, `test`,
and any other environment fail closed even if a token is supplied.

## Enable

Set these values in the ignored `.env.local` file and clear the cache:

```dotenv
VEYLUNE_DRAFT_PREVIEW_ENABLED=1
VEYLUNE_DRAFT_PREVIEW_TOKEN="<private-review-token>"
```

```bash
ddev exec php bin/console cache:clear
```

## Disable / Rollback

```bash
sed -i 's/^VEYLUNE_DRAFT_PREVIEW_ENABLED=.*/VEYLUNE_DRAFT_PREVIEW_ENABLED=0/' .env.local
ddev exec php bin/console cache:clear
```

Disabling preview does not change or delete catalog entities. Full code
rollback removes the preview controller, access and data services, templates,
SCSS import, service registrations, route allowlist entries, and environment
defaults. The 50 draft catalog products remain governed by WP-IMP-CAT-01.

## Safety Invariants

- Seeded products remain `active=false`.
- Seeded products retain zero sales-channel visibility.
- Seeded products retain zero stock and availability.
- No product SEO URLs or search keywords are created.
- Direct draft PDP access remains denied.
- Public cart and checkout admission remains denied.
- Preview routes never enter sitemap generation.
- Public storefront behavior is unchanged when preview is enabled or disabled.
