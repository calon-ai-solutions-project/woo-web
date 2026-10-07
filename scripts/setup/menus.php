<?php
/**
 * Setup step: the main menu and the two footer menus.
 *
 * A menu that already exists is kept as it is, so changes made in
 * Appearance > Menus survive. Pass --reset-menus to rebuild all three.
 *
 * @package clo-setup
 */

require_once __DIR__ . '/lib.php';

WP_CLI::log( 'Menus' );

$clo_reset     = clo_setup_flag( isset( $args ) ? $args : array(), 'reset-menus' );
$clo_catalogue = require __DIR__ . '/data/catalogue.php';

/**
 * A menu item pointing at a page this script created.
 *
 * @param string $key     Page key from pages.php.
 * @param string $title   Link text. Empty to use the page title.
 * @param string $classes CSS classes.
 * @return array<string,mixed>|null
 */
function clo_menu_page( $key, $title = '', $classes = '' ) {
	$id = clo_setup_page_id( $key );
	if ( ! $id ) {
		WP_CLI::warning( sprintf( 'Menu: page "%s" not found. Run the pages step first.', $key ) );
		return null;
	}

	return array(
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $id,
		'menu-item-type'      => 'post_type',
		'menu-item-title'     => $title,
		'menu-item-classes'   => $classes,
		'menu-item-status'    => 'publish',
	);
}

/**
 * A menu item pointing at a product category.
 *
 * @param string $slug Category slug.
 * @return array<string,mixed>|null
 */
function clo_menu_category( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term ) {
		WP_CLI::warning( sprintf( 'Menu: category "%s" not found. Run the catalogue step first.', $slug ) );
		return null;
	}

	return array(
		'menu-item-object'    => 'product_cat',
		'menu-item-object-id' => $term->term_id,
		'menu-item-type'      => 'taxonomy',
		'menu-item-title'     => '',
		'menu-item-status'    => 'publish',
	);
}

/**
 * Create a menu, fill it, and assign it to a theme location.
 *
 * @param string                         $name     Menu name.
 * @param string                         $location Theme location.
 * @param array<int,array<string,mixed>> $items    Items; an item may have 'children'.
 * @param bool                           $reset    Delete and rebuild an existing menu.
 */
function clo_setup_menu( $name, $location, $items, $reset ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu && $reset ) {
		wp_delete_nav_menu( $menu->term_id );
		$menu = false;
		clo_setup_log( sprintf( 'Rebuilding menu: %s', $name ) );
	}

	if ( $menu ) {
		clo_setup_log( sprintf( 'Kept menu: %s', $name ) );
		$menu_id = (int) $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			WP_CLI::warning( sprintf( 'Could not create menu %s: %s', $name, $menu_id->get_error_message() ) );
			return;
		}
		$position = 1;
		foreach ( $items as $item ) {
			if ( ! $item ) {
				continue;
			}
			$children = isset( $item['children'] ) ? $item['children'] : array();
			unset( $item['children'] );
			$item['menu-item-position'] = $position++;
			$parent_id                  = wp_update_nav_menu_item( $menu_id, 0, $item );
			foreach ( $children as $child ) {
				if ( ! $child ) {
					continue;
				}
				$child['menu-item-parent-id'] = $parent_id;
				$child['menu-item-position']  = $position++;
				wp_update_nav_menu_item( $menu_id, 0, $child );
			}
		}
		clo_setup_log( sprintf( 'Created menu: %s', $name ) );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! isset( $locations[ $location ] ) || (int) $locations[ $location ] !== $menu_id ) {
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		clo_setup_log( sprintf( 'Assigned %s to the %s location', $name, $location ) );
	}
}

// Shop drop-down: the 13 shopping categories (not the wholesale one, which has its own menu link).
$clo_shop_children = array();
foreach ( $clo_catalogue['categories'] as $clo_category ) {
	if ( empty( $clo_category['children'] ) ) {
		$clo_shop_children[] = clo_menu_category( $clo_category['slug'] );
	}
}
$clo_shop             = clo_menu_page( 'shop' );
$clo_shop['children'] = $clo_shop_children;

clo_setup_menu(
	'Main menu',
	'primary',
	array(
		$clo_shop,
		clo_menu_page( 'new-stock' ),
		clo_menu_page( 'clearance' ),
		clo_menu_page( 'wholesale' ),
		clo_menu_page( 'wholesale-enquiries', '', 'clo-menu-button' ),
		clo_menu_page( 'contact' ),
	),
	$clo_reset
);

clo_setup_menu(
	'Footer: shop',
	'clo-footer-shop',
	array(
		clo_menu_page( 'shop', 'Shop all' ),
		clo_menu_page( 'new-stock' ),
		clo_menu_page( 'clearance' ),
		clo_menu_page( 'wholesale' ),
		clo_menu_category( 'pallets-lots' ),
		clo_menu_page( 'guides' ),
	),
	$clo_reset
);

clo_setup_menu(
	'Footer: help and policies',
	'clo-footer-policies',
	array(
		clo_menu_page( 'contact' ),
		clo_menu_page( 'wholesale-enquiries' ),
		clo_menu_page( 'delivery' ),
		clo_menu_page( 'returns' ),
		clo_menu_page( 'terms' ),
		clo_menu_page( 'privacy' ),
		clo_menu_page( 'cookies' ),
		clo_menu_page( 'account' ),
	),
	$clo_reset
);

WP_CLI::success( 'Menus done.' );
