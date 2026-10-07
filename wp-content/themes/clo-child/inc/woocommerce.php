<?php
/**
 * WooCommerce display tweaks: discount badges and empty product lists.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * The discount on a product, in whole percent, rounded down so it is never overstated.
 *
 * For a variable product this is the largest discount across its variations.
 *
 * @param WC_Product $product Product.
 * @return int 0 when the product is not on sale.
 */
function clo_discount_percent( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}

	$pairs = array();
	if ( $product->is_type( 'variable' ) ) {
		$prices = $product->get_variation_prices();
		foreach ( $prices['regular_price'] as $id => $regular ) {
			$pairs[] = array( (float) $regular, (float) $prices['sale_price'][ $id ] );
		}
	} elseif ( '' !== $product->get_regular_price() && '' !== $product->get_sale_price() ) {
		$pairs[] = array( (float) $product->get_regular_price(), (float) $product->get_sale_price() );
	}

	$best = 0;
	foreach ( $pairs as $pair ) {
		list( $regular, $sale ) = $pair;
		if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
			$best = max( $best, (int) floor( ( $regular - $sale ) / $regular * 100 ) );
		}
	}

	return $best;
}

// Sale badge shows the saving, for example "30% off", instead of "Sale!".
add_filter(
	'woocommerce_sale_flash',
	function ( $html, $post, $product ) {
		$percent = clo_discount_percent( $product );
		if ( $percent < 1 ) {
			return $html;
		}
		$label = $product->is_type( 'variable' ) ? sprintf( 'Up to %d%% off', $percent ) : sprintf( '%d%% off', $percent );

		return '<span class="onsale">' . esc_html( $label ) . '</span>';
	},
	10,
	3
);

// A product list with nothing in it says so, instead of showing a blank space.
add_action(
	'woocommerce_shortcode_products_loop_no_results',
	function () {
		echo '<p class="clo-empty">Nothing here right now. New stock arrives every week, so please check back soon.</p>';
	}
);

if ( class_exists( 'WC_Shortcode_Products' ) ) {
	/**
	 * The [products] shortcode, plus a way to ask whether it found anything.
	 */
	class CLO_Products_Query extends WC_Shortcode_Products {
		/**
		 * Whether the query returns at least one product.
		 *
		 * @return bool
		 */
		public function has_products() {
			$results = $this->get_query_results();

			return $results && ! empty( $results->ids );
		}
	}
}
