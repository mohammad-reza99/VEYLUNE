# Phase 7A: local native commerce validation

## Final closure checkpoint — recorded 2026-09-26

Status: complete for the owner-approved local supplier-demo scope.

The combined 7A.6 and Phase 8 acceptance run passed. Seven anonymous native
surfaces were checked at 390, 768 and 1440 CSS pixels, and populated offcanvas,
cart and checkout states were checked at the same widths. The matrix found no
unexpected status, horizontal overflow, broken loaded image, unnamed visible
control or captured runtime error. Private/no-store and noindex boundaries are
present, and the native adapter uses the governed primary font and action token.

Transaction regression also passed for cart, checkout, variants, account,
password/profile/address lifecycle, repeat, cancel, local refund and the
single-unit concurrency race. The latest verified local refund is order 10016,
refund `01a0dd8e5cd872f085778a0f9b3a0193`.

Evidence lives in `reports/local-commerce/final-signoff/`; the signed decision
is `docs/veylune-supplier-demo-technical-signoff.md`. Every older "still open"
or "no final sign-off" sentence below is retained only as historical checkpoint
context and is superseded by this closure.

Real supplier authority, media rights, live price/stock, external payment
provider certification and production deployment remain excluded owner-managed
work. The local completion does not permit public sales.

## Variant and order-action checkpoint — recorded 2026-09-25

The native scenario suite now verifies two independent variant children on the
isolated channel. Sand is in stock, keeps its selected option in the native cart,
and rejects a forged quantity above available stock by capping it to stock.
Charcoal has zero stock and exposes no enabled buy action. The fixtures do not
modify supplier-candidate products and repeated setup does not reset stock.

The registered-order suite also verifies that native repeat order restores the
original product to the cart and that customer cancellation persists a cancelled
order state. That test exposed a real stacking-context defect: the cancellation
modal was rendered behind Bootstrap's body-level backdrop. The local-commerce
body scope now removes the nested content stacking context while a modal is open,
and the repeated browser test passes at desktop and mobile widths.

Database verification first confirmed order 10014 was cancelled while its
transaction remained paid, proving cancellation was not silently treated as a
refund. The local handler and an explicitly guarded DDEV command now exercise
Shopware's native refund processor. Refund record
`01a0d5acc6d2731eb6392319f8f8c197` completed and order 10014's transaction moved
to refunded. This is a local simulator result, not external-provider certification.

The stock-race test explicitly reset only the independent Sand fixture to one
unit, prepared two isolated carts through native guest checkout, and submitted
both confirmations concurrently. Exactly one reached the finish route; the
other remained on a controlled storefront surface. Database verification shows
order 10015 paid and Sand stock/available stock both zero. The race did not
modify supplier candidates.

Together with the earlier wrong-password, unchecked-terms, forged-quantity and
cross-account read tests, this closes the scoped 7A.5 technical scenarios. Full
visual comparison, wider Phase 8 security review and external-provider sandbox
certification remain separate gates.

Scripts and evidence:

- `tools/audit/veylune-local-variant-smoke.cjs`
- `tools/audit/veylune-local-account-smoke.cjs`
- `tools/audit/veylune-local-order-state.sql`
- `tools/audit/veylune-local-stock-race.cjs`
- `reports/local-commerce/variant-smoke.json`
- `reports/local-commerce/order-cycle.json`
- `reports/local-commerce/stock-race.json`

## Account lifecycle checkpoint — recorded 2026-09-25

The preceding run passed all four checks; its evidence and reusable script are
now recorded in the repository:

- Profile first-name edit persists after reloading.
- Address street edit persists after reloading.
- A second registered customer cannot read the owner's address editor or order
  detail, and the owner's product does not appear in its order history.
- Password recovery email reaches local Mailpit; its reset link stays in the
  isolated channel. New password works, old password fails, and the used reset
  link no longer opens a password-reset form.

Script: tools/audit/veylune-local-account-lifecycle.cjs
Evidence: reports/local-commerce/account-lifecycle.json

Run with Node/Playwright, VEYLUNE_ALLOW_TEST_ORDER=1 and optionally
VEYLUNE_BROWSER_EXECUTABLE / VEYLUNE_TEST_OUTPUT. It creates two test customers
and one simulated order and reads only recovery mail for its generated test
recipient. Passwords and recovery tokens are not included in the evidence.
This checkpoint records the already executed test, not a new run on 2026-09-25.

Scope limit: this is read-access isolation plus successful profile/address edits,
not a complete authorization/security audit. Unauthorized mutation, token expiry,
rate limits and enumeration remain part of the wider security review.
The account lifecycle package is verified; final visual parity and supplier-demo
approval remain open.

## Account verification checkpoint — 2026-09-24

Implemented channel-scoped account typography, surfaces, controls and active
navigation using existing design tokens. Native account registration, login,
profile, addresses and order-history routes are retained. Order-history headings
identify test orders rather than the legacy acquisition archive.

Reproducible test: tools/audit/veylune-local-account-smoke.cjs.
Default execution creates a new local test customer and exercises registration,
empty history, profile/address routes, logout, wrong-password rejection and valid
login. VEYLUNE_ALLOW_TEST_ORDER=1 additionally creates ONE simulated local order,
expands its history details and checks anonymous access requires login.
No real provider is contacted. Random test credentials are held only in memory.
Each run creates test data; it does not reset or delete existing customer/order data.

Latest run passed: registered order 10007 visible with product details and PAID
payment state; 390/1440 account routes without horizontal overflow, including
populated mobile order history. Screenshots were inspected. Evidence is in
reports/local-commerce/account-smoke.json.

Recovery, profile/address edits and cross-customer read isolation were verified
in the later lifecycle checkpoint above. Still open: repeat order/cancel/refund,
variants, wider authorization/security checks, and complete
preview/native visual parity. No final supplier-demo sign-off is claimed.

## Verification checkpoint — 2026-09-23

The interrupted native cart/checkout adapter verification is closed:
hardcoded color additions were replaced by existing governed marketplace tokens,
without raising the token audit ceiling. Theme compilation and full governance
verification passed. Cart/mini-cart smoke passed again after compilation.
Fresh-session guest registration, checkout at 390/768/1440, desktop two-column
layout and rejected unchecked terms all passed. The previous checkout probe
depended on a stale session; its replacement creates its own guest test context.

Repeat with Node/Playwright and optional VEYLUNE_BROWSER_EXECUTABLE:

    node tools/audit/veylune-local-checkout-smoke.cjs

This creates a local guest and cart, then deliberately submits unchecked terms
to verify rejection; it does not submit a valid order. Test email uses
example.invalid. Screenshots and results go to VEYLUNE_TEST_OUTPUT or
var/local-checkout-smoke. It requires the isolated local fixtures and available F01.

This checkpoint is not final preview/native visual parity or supplier-demo
sign-off. Account/history, remaining visual alignment, variants/lifecycle and
broader Phase 8 acceptance remain open.

Date: 2026-09-22
Status: core transaction path verified; broader technical sign-off remains open.

## Scope and isolation

Entry: https://veylune-shopware.ddev.site/__commerce-test/test-products

No preview token is required. This is a separate local DDEV sales channel, not
public commercial publication and not a replacement for private editorial preview.
Ten VLT-TEST-F01 through F10 product fixtures have separate native product IDs,
Admin media associations and prices. F10 is intentionally out of stock.
Supplier candidates and commercial approval gates are unchanged.

Setup: ddev exec bin/console veylune:test-commerce:setup --apply
Without --apply the command prints a plan. Existing fixture stock is not reset.
The pre-setup database snapshot is before-local-commerce-7a.

Test payments are synchronous local simulations, NOT an external payment-provider
sandbox. They never contact a payment provider. Test orders are real database
records, but create no actual purchase or fulfillment. Test email was captured
by local Mailpit; do not run with production transport or production credentials.
Host, development environment and DDEV-project guards restrict the storefront.
Responses carry noindex/noarchive and private/no-store headers.

## Evidence from executed tests

- Order 10002: guest checkout, native order persisted, EUR 4909.90 total,
  payment transaction paid, stock reduced from 20 to 19.
- Order 10003: simulated decline opened native payment recovery with an error;
  choosing success recovered the SAME order. Database retains failed transaction
  history and a paid transaction. Stock is 18, not decremented again by recovery.
- Mailpit captured order confirmation and payment notification for 10002.
- Zero test-product visibility associations outside the isolated test channel.
- Repeatable smoke: ten products, native add, header count, quantity update,
  correct EUR 9809.90 total for two units, remove, empty counter, out-of-stock
  buy prevention, public /test-products denied with 404.
- Cart horizontal overflow checks passed at 390, 768 and 1440 CSS pixels.
- Mobile screenshot exposed a hardcoded zero badge; replaced with native data.
- All 105 Twig files linted, service container linted, theme compiled.
- Full governance verification passed after registering the new scoped CSS owner
  and updating inventory counts (no acceptance checks removed).

Re-run the non-order smoke from the repository root with Node and Playwright:

    node tools/audit/veylune-local-commerce-smoke.cjs

Optional environment settings: CODEX_PLAYWRIGHT_MODULE,
VEYLUNE_BROWSER_EXECUTABLE, VEYLUNE_TEST_OUTPUT (default var/local-commerce-smoke).
The test uses an anonymous cart and does not submit orders. It expects F01 to
have at least two units. Existing manually created orders are not silently reset.

## Still open before technical supplier-demo sign-off

1. Repeat purchase and wider account authorization/security checks; registration,
   login/reset, profile/address edits and order history have passed their scoped tests.
2. Variant fixtures, stock concurrency, cancel/refund lifecycle and invalid input.
3. Full native checkout/mobile visual parity: first integration adapter now uses
   preview color/typography/form/summary values; quantity controls no longer wrap.
   Final side-by-side parity and founder review are still open.
4. Full Phase 8 cross-page interaction, accessibility, security and performance QA.
5. External provider sandbox validation when a provider is selected.

Supplier evidence belongs to Phase 7B and live deployment to Phase 9; neither
blocks continuing local technical tests. Technical demo readiness is distinct
from permission to sell or a production launch decision.

## Preview-to-native integration implementation

- Added a sales-channel-scoped body marker. Public and token-preview styles are
  not changed by the native commerce adapter.
- Cart and checkout use preview-derived purple actions, sans typography, white
  surfaces, summary cards and consistent inputs. Native form actions remain intact.
- Corrected mini-cart checkout: it previously pointed to consultation; now it
  invokes native checkout only for the local test channel.
- Removed legacy checkout wordmark, restored visible remove control and checked
  payment/shipping states; desktop checkout summary now sits beside the form.
- Extended the cart smoke with the mini-cart route assertion. Add/remove,
  quantities/totals, counter and no-overflow checks at 390/768/1440 passed.
- Checkout captures at 390/768/1440 passed overflow checks; desktop two-column
  layout verified. Unchecked terms shows native validation feedback. The validation
  response URL is /account/order, not necessarily /checkout/confirm.
- This is an implementation checkpoint, not supplier-demo or visual exit approval.
  Remaining work includes full preview/native comparisons, wider account security,
  lifecycle/variant tests and integrated Phase 8 QA.
