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

## Route Ownership Contract

`StorefrontRouteOwnershipPolicy` is the successor-policy contract for canonical
storefront route families. It records current exposure state, future owner, and
activation prerequisites without activating any pending surface.

| Surface | Current state | Future owner | Activation prerequisites |
| --- | --- | --- | --- |
| Homepage | `public` | `storefront_commerce` | none |
| Products | `activation_pending` | `product_publication_policy` | product publication policy, catalog quality gate, PDP runtime verification |
| Categories | `activation_pending` | `category_publication_policy` | category publication policy, taxonomy quality gate, navigation runtime verification |
| Collections | `activation_pending` | `collection_policy` | collection publication policy, collection taxonomy gate, listing runtime verification |
| Search | `activation_pending` | `search_architecture` | search governance, indexing readiness, search runtime verification |
| Cart | `activation_pending` | `native_commerce` | sellability policy, cart runtime verification |
| Checkout | `activation_pending` | `native_commerce` | cart activation, payment and shipping readiness, checkout runtime verification |
| Account | `activation_pending` | `native_commerce` | customer account policy, account runtime verification |
| Wishlist | `activation_pending` | `commerce_policy` | wishlist business decision, wishlist runtime verification |
| Consultation | `public` | `acquisition_policy` | none |
| Trade | `activation_pending` | `acquisition_policy` | trade workflow policy, trade runtime verification |
| Editions | `governed_public` | `edition_governance` | publication-state policy, identity retrieval mediation |

## WP-02 Successor Policy

The WP-02 deny-by-default doctrine remains authoritative during WP-06.
`IdentityIngressAllowlistSubscriber` is retained as a transitional class name,
but it now applies these explicit boundary steps:

1. Deny Store API and Admin API paths on canonical storefront ingress.
2. Deny route families classified as `activation_pending`.
3. Permit only the current allowlisted public and governed-public routes.
4. Deny every unclassified route fail closed.

Future work packages may evolve a route family from `activation_pending` only
after its listed prerequisites are implemented, verified, and explicitly
authorized. Removing a denial without changing the route-ownership contract is
not sufficient authorization.

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

1. Remove `StorefrontRouteOwnershipPolicy`.
2. Remove its activation-pending denial branch from
   `IdentityIngressAllowlistSubscriber`.
3. Revert the WP-06 storefront-role registry and references if rolling back
   Sprint 1 as well.
4. Revert the WP-06 documentation and regression terminology additions.
5. Clear the Shopware cache.
6. Run the existing governance suite.

No database rollback is required.
