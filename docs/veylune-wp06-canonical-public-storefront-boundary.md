# WP-06 Canonical Public Storefront Boundary

## Scope

WP-06 designates the existing `VEYLUNE STUDIO` sales channel as the canonical
public storefront. This changes ownership classification only. It does not
activate commerce surfaces, publish products or categories, enable search,
restore native sitemap sources, expose Store API routes, or activate WP-01
foundation channels.

## Storefront Role Contract

`StorefrontRoleRegistry` is the runtime ownership contract for known sales
channels.

| Sales channel | ID | Role |
| --- | --- | --- |
| `VEYLUNE STUDIO` | `019e3bf9c220717884d2a4eaca77c2d1` | `canonical_public_storefront` |
| `Headless` | `98432def39fc4624b33213a56b8c944d` | `headless` |
| `VEYLUNE Identity Foundation` | `019e9e8f000070008000000000000001` | `identity_foundation` |
| `VEYLUNE Acquisition Foundation` | `019e9e8f000070008000000000000002` | `acquisition_foundation` |
| `VEYLUNE Private Commerce Foundation` | `019e9e8f000070008000000000000003` | `private_commerce_foundation` |

The three foundation channels remain inactive and domainless.

## Runtime Preservation

Existing identity-era classes remain transitional containment mechanisms:

- `IdentityIngressAllowlistSubscriber`
- `IdentityUrlProvider`
- `IdentitySitemapSourceFilterSubscriber`
- `IdentityScopedHomeUrlProvider`
- `IdentityScopedCustomUrlProvider`
- `SitemapGovernanceAuditService`

Their runtime behavior is intentionally unchanged during WP-06. They now scope
their existing containment behavior through the canonical public storefront
role instead of embedding an identity-only storefront ID assumption.

Edition retrieval remains mediated by `IdentityRetrievalMediator`. The mediator
accepts governed Edition retrieval only from the canonical public storefront.
WP-03 publication-state enforcement remains authoritative.

## Explicit Non-Activation

WP-06 does not authorize:

- product or category publication
- catalog browsing
- search, cart, checkout, account, or wishlist exposure
- Store API exposure
- sitemap restoration or regeneration
- supplier onboarding
- foundation-channel activation

## Rollback

Rollback is code and documentation only:

1. Revert the WP-06 storefront-role registry and references.
2. Revert the WP-06 documentation additions.
3. Clear the Shopware cache.
4. Run the existing governance suite.

No database rollback is required.
