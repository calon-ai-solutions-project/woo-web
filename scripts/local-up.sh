#!/usr/bin/env bash
#
# Phase 0: start the local Docker environment and install WordPress, the
# Kadence theme, WooCommerce and the CL Outlet child theme with WP-CLI.
#
# Run from anywhere:  ./scripts/local-up.sh
# Safe to run again: every step checks before it acts.
#
# The store itself (settings, categories, pages, menus) is built by
# scripts/setup.sh, which comes in Phase 2.

set -euo pipefail
cd "$(dirname "$0")/.."

if ! command -v docker >/dev/null 2>&1; then
	echo "Docker is not installed. Install Docker Desktop, start it, then run this again." >&2
	exit 1
fi
if ! docker info >/dev/null 2>&1; then
	echo "Docker is installed but not running. Start Docker Desktop, then run this again." >&2
	exit 1
fi
if [ ! -f .env ]; then
	echo "Missing .env. Copy .env.example to .env and set your own passwords." >&2
	exit 1
fi

set -a
# shellcheck disable=SC1091
. ./.env
set +a

wp() {
	docker compose run --rm -T wpcli "$@"
}

echo "Starting the database and WordPress containers..."
docker compose up -d db wordpress

# On first start the WordPress container is still copying its files and the
# database is still initialising, so keep trying until the install goes through.
echo "Installing WordPress (the first run can take a minute)..."
installed=false
last_error=""
for _ in $(seq 1 40); do
	if wp core is-installed >/dev/null 2>&1; then
		installed=true
		break
	fi
	if last_error=$(wp core install \
		--url="$SITE_URL" \
		--title="Clearance Liquidation Outlet" \
		--admin_user="$WP_ADMIN_USER" \
		--admin_password="$WP_ADMIN_PASSWORD" \
		--admin_email="$WP_ADMIN_EMAIL" \
		--locale=en_GB \
		--skip-email 2>&1); then
		installed=true
		break
	fi
	sleep 3
done
if [ "$installed" != true ]; then
	echo "WordPress did not install. Last message from WP-CLI:" >&2
	echo "$last_error" >&2
	exit 1
fi

wp theme is-installed kadence || wp theme install kadence
wp plugin is-installed woocommerce || wp plugin install woocommerce
wp plugin is-active woocommerce || wp plugin activate woocommerce
if [ "$(wp theme list --status=active --field=name)" != "clo-child" ]; then
	wp theme activate clo-child
fi

echo
echo "Ready: $SITE_URL"
echo "Admin: $SITE_URL/wp-admin (user and password are in .env)"
