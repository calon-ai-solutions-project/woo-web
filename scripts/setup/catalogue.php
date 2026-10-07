<?php
/**
 * Setup step: product categories, tags, attributes, shipping classes and the UK delivery zone.
 *
 * @package clo-setup
 */

require_once __DIR__ . '/lib.php';

if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$clo_catalogue = require __DIR__ . '/data/catalogue.php';
$clo_seo_text  = is_readable( __DIR__ . '/data/category-text.php' ) ? require __DIR__ . '/data/category-text.php' : array();

/*
 * Categories.
 */
WP_CLI::log( 'Product categories' );

/**
 * Create categories in order, with their SKU code and position.
 *
 * @param array<int,array<string,mixed>> $categories Categories from data/catalogue.php.
 * @param int                            $parent     Parent term ID.
 * @param int                            $position   Running menu order.
 * @param array<string,string>           $text       Category page text keyed by slug.
 * @return int Next menu order.
 */
function clo_setup_categories( $categories, $parent, $position, $text ) {
	foreach ( $categories as $category ) {
		$id = clo_setup_term(
			'product_cat',
			$category['name'],
			$category['slug'],
			array(
				'parent'      => $parent,
				'description' => isset( $text[ $category['slug'] ] ) ? $text[ $category['slug'] ] : '',
			)
		);
		if ( ! $id ) {
			continue;
		}
		update_term_meta( $id, 'clo_sku_code', $category['code'] );
		update_term_meta( $id, 'order', $position++ );
		if ( ! empty( $category['children'] ) ) {
			$position = clo_setup_categories( $category['children'], $id, $position, $text );
		}
	}

	return $position;
}
clo_setup_categories( $clo_catalogue['categories'], 0, 1, $clo_seo_text );

// New products without a category go to Mixed Products, and WooCommerce's
// empty "Uncategorised" category is removed.
$clo_default = get_term_by( 'slug', $clo_catalogue['default_category'], 'product_cat' );
if ( $clo_default ) {
	clo_setup_option( 'default_product_cat', (int) $clo_default->term_id );
	$clo_uncategorised = get_term_by( 'slug', 'uncategorized', 'product_cat' );
	if ( $clo_uncategorised && 0 === (int) $clo_uncategorised->count && (int) $clo_uncategorised->term_id !== (int) $clo_default->term_id ) {
		wp_delete_term( $clo_uncategorised->term_id, 'product_cat' );
		clo_setup_log( 'Removed the empty Uncategorised category' );
	}
}

/*
 * Tags.
 */
WP_CLI::log( 'Product tags' );
foreach ( $clo_catalogue['tags'] as $clo_tag ) {
	clo_setup_term( 'product_tag', $clo_tag['name'], $clo_tag['slug'], array( 'description' => $clo_tag['description'] ) );
}

/*
 * Attributes and their fixed terms.
 */
WP_CLI::log( 'Product attributes' );
foreach ( $clo_catalogue['attributes'] as $clo_attribute ) {
	$clo_attribute_id = wc_attribute_taxonomy_id_by_name( $clo_attribute['slug'] );
	if ( ! $clo_attribute_id ) {
		$clo_attribute_id = wc_create_attribute(
			array(
				'name'         => $clo_attribute['name'],
				'slug'         => $clo_attribute['slug'],
				'type'         => 'select',
				'order_by'     => $clo_attribute['order_by'],
				'has_archives' => false,
			)
		);
		if ( is_wp_error( $clo_attribute_id ) ) {
			WP_CLI::warning( sprintf( 'Could not create attribute %s: %s', $clo_attribute['name'], $clo_attribute_id->get_error_message() ) );
			continue;
		}
		clo_setup_log( sprintf( 'Created attribute: %s', $clo_attribute['name'] ) );
	}

	if ( empty( $clo_attribute['terms'] ) ) {
		continue;
	}

	// A new attribute's taxonomy is only registered on the next page load, so register it for this run.
	$clo_taxonomy = wc_attribute_taxonomy_name( $clo_attribute['slug'] );
	if ( ! taxonomy_exists( $clo_taxonomy ) ) {
		register_taxonomy( $clo_taxonomy, 'product', array( 'hierarchical' => false ) );
	}
	foreach ( $clo_attribute['terms'] as $clo_index => $clo_term ) {
		$clo_term_id = clo_setup_term( $clo_taxonomy, $clo_term['name'], $clo_term['slug'], array( 'description' => $clo_term['description'] ) );
		if ( $clo_term_id ) {
			update_term_meta( $clo_term_id, 'order', $clo_index );
		}
	}
}

/*
 * Shipping classes.
 */
WP_CLI::log( 'Shipping classes' );
$clo_class_ids = array();
foreach ( $clo_catalogue['shipping_classes'] as $clo_class ) {
	$clo_class_ids[ $clo_class['slug'] ] = clo_setup_term( 'product_shipping_class', $clo_class['name'], $clo_class['slug'], array( 'description' => $clo_class['description'] ) );
}

/*
 * UK delivery zone. The flat rate is only added once real prices are set in the
 * child theme's inc/site-config.php, and never changed after that.
 */
WP_CLI::log( 'UK delivery zone' );
$clo_zone = null;
foreach ( WC_Shipping_Zones::get_zones() as $clo_zone_data ) {
	if ( 'United Kingdom' === $clo_zone_data['zone_name'] ) {
		$clo_zone = new WC_Shipping_Zone( $clo_zone_data['id'] );
		break;
	}
}
if ( ! $clo_zone ) {
	$clo_zone = new WC_Shipping_Zone();
	$clo_zone->set_zone_name( 'United Kingdom' );
	$clo_zone->set_zone_order( 0 );
	$clo_zone->add_location( 'GB', 'country' );
	$clo_zone->save();
	clo_setup_log( 'Created delivery zone: United Kingdom' );
}

$clo_has_flat_rate = false;
foreach ( $clo_zone->get_shipping_methods() as $clo_method ) {
	if ( 'flat_rate' === $clo_method->id ) {
		$clo_has_flat_rate = true;
	}
}

$clo_rates = function_exists( 'clo_delivery_rates' ) ? clo_delivery_rates() : array();
$clo_ready = isset( $clo_rates['standard-parcel'], $clo_rates['large-parcel'] )
	&& is_numeric( $clo_rates['standard-parcel'] )
	&& is_numeric( $clo_rates['large-parcel'] );

if ( $clo_has_flat_rate ) {
	clo_setup_log( 'Kept the existing UK delivery method' );
} elseif ( ! $clo_ready ) {
	WP_CLI::warning( 'No UK delivery method yet: set the delivery prices in wp-content/themes/clo-child/inc/site-config.php and run setup again.' );
} else {
	$clo_instance_id = $clo_zone->add_shipping_method( 'flat_rate' );
	$clo_settings    = array(
		'title'         => 'UK delivery',
		'tax_status'    => 'taxable',
		// WooCommerce adds the base cost to the class cost, so the price sits on the classes only.
		'cost'          => '',
		'no_class_cost' => wc_format_decimal( $clo_rates['standard-parcel'] ),
		// Per order: charge once, at the price of the largest item in the basket.
		'type'          => 'order',
	);
	if ( ! empty( $clo_class_ids['standard-parcel'] ) ) {
		$clo_settings[ 'class_cost_' . $clo_class_ids['standard-parcel'] ] = wc_format_decimal( $clo_rates['standard-parcel'] );
	}
	if ( ! empty( $clo_class_ids['large-parcel'] ) ) {
		$clo_settings[ 'class_cost_' . $clo_class_ids['large-parcel'] ] = wc_format_decimal( $clo_rates['large-parcel'] );
	}
	if ( ! empty( $clo_class_ids['pallet'] ) ) {
		// Pallet delivery is quoted and invoiced after the order.
		$clo_settings[ 'class_cost_' . $clo_class_ids['pallet'] ] = '0';
	}
	update_option( 'woocommerce_flat_rate_' . $clo_instance_id . '_settings', $clo_settings );
	clo_setup_log( 'Added UK delivery (flat rate) to the United Kingdom zone' );
}

WP_CLI::success( 'Catalogue done.' );
