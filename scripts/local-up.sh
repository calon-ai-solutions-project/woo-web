#!/usr/bin/env bash
#
# Start the local Docker environment, install WordPress with WP-CLI, then
# build the store with scripts/setup.sh (theme, plugins, settings,
# categories, pages and menus).
#
# Run from anywhere:  ./scripts/local-up.sh
# Safe to run again: every step checks before it acts.
# Options are passed on to setup.sh, for example --reset-menus.

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

echo "Building the store..."
docker compose run --rm -T --entrypoint bash wpcli /scripts/setup.sh "$@"

echo
echo "Ready: $SITE_URL"
echo "Admin: $SITE_URL/wp-admin (user and password are in .env)"
echo "Email: http://localhost:8025 (every email the local site sends)"
