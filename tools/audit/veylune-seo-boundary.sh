#!/usr/bin/env bash
set -euo pipefail

base_url="${VEYLUNE_BASE_URL:-https://veylune-shopware.ddev.site}"
base_host="${base_url#*://}"
base_host="${base_host%%/*}"
work_dir="$(mktemp -d)"
trap 'rm -rf -- "$work_dir"' EXIT

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

curl -ksS "$base_url/robots.txt" -o "$work_dir/robots.txt"
grep -Fq "Sitemap: $base_url/sitemap.xml" "$work_dir/robots.txt" \
    || fail 'robots.txt does not advertise the canonical sitemap index'
! grep -Fq '/__commerce-test/' "$work_dir/robots.txt" \
    || fail 'robots.txt advertises the isolated commerce-test channel'

while IFS= read -r sitemap_url; do
    sitemap_host="${sitemap_url#*://}"
    sitemap_host="${sitemap_host%%/*}"
    [[ "$sitemap_host" == "$base_host" ]] \
        || fail "robots.txt advertises foreign sitemap host $sitemap_host"
done < <(sed -n 's/^Sitemap:[[:space:]]*//p' "$work_dir/robots.txt")

curl -ksS "$base_url/sitemap.xml" -o "$work_dir/sitemap.xml"
grep -Fq '<sitemapindex' "$work_dir/sitemap.xml" || fail 'sitemap index is missing'
while IFS= read -r location; do
    location_host="${location#*://}"
    location_host="${location_host%%/*}"
    [[ "$location_host" == "$base_host" ]] \
        || fail "sitemap index leaked foreign host $location_host"
done < <(grep -oE '<loc>https?://[^<]+' "$work_dir/sitemap.xml" | sed 's#<loc>##')

printf 'PASS: robots and sitemap are scoped to %s\n' "$base_host"
