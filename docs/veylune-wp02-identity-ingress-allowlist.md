# WP-02 Identity Ingress Allowlist

## Scope

WP-02 adds a deny-by-default allowlist for the existing public-behavior
`VEYLUNE STUDIO` sales channel. It does not activate WP-01 foundations, assign
domains, mediate Store API requests, replace sitemap generation, or create
Acquisition or Commerce routing.

## Allowed Identity Ingress

- homepage and localized homepage
- Editions index and governed Edition detail routes
- Atelier Partnerships
- approved Consultation category
- approved contact, imprint, and privacy CMS pages
- contact-form and basic-captcha support
- cookie-consent support
- current transitional robots and sitemap routes
- Shopware header and footer ESI support

All other requests reaching identity ingress return a direct localized branded
`404` response. The response does not redirect and does not disclose commerce
route existence.

## Rollback

Rollback is code-only:

1. Remove the `IdentityIngressAllowlistSubscriber` service registration.
2. Remove `IdentityIngressAllowlistSubscriber.php`.
3. Clear the Shopware cache.
4. Re-run the governance suite and baseline route probes.

No database rollback is required for WP-02.
