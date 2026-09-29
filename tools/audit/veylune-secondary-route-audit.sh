#!/usr/bin/env bash
set -euo pipefail

base="https://veylune-shopware.ddev.site"
routes=(
  "/"
  "/catalog"
  "/catalog/search?q=chair"
  "/catalog/category/furniture"
  "/catalog/category/lighting"
  "/catalog/category/decor-objects"
  "/catalog/category/textiles-rugs"
  "/catalog/category/dining-kitchen"
  "/catalog/category/outdoor"
  "/catalog/room/living-room"
  "/catalog/room/dining-room"
  "/catalog/room/bedroom"
  "/catalog/room/workspace"
  "/catalog/room/hallway"
  "/catalog/collection/founder-selection"
  "/catalog/collection/new-arrivals"
  "/categories/outdoor"
  "/categories/bedding-bath"
  "/categories/mattresses"
  "/categories/rugs"
  "/categories/decor-objects"
  "/categories/lighting"
  "/categories/organization"
  "/collections/sale"
  "/collections/new-arrivals"
  "/collections/best-sellers"
  "/editions"
  "/atelier-partnerships"
  "/private-consultation"
  "/trade-program"
  "/contact-studio"
  "/legal/privacy"
  "/legal/imprint"
  "/legal/terms"
  "/legal/payment-delivery"
  "/legal/cancellation"
  "/account"
  "/account/profile"
  "/account/address"
  "/account/order"
  "/checkout/cart"
)

body_file="$(mktemp)"
trap 'rm -f "$body_file"' EXIT
failed=0

for route in "${routes[@]}"; do
  row="$(curl -k -sS -L -o "$body_file" -w '%{http_code}|%{url_effective}' "${base}${route}")"
  code="${row%%|*}"
  effective="${row#*|}"

  if [[ "$code" -ge 400 ]] || grep -qiE '<h1[^>]*>[[:space:]]*Page not found|<title>[^<]*(404|Page not found)' "$body_file"; then
    printf 'FAIL|%s|%s|%s\n' "$route" "$code" "$effective"
    failed=$((failed + 1))
  else
    printf 'PASS|%s|%s|%s\n' "$route" "$code" "$effective"
  fi
done

printf 'TOTAL=%s FAILURES=%s\n' "${#routes[@]}" "$failed"
exit "$failed"
