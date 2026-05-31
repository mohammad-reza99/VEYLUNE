# WP-01 Channel Topology Foundation

## Scope

WP-01 creates inactive, domainless foundations for future Identity, Acquisition,
and Private Commerce channels. It does not activate those channels, assign
domains, change the existing storefront, modify the existing Headless channel,
or change sitemap ownership.

The current `VEYLUNE STUDIO` storefront remains the active public-behavior
baseline until later work packages are authorized.

## Foundations

| Foundation | State | Domain assignment | Navigation root |
| --- | --- | --- | --- |
| `VEYLUNE Identity Foundation` | inactive | none | dedicated inactive root |
| `VEYLUNE Acquisition Foundation` | inactive | none | dedicated inactive root |
| `VEYLUNE Private Commerce Foundation` | inactive | none | dedicated inactive root |

Each foundation receives a distinct generated sales-channel access key. The
existing Headless channel remains unchanged during WP-01.

## Rollback

Rollback is safe before later work packages use these foundations:

1. Verify the three foundation channels remain inactive and domainless.
2. Delete the three foundation sales channels by their IDs.
3. Delete the three inactive foundation root categories by their IDs.
4. Leave the existing `VEYLUNE STUDIO` and Headless channels unchanged.

Foundation channel IDs:

```text
019e9e8f000070008000000000000001
019e9e8f000070008000000000000002
019e9e8f000070008000000000000003
```

Foundation root category IDs:

```text
019e9e8f000070008000000000000011
019e9e8f000070008000000000000012
019e9e8f000070008000000000000013
```

Rollback must be executed as an explicitly approved operational action. The
migration intentionally has no automatic destructive rollback.
