#!/usr/bin/env bash
set -euo pipefail

base_url="${VEYLUNE_BASE_URL:-https://veylune-shopware.ddev.site}"
preview_token="${VEYLUNE_DRAFT_PREVIEW_TOKEN:-$(ddev exec printenv VEYLUNE_DRAFT_PREVIEW_TOKEN 2>/dev/null | tr -d '\r')}"
work_dir="$(mktemp -d)"
trap 'rm -rf -- "$work_dir"' EXIT

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

request_status() {
    curl -ksS -o "$2" -w '%{http_code}' "$1"
}

catalog_status="$(request_status "$base_url/catalog" "$work_dir/catalog.html")"
[[ "$catalog_status" == "200" ]] || fail "public catalog returned HTTP $catalog_status"
grep -q 'Liora Curved Sofa' "$work_dir/catalog.html" || fail 'public catalog did not render the active F02 fixture'
! grep -q 'Supplier Selection' "$work_dir/catalog.html" || fail 'public catalog leaked draft supplier-selection status'

product_status="$(request_status "$base_url/catalog/product/F02" "$work_dir/product.html")"
[[ "$product_status" == "200" ]] || fail "public product returned HTTP $product_status"
grep -q 'data-add-to-cart="true"' "$work_dir/product.html" || fail 'public product is missing native Shopware add-to-cart markup'
! grep -q 'frontend.veylune.catalog.test_cart.add' "$work_dir/product.html" || fail 'public product still references the retired GET cart shortcut'

product_id="$(grep -o 'lineItems\[[a-f0-9]\{32\}\]\[referencedId\]' "$work_dir/product.html" | head -1 | cut -d '[' -f2 | cut -d ']' -f1)"
[[ "$product_id" =~ ^[a-f0-9]{32}$ ]] || fail 'could not resolve the native Shopware product id'

without_token_status="$(request_status "$base_url/__veylune-preview/catalog/product/F02" "$work_dir/preview-denied.html")"
[[ "$without_token_status" == "404" ]] || fail "preview without token returned HTTP $without_token_status instead of 404"
[[ -n "$preview_token" ]] || fail 'VEYLUNE_DRAFT_PREVIEW_TOKEN is unavailable'

curl -ksS -D "$work_dir/preview.headers" -o "$work_dir/preview.html" \
    "$base_url/__veylune-preview/catalog/product/F02?token=$preview_token"
grep -Eqi '^x-robots-tag:.*noindex' "$work_dir/preview.headers" || fail 'preview is missing the noindex response header'
grep -Eqi '^cache-control:.*no-store' "$work_dir/preview.headers" || fail 'preview is missing the no-store response header'
grep -q 'data-pdp-add-selection' "$work_dir/preview.html" || fail 'preview selection interaction is missing'
! grep -q 'data-add-to-cart="true"' "$work_dir/preview.html" || fail 'preview leaked native add-to-cart markup'

curl -ksS -c "$work_dir/cart.cookies" "$base_url/catalog/product/F02" >/dev/null
cart_status="$(curl -ksS -b "$work_dir/cart.cookies" -c "$work_dir/cart.cookies" -L \
    -o "$work_dir/cart.html" -w '%{http_code}' \
    "$base_url/checkout/line-item/add" \
    --data-urlencode 'redirectTo=frontend.checkout.cart.page' \
    --data-urlencode "lineItems[$product_id][id]=$product_id" \
    --data-urlencode "lineItems[$product_id][type]=product" \
    --data-urlencode "lineItems[$product_id][referencedId]=$product_id" \
    --data-urlencode "lineItems[$product_id][stackable]=1" \
    --data-urlencode "lineItems[$product_id][removable]=1" \
    --data-urlencode "lineItems[$product_id][quantity]=1")"
[[ "$cart_status" == "200" ]] || fail "native add-to-cart journey returned HTTP $cart_status"
grep -q 'Liora Curved Sofa' "$work_dir/cart.html" || fail 'native cart did not contain the selected product'
! grep -q 'Your cart is empty' "$work_dir/cart.html" || fail 'native cart remained empty after add-to-cart'

printf 'PASS: public catalog uses active sales-channel products\n'
printf 'PASS: preview remains token-protected, noindex and no-store\n'
printf 'PASS: public PDP adds the native product to the Shopware cart\n'
