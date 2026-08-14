# Phase 2.1: Header Precision Pass

## Corrections

- Moved the utility row out of the main header grid and verified it as a direct header child.
- Replaced the full-purple announcement treatment with a white service row and a focused benefit chip.
- Increased desktop search dominance while retaining a compact mobile search.
- Added a dense, horizontally contained department rail.
- Kept unavailable departments non-interactive and explicitly disabled so catalog governance is not bypassed.
- Corrected a legacy grid-column override that caused desktop horizontal overflow.

## Verified geometry

- Desktop search: 769 by 50 pixels at the QA viewport.
- Desktop category rail: 12 items inside a 1145-pixel viewport with internal horizontal scrolling.
- Mobile search: 303 by 50 pixels at a 390-pixel viewport.
- No document-level horizontal overflow on desktop or mobile.

## Status

Twig lint, theme compilation, browser QA, and the complete governance suite pass.
