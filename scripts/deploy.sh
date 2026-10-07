#!/usr/bin/env bash
#
# Deploy the store to a WordPress host over SSH, for example Hostinger.
#
# Copies the child theme, the store plugin and the setup scripts to the
# server, then runs scripts/setup.sh there to build or update the store.
# Safe to run again after every change.
#
# Usage, from the repo folder on your computer:
#
#   ./scripts/deploy.sh [-p PORT] USER@HOST WORDPRESS_FOLDER [setup.sh options]
#
# Hostinger example (SSH details are in hPanel > Advanced > SSH Access):
#
#   ./scripts/deploy.sh -p 65002 u123456789@1.2.3.4 \
#     domains/clearanceliquidationoutlet.co.uk/public_html
#
# WORDPRESS_FOLDER is the folder holding wp-config.php, relative to your home
# folder on the server. Options after it go to setup.sh; --production is
# always added. Use an SSH key (see docs/DEPLOY.md) so you are not asked for
# the password at every step.

set -euo pipefail
cd "$(dirname "$0")/.."

port=22
if [ "${1:-}" = "-p" ]; then
	port="${2:?Missing port after -p}"
	shift 2
fi
target="${1:-}"
wp_path="${2:-}"
if [ -z "$target" ] || [ -z "$wp_path" ]; then
	sed -n '2,22p' "$0"
	exit 1
fi
shift 2

# Keep macOS from adding ._ resource files and .DS_Store to the uploads.
export COPYFILE_DISABLE=1

remote() {
	ssh -p "$port" -o ServerAliveInterval=30 "$target" "$@"
}

# Upload a local folder to a folder on the server, replacing what was there.
# The new copy is unpacked beside the old one and swapped in, so the site
# never sees a half-uploaded folder.
upload() {
	local from="$1" to="$2"
	tar -czf - --exclude .DS_Store -C "$(dirname "$from")" "$(basename "$from")" | remote "
		set -e
		mkdir -p '$to.new'
		tar -xzf - -C '$to.new' --strip-components=1
		if [ -d '$to' ]; then mv '$to' '$to.old'; fi
		mv '$to.new' '$to'
		rm -rf '$to.old'
	"
}

echo "Checking the server..."
remote "command -v wp >/dev/null" || {
	echo "WP-CLI (the wp command) was not found on the server. Ask your host to enable it." >&2
	exit 1
}
remote "wp --path='$wp_path' core is-installed" || {
	echo "No WordPress install found in $wp_path on the server. Install WordPress there first, or check the folder name." >&2
	exit 1
}

echo "Uploading the child theme..."
upload wp-content/themes/clo-child "$wp_path/wp-content/themes/clo-child"

echo "Uploading the store plugin..."
remote "mkdir -p '$wp_path/wp-content/mu-plugins'"
upload wp-content/mu-plugins/clo-store "$wp_path/wp-content/mu-plugins/clo-store"
remote "cat > '$wp_path/wp-content/mu-plugins/clo-store.php'" < wp-content/mu-plugins/clo-store.php

echo "Uploading the setup scripts..."
upload scripts clo-setup/scripts

echo "Building the store on the server..."
remote "WP_PATH='$wp_path' bash clo-setup/scripts/setup.sh --production ${*:-}"

echo
echo "Deployed. Open your site and log in to check it."
