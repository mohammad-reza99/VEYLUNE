# Veylune Phase 2.1C Topbar and Announcement Hierarchy

Captured: 2026-09-14

Status: complete

Exact next package: 2.1D Primary Header, Search and Utilities

## Outcome

The Veylune topbar now has one SCSS owner, one Twig family, and three visually distinct zones. Eleven later cascade files no longer redefine topbar selectors.

1. Signature zone: Veylune, Rooms, and Material Index.
2. Announcement zone: Private consultation by appointment, linked to the governed consultation route.
3. Service/index zone: Atelier Partnerships, Design Service, and EN / EUR.

The rhythm resolves to 13px/900 for the signature, 12px/700 for the announcement, and 11px/700 for the locale index. The three zones use a balanced `1fr / auto / 1fr` grid instead of visually unrelated left and right clusters.

## Reference comparison

The current Wayfair shell separates service utilities, a prominent shipping message, primary search/actions, category dropdown navigation, and a promotional strip. Veylune now matches the clarity of that hierarchy without copying claims or assets. Because Veylune has no verified shipping, rewards, or financing program, the center zone uses a truthful consultation statement instead.

## Responsive decision

- Desktop: topbar remains 31px high and the complete header remains 190px high.
- Tablet and mobile: topbar remains hidden, preserving the existing 122px and 166.36px shell heights.
- Intermediate desktop: secondary family links and the first partnership link collapse before the three-zone grid can overflow.
- Reduced motion: topbar transitions are removed.

## Validation

- Fifteen public screenshots across five routes and three viewports.
- Fifteen HTTP 200 captures.
- Zero runtime, response, overflow, or interaction issues.
- One topbar SCSS owner.
- Zero raw color literals and zero `!important` declarations in the canonical topbar.
- Active SCSS debt reduced: color literals 1814 to 1796, shadow literals 98 to 97, transition-time literals 81 to 80, and `!important` declarations 130 to 127.

`php bin/veylune-topbar-contract-audit` protects the owner boundary, approved copy, governed route, token consumption, three-part computed type rhythm, responsive visibility, and visual capture matrix.

The next exact implementation package is 2.1D: consolidate the primary header, dominant search, account and cart utilities, click-away behavior, Escape handling, and focus restoration under one header behavior owner.
