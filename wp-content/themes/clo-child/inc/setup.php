<?php
/**
 * Theme setup: assets, fonts, menus, site icon.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Self-hosted font files, in the order they should be preloaded.
 *
 * @return array<int,array{family:string,weight:int,file:string,preload:bool}>
 */
function clo_font_files() {
	return array(
		array(
			'family'  => 'Barlow Condensed',
			'weight'  => 500,
			'file'    => 'barlow-condensed-latin-500-normal.woff2',
			'preload' => true,
		),
		array(
			'family'  => 'Barlow Condensed',
			'weight'  => 600,
			'file'    => 'barlow-condensed-latin-600-normal.woff2',
			'preload' => true,
		),
		array(
			'family'  => 'Anton',
			'weight'  => 400,
			'file'    => 'anton-latin-400-normal.woff2',
			'preload' => true,
		),
	);
}

/**
 * The @font-face rules for the self-hosted fonts.
 *
 * @return string
 */
function clo_font_face_css() {
	$css = '';
	foreach ( clo_font_files() as $font ) {
		$css .= sprintf(
			'@font-face{font-family:"%1$s";font-style:normal;font-weight:%2$d;font-display:swap;src:url("%3$s") format("woff2");}',
			$font['family'],
			$font['weight'],
			esc_url_raw( CLO_URI . '/assets/fonts/' . $font['file'] )
		);
	}

	return $css;
}

// Fonts load on the front end and inside the block editor, so patterns look the same in both.
add_action(
	'enqueue_block_assets',
	function () {
		wp_register_style( 'clo-fonts', false, array(), CLO_VERSION );
		wp_add_inline_style( 'clo-fonts', clo_font_face_css() );
		wp_enqueue_style( 'clo-fonts' );
	}
);

add_action(
	'wp_head',
	function () {
		foreach ( clo_font_files() as $font ) {
			if ( $font['preload'] ) {
				printf(
					'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( CLO_URI . '/assets/fonts/' . $font['file'] )
				);
			}
		}
		// The round CLO sticker stands in as the site icon until one is set in Settings > General.
		if ( ! has_site_icon() ) {
			printf(
				'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
				esc_url( CLO_URI . '/assets/img/logo-icon.svg' )
			);
		}
	},
	1
);

add_action(
	'wp_enqueue_scripts',
	function () {
		// The child theme has its own header and footer markup, so Kadence's builder styles are not needed.
		wp_deregister_style( 'kadence-header' );
		wp_deregister_style( 'kadence-footer' );

		wp_enqueue_style( 'clo-child', get_stylesheet_uri(), array( 'kadence-global' ), CLO_VERSION );
		wp_enqueue_script(
			'clo-header',
			CLO_URI . '/assets/js/header.js',
			array(),
			CLO_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);

		// Keeps the basket count in the header correct on cached pages and after add to basket.
		if ( function_exists( 'WC' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	},
	20
);

add_action(
	'after_setup_theme',
	function () {
		register_nav_menus(
			array(
				'clo-footer-shop'     => 'Footer: shop links',
				'clo-footer-policies' => 'Footer: help and policy links',
			)
		);
	}
);

/**
 * Style the Wholesale Enquiries menu item as the yellow button.
 *
 * Works for any menu item with the CSS class "clo-menu-button", and also for an
 * item that links to the page with the slug "wholesale-enquiries", so the menu
 * can be rebuilt by hand without losing the button.
 */
add_filter(
	'nav_menu_css_class',
	function ( $classes, $item, $args ) {
		if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $classes;
		}
		if ( in_array( 'clo-menu-button', $classes, true ) ) {
			return $classes;
		}
		if ( 'page' === $item->object && 'wholesale-enquiries' === get_post_field( 'post_name', (int) $item->object_id ) ) {
			$classes[] = 'clo-menu-button';
		}

		return $classes;
	},
	10,
	3
);

/**
 * Refresh the header basket count through WooCommerce cart fragments.
 */
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span.clo-basket__count'] = clo_basket_count_html();

		return $fragments;
	}
);
