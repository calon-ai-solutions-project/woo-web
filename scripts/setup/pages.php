<?php
/**
 * Setup step: pages, front page, blog page and WooCommerce page roles.
 *
 * Page text lives in scripts/setup/pages/. A page edited in the dashboard is
 * never overwritten; see clo_setup_page() in lib.php.
 *
 * @package clo-setup
 */

require_once __DIR__ . '/lib.php';

if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

WP_CLI::log( 'Pages' );

// Makes any missing WooCommerce page (shop, basket, checkout, account). Existing ones are left alone.
WC_Install::create_pages();

$clo_pages = array(
	array(
		'key'     => 'home',
		'title'   => 'Home',
		'slug'    => 'home',
		'content' => clo_setup_page_content( 'home.html' ),
	),
	array(
		'key'      => 'shop',
		'title'    => 'Shop',
		'slug'     => 'shop',
		'adopt_id' => wc_get_page_id( 'shop' ),
	),
	array(
		'key'     => 'new-stock',
		'title'   => 'New Stock',
		'slug'    => 'new-stock',
		'content' => clo_setup_page_content( 'new-stock.html' ),
	),
	array(
		'key'     => 'clearance',
		'title'   => 'Clearance',
		'slug'    => 'clearance',
		'content' => clo_setup_page_content( 'clearance.html' ),
	),
	array(
		'key'     => 'wholesale',
		'title'   => 'Wholesale & Bulk Buy',
		'slug'    => 'wholesale-bulk-buy',
		'content' => clo_setup_page_content( 'wholesale-bulk-buy.html' ),
	),
	array(
		// The slug matters: the theme styles the menu link to this page as the yellow button.
		'key'     => 'wholesale-enquiries',
		'title'   => 'Wholesale Enquiries',
		'slug'    => 'wholesale-enquiries',
		'content' => clo_setup_page_content( 'wholesale-enquiries.html' ),
	),
	array(
		'key'     => 'contact',
		'title'   => 'Contact',
		'slug'    => 'contact',
		'content' => clo_setup_page_content( 'contact.html' ),
	),
	array(
		'key'     => 'delivery',
		'title'   => 'Delivery Information',
		'slug'    => 'delivery-information',
		'content' => clo_setup_page_content( 'delivery-information.html' ),
	),
	array(
		'key'      => 'returns',
		'title'    => 'Returns & Refunds',
		'slug'     => 'returns-refunds',
		'content'  => clo_setup_page_content( 'returns-refunds.html' ),
		'adopt_id' => (int) get_option( 'woocommerce_refund_returns_page_id' ),
	),
	array(
		'key'     => 'terms',
		'title'   => 'Terms & Conditions',
		'slug'    => 'terms-conditions',
		'content' => clo_setup_page_content( 'terms-conditions.html' ),
	),
	array(
		'key'      => 'privacy',
		'title'    => 'Privacy Policy',
		'slug'     => 'privacy-policy',
		'content'  => clo_setup_page_content( 'privacy-policy.html' ),
		'adopt_id' => (int) get_option( 'wp_page_for_privacy_policy' ),
	),
	array(
		'key'     => 'cookies',
		'title'   => 'Cookie Policy',
		'slug'    => 'cookie-policy',
		'content' => clo_setup_page_content( 'cookie-policy.html' ),
	),
	array(
		'key'   => 'guides',
		'title' => 'Guides',
		'slug'  => 'guides',
	),
	// WooCommerce's own pages keep their content. UK wording for the basket.
	array(
		'key'      => 'basket',
		'title'    => 'Basket',
		'slug'     => 'basket',
		'adopt_id' => wc_get_page_id( 'cart' ),
	),
	array(
		'key'      => 'checkout',
		'title'    => 'Checkout',
		'slug'     => 'checkout',
		'adopt_id' => wc_get_page_id( 'checkout' ),
	),
	array(
		'key'      => 'account',
		'title'    => 'My account',
		'slug'     => 'my-account',
		'adopt_id' => wc_get_page_id( 'myaccount' ),
	),
);

$clo_ids = array();
foreach ( $clo_pages as $clo_page ) {
	$clo_ids[ $clo_page['key'] ] = clo_setup_page( $clo_page );
}

// Kadence page settings for the homepage (the Kadence panel in the page editor): full
// width, no page title and no extra padding, so the pattern bands run edge to edge.
// Set once, so a change made in that panel is kept.
foreach (
	array(
		'_kad_post_layout'           => 'fullwidth',
		'_kad_post_title'            => 'hide',
		'_kad_post_content_style'    => 'unboxed',
		'_kad_post_vertical_padding' => 'hide',
	) as $clo_meta_key => $clo_meta_value
) {
	if ( '' === get_post_meta( $clo_ids['home'], $clo_meta_key, true ) ) {
		update_post_meta( $clo_ids['home'], $clo_meta_key, $clo_meta_value );
	}
}

WP_CLI::log( 'Page roles' );
clo_setup_option( 'show_on_front', 'page' );
clo_setup_option( 'page_on_front', $clo_ids['home'] );
clo_setup_option( 'page_for_posts', $clo_ids['guides'] );
clo_setup_option( 'wp_page_for_privacy_policy', $clo_ids['privacy'] );
clo_setup_option( 'woocommerce_shop_page_id', $clo_ids['shop'] );
clo_setup_option( 'woocommerce_cart_page_id', $clo_ids['basket'] );
clo_setup_option( 'woocommerce_checkout_page_id', $clo_ids['checkout'] );
clo_setup_option( 'woocommerce_myaccount_page_id', $clo_ids['account'] );
// Checkout asks buyers to accept the terms.
clo_setup_option( 'woocommerce_terms_page_id', $clo_ids['terms'] );
clo_setup_option( 'woocommerce_refund_returns_page_id', $clo_ids['returns'] );

// WordPress's sample page and first post, removed only if nobody has edited them.
foreach ( array( array( 'sample-page', 'page' ), array( 'hello-world', 'post' ) ) as $clo_sample ) {
	$clo_post = get_page_by_path( $clo_sample[0], OBJECT, $clo_sample[1] );
	if ( $clo_post && $clo_post->post_modified_gmt === $clo_post->post_date_gmt ) {
		wp_delete_post( $clo_post->ID, true );
		clo_setup_log( sprintf( 'Removed WordPress sample content: %s', $clo_post->post_title ) );
	}
}

WP_CLI::success( 'Pages done.' );
