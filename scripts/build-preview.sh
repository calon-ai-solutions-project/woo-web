#!/usr/bin/env bash
#
# Build a static, look-only preview of the local site into preview/, for
# showing people the design before the shop is live. vercel.json publishes
# that folder. Shopping, the basket and forms do not work in the preview.
#
# Needs the local site running (./scripts/local-up.sh) and wget
# (on a Mac: brew install wget).
#
# Run from anywhere:  ./scripts/build-preview.sh

set -euo pipefail
cd "$(dirname "$0")/.."

site="${SITE_URL:-http://localhost:8080}"
command -v wget >/dev/null 2>&1 || { echo "wget is not installed (on a Mac: brew install wget)." >&2; exit 1; }
curl -fsS -o /dev/null "$site/" || { echo "The local site is not running at $site. Start it with ./scripts/local-up.sh" >&2; exit 1; }

rm -rf preview
mkdir preview
# wget exits non-zero when any single link fails, so the homepage check below decides success.
wget -q -e robots=off --mirror --page-requisites --adjust-extension --convert-links \
	--no-host-directories --directory-prefix=preview \
	--reject-regex '(wp-admin|wp-login|xmlrpc|wp-json|/feed/|\?add-to-cart|\?s=|\?replytocom|oembed|wp-cron|\?orderby|\?filter_|\?sent=|product-page=)' \
	"$site/" || true
[ -f preview/index.html ] || { echo "The mirror did not produce a homepage." >&2; exit 1; }

python3 -I scripts/build-preview.py preview "$site"
echo "Preview built: $(find preview -name index.html | wc -l | tr -d ' ') pages in preview/"
