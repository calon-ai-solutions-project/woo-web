#!/usr/bin/env bash
#
# Phase 2: build the store on a WordPress install with WP-CLI.
#
# Installs the theme and plugins, then applies the store settings, product
# categories, attributes, tags, shipping classes, pages and menus. Safe to run
# again: every step checks before it acts, and pages or menus edited in the
# dashboard are kept.
#
# Locally, scripts/local-up.sh runs this for you inside the WP-CLI container.
# On a server, run it from the repo folder with WP-CLI installed:
#
#   WP_PATH=/path/to/wordpress bash scripts/setup.sh --production
#
# Options:
#   --production   Also install the backup and security plugins (UpdraftPlus, Wordfence).
#   --offline      Skip downloads from wordpress.org (themes, plugins, translations).
#                  Only plugins that are already installed are activated.
#   --reset-menus  Delete and rebuild the main and footer menus.

set -euo pipefail

STEPS="$(cd "$(dirname "$0")" && pwd)/setup"

production=false
offline=false
step_args=()
for arg in "$@"; do
	case "$arg" in
		--production) production=true ;;
		--offline) offline=true ;;
		--reset-menus) step_args+=("reset-menus") ;;
		-h | --help)
			sed -n '2,20p' "$0"
			exit 0
			;;
		*)
			echo "Unknown option: $arg (see --help)" >&2
			exit 1
			;;
	esac
done

wp() {
	command wp ${WP_PATH:+--path="$WP_PATH"} "$@"
}

# Plugins from CLAUDE.md, by wordpress.org slug.
# Not installed here: woocommerce-wholesale-prices (trade accounts come later),
# fluentform (the enquiry and contact forms are built into the site, see
# wp-content/mu-plugins/clo-store/forms.php) and wp-lister-for-ebay (optional, later).
plugins=(
	woocommerce
	woocommerce-gateway-stripe
	woocommerce-paypal-payments
	seo-by-rank-math
	google-listings-and-ads
	complianz-gdpr
	webp-converter-for-media
	mailpoet
)
if [ "$production" = true ]; then
	plugins+=(updraftplus wordfence)
fi

if ! wp core is-installed >/dev/null 2>&1; then
	echo "WordPress is not installed here. Install it first (locally: ./scripts/local-up.sh)." >&2
	exit 1
fi

echo "Language: English (UK)"
if [ "$offline" = true ]; then
	echo "  Skipped (offline)"
else
	wp language core is-installed en_GB || wp language core install en_GB
	if [ "$(wp option get WPLANG)" != "en_GB" ]; then
		wp site switch-language en_GB
	fi
fi

echo "Theme"
if ! wp theme is-installed kadence; then
	if [ "$offline" = true ]; then
		echo "The Kadence theme is not installed and --offline was given." >&2
		exit 1
	fi
	wp theme install kadence
fi
if ! wp theme is-installed clo-child; then
	echo "The child theme is missing. Copy wp-content/themes/clo-child from this repo into the site first." >&2
	exit 1
fi
if [ "$(wp option get stylesheet)" != "clo-child" ]; then
	wp theme activate clo-child
fi

echo "Plugins"
for plugin in "${plugins[@]}"; do
	if ! wp plugin is-installed "$plugin"; then
		if [ "$offline" = true ]; then
			echo "  Not installed (offline): $plugin"
			continue
		fi
		wp plugin install "$plugin"
	fi
	if ! wp plugin is-active "$plugin"; then
		wp plugin activate "$plugin"
	fi
done
if ! wp plugin is-active woocommerce; then
	echo "WooCommerce is not active, so the store cannot be built." >&2
	exit 1
fi

if [ "$offline" != true ]; then
	echo "Translations for the theme and plugins"
	wp language theme install --all en_GB >/dev/null || echo "  Some theme translations could not be downloaded"
	wp language plugin install --all en_GB >/dev/null || echo "  Some plugin translations could not be downloaded"
fi

echo "Permalinks"
if [ "$(wp option get permalink_structure)" != "/%postname%/" ]; then
	wp rewrite structure '/%postname%/'
fi

for step in store catalogue pages menus; do
	wp eval-file "$STEPS/$step.php" --use-include ${step_args[@]+"${step_args[@]}"}
done

wp rewrite flush >/dev/null

cat <<'EOF'

Store built. Still needed from the owner (see docs/DEPLOY.md):
  - Company details, contact inbox and delivery prices in wp-content/themes/clo-child/inc/site-config.php
  - Bank details for bank transfer: WooCommerce > Settings > Payments > Direct bank transfer
  - Connect Stripe and PayPal (both stay in test mode until you switch them to live)
  - VAT: decide on tax settings in WooCommerce > Settings > Tax
EOF
