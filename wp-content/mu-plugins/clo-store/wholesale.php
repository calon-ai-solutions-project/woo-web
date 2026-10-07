<?php
/**
 * Wholesale buying: tiered quantity pricing, minimum order quantities and
 * pallet delivery.
 *
 * Both are set per product, for simple products, under Product data:
 * - General: "Bulk prices", for example 10 or more at £4.50 each.
 * - Inventory: "Minimum order quantity".
 *
 * They are stored as product meta, so a WooCommerce CSV import can set them:
 * "Meta: _clo_price_tiers" as quantity:price pairs ("10:4.50|50:3.95") and
 * "Meta: _clo_min_qty" as a whole number.
 *
 * @package clo-store
 */

defined( 'ABSPATH' ) || exit;

const CLO_TIERS_META  = '_clo_price_tiers';
const CLO_MIN_QTY_META = '_clo_min_qty';
const CLO_TIER_ROWS   = 4;

/**
 * Parse stored bulk prices.
 *
 * @param string $raw For example "10:4.50|50:3.95".
 * @return array<int,float> Unit price keyed by the quantity it starts at, lowest quantity first.
 */
function clo_parse_tiers( $raw ) {
	$tiers = array();
	foreach ( explode( '|', (string) $raw ) as $pair ) {
		$parts = array_map( 'trim', explode( ':', $pair ) );
		if ( 2 !== count( $parts ) ) {
			continue;
		}
		$qty   = absint( $parts[0] );
		$price = (float) wc_format_decimal( $parts[1] );
		if ( $qty >= 2 && $price > 0 ) {
			$tiers[ $qty ] = $price;
		}
	}
	ksort( $tiers );

	return $tiers;
}

/**
 * Bulk prices for a product. Simple products only.
 *
 * @param WC_Product|false|null $product Product.
 * @return array<int,float>
 */
function clo_price_tiers( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'simple' ) ) {
		return array();
	}

	return clo_parse_tiers( $product->get_meta( CLO_TIERS_META ) );
}

/**
 * Minimum order quantity for a product. 1 means no minimum.
 *
 * @param WC_Product|false|null $product Product.
 * @return int
 */
function clo_min_qty( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'simple' ) ) {
		return 1;
	}

	return max( 1, absint( $product->get_meta( CLO_MIN_QTY_META ) ) );
}

/**
 * Unit price for a quantity. A sale price lower than the bulk price still wins.
 *
 * @param array<int,float> $tiers Bulk prices.
 * @param int              $qty   Quantity.
 * @param float            $base  Normal unit price.
 * @return float
 */
function clo_unit_price( $tiers, $qty, $base ) {
	$price = $base;
	foreach ( $tiers as $from => $tier_price ) {
		if ( $qty >= $from ) {
			$price = min( $base, $tier_price );
		}
	}

	return $price;
}

/*
 * Product editor fields.
 */
add_action(
	'woocommerce_product_options_pricing',
	function () {
		global $product_object;
		$tiers = $product_object instanceof WC_Product ? clo_parse_tiers( $product_object->get_meta( CLO_TIERS_META ) ) : array();
		$rows  = array_slice( array_pad( array_map( null, array_keys( $tiers ), array_values( $tiers ) ), CLO_TIER_ROWS, array( '', '' ) ), 0, CLO_TIER_ROWS );
		?>
		<div class="clo-tiers">
			<p class="form-field">
				<label>Bulk prices</label>
				<span class="description">Lower prices for larger quantities. Example: from 10 at 4.50 each, from 50 at 3.95 each. Leave empty for none.</span>
			</p>
			<?php foreach ( $rows as $i => $row ) : ?>
				<?php list( $qty, $price ) = array_pad( (array) $row, 2, '' ); ?>
				<p class="form-field">
					<label for="clo_tier_qty_<?php echo (int) $i; ?>"><?php echo esc_html( sprintf( 'Bulk price %d', $i + 1 ) ); ?></label>
					<?php // Inline styles undo WooCommerce's half-width floated inputs, so the row reads as a sentence. ?>
					<span style="display:inline-flex;flex-wrap:wrap;align-items:center;gap:0.4em">
						From
						<input type="number" min="2" step="1" style="float:none;width:6em" id="clo_tier_qty_<?php echo (int) $i; ?>" name="clo_tier_qty[]" value="<?php echo esc_attr( $qty ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Bulk price %d: from quantity', $i + 1 ) ); ?>">
						units at <?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
						<input type="text" class="wc_input_price" style="float:none;width:7em" name="clo_tier_price[]" value="<?php echo esc_attr( '' === $price ? '' : wc_format_localized_price( $price ) ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Bulk price %d: price each', $i + 1 ) ); ?>">
						each
					</span>
				</p>
			<?php endforeach; ?>
		</div>
		<?php
	}
);

add_action(
	'woocommerce_product_options_inventory_product_data',
	function () {
		echo '<div class="options_group show_if_simple">';
		woocommerce_wp_text_input(
			array(
				'id'                => CLO_MIN_QTY_META,
				'label'             => 'Minimum order quantity',
				'description'       => 'Buyers must order at least this many. Leave empty for no minimum.',
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			)
		);
		echo '</div>';
	}
);

// WooCommerce has checked the nonce and permissions before this runs.
add_action(
	'woocommerce_admin_process_product_object',
	function ( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['clo_tier_qty'], $_POST['clo_tier_price'] ) ) {
			$quantities = array_map( 'absint', (array) wp_unslash( $_POST['clo_tier_qty'] ) );
			$prices     = array_map( 'wc_clean', (array) wp_unslash( $_POST['clo_tier_price'] ) );
			$pairs      = array();
			foreach ( $quantities as $i => $qty ) {
				$price = isset( $prices[ $i ] ) ? wc_format_decimal( $prices[ $i ] ) : '';
				if ( $qty >= 2 && '' !== $price && (float) $price > 0 ) {
					$pairs[ $qty ] = $qty . ':' . $price;
				}
			}
			ksort( $pairs );
			if ( $pairs ) {
				$product->update_meta_data( CLO_TIERS_META, implode( '|', $pairs ) );
			} else {
				$product->delete_meta_data( CLO_TIERS_META );
			}
		}
		if ( isset( $_POST[ CLO_MIN_QTY_META ] ) ) {
			$min = absint( wp_unslash( $_POST[ CLO_MIN_QTY_META ] ) );
			if ( $min > 1 ) {
				$product->update_meta_data( CLO_MIN_QTY_META, $min );
			} else {
				$product->delete_meta_data( CLO_MIN_QTY_META );
			}
		}
		// phpcs:enable
	}
);

/*
 * Bulk prices in the basket.
 */
add_action(
	'woocommerce_before_calculate_totals',
	function ( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'];
			$tiers   = clo_price_tiers( $product );
			if ( ! $tiers ) {
				continue;
			}
			// Start from a fresh copy, so the price is right however many times totals are worked out.
			$fresh = wc_get_product( $product->get_id() );
			if ( ! $fresh || '' === $fresh->get_price() ) {
				continue;
			}
			$product->set_price( clo_unit_price( $tiers, (int) $item['quantity'], (float) $fresh->get_price() ) );
		}
	},
	20
);

/*
 * Bulk prices and minimum order on the product page.
 */
add_action(
	'woocommerce_single_product_summary',
	function () {
		global $product;
		$tiers = clo_price_tiers( $product );
		$min   = clo_min_qty( $product );
		if ( ! $tiers && $min < 2 ) {
			return;
		}

		echo '<div class="clo-bulk">';
		if ( $tiers && '' !== $product->get_price() ) {
			$base   = (float) $product->get_price();
			$starts = array_merge( array( $min ), array_keys( $tiers ) );
			$starts = array_values( array_unique( array_filter( $starts, fn( $from ) => $from >= $min ) ) );
			sort( $starts );
			echo '<table class="clo-bulk__table"><caption>Bulk prices</caption><thead><tr><th scope="col">Quantity</th><th scope="col">Price each</th></tr></thead><tbody>';
			foreach ( $starts as $i => $from ) {
				$to    = isset( $starts[ $i + 1 ] ) ? $starts[ $i + 1 ] - 1 : 0;
				$range = $to ? ( $to === $from ? (string) $from : $from . ' to ' . $to ) : $from . ' or more';
				$price = wc_get_price_to_display( $product, array( 'price' => clo_unit_price( $tiers, $from, $base ) ) );
				printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $range ), wp_kses_post( wc_price( $price ) ) );
			}
			echo '</tbody></table>';
		}
		if ( $min > 1 ) {
			printf( '<p class="clo-bulk__min">Minimum order: %d units.</p>', (int) $min );
		}
		echo '</div>';
	},
	11
);

// In product lists, mention the lowest bulk price under the normal one.
add_filter(
	'woocommerce_get_price_html',
	function ( $html, $product ) {
		$in_loop_item = did_action( 'woocommerce_before_shop_loop_item' ) > did_action( 'woocommerce_after_shop_loop_item' );
		$tiers        = $in_loop_item ? clo_price_tiers( $product ) : array();
		if ( ! $tiers || '' === $product->get_price() ) {
			return $html;
		}
		$from  = array_key_last( $tiers );
		$price = clo_unit_price( $tiers, $from, (float) $product->get_price() );
		if ( $price >= (float) $product->get_price() ) {
			return $html;
		}

		return $html . sprintf(
			'<span class="clo-bulk-from">%s each for %d or more</span>',
			wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $price ) ) ) ),
			(int) $from
		);
	},
	10,
	2
);

/*
 * Minimum order quantities.
 */
add_filter(
	'woocommerce_quantity_input_args',
	function ( $args, $product ) {
		$min = clo_min_qty( $product );
		if ( $min > 1 ) {
			$args['min_value'] = $min;
			// The block basket asks for the limits without an input value.
			if ( isset( $args['input_value'] ) && (int) $args['input_value'] < $min ) {
				$args['input_value'] = $min;
			}
		}

		return $args;
	},
	10,
	2
);

// "Add to basket" buttons in product lists add the minimum, not one.
add_filter(
	'woocommerce_loop_add_to_cart_args',
	function ( $args, $product ) {
		$min = clo_min_qty( $product );
		if ( $min > 1 ) {
			$args['quantity'] = max( $min, isset( $args['quantity'] ) ? (int) $args['quantity'] : 1 );
		}

		return $args;
	},
	10,
	2
);

// The block basket's quantity picker starts at the minimum.
add_filter(
	'woocommerce_store_api_product_quantity_minimum',
	function ( $value, $product ) {
		return max( (int) $value, clo_min_qty( $product ) );
	},
	10,
	2
);

/**
 * How many of a product are already in the basket.
 *
 * @param int $product_id Product ID.
 * @return int
 */
function clo_qty_in_cart( $product_id ) {
	$qty = 0;
	if ( WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( (int) $item['product_id'] === (int) $product_id ) {
				$qty += (int) $item['quantity'];
			}
		}
	}

	return $qty;
}

add_filter(
	'woocommerce_add_to_cart_validation',
	function ( $passed, $product_id, $quantity ) {
		$product = wc_get_product( $product_id );
		$min     = clo_min_qty( $product );
		if ( $passed && $min > 1 && clo_qty_in_cart( $product_id ) + (int) $quantity < $min ) {
			wc_add_notice(
				sprintf( 'The minimum order for %1$s is %2$d. Please choose a quantity of %2$d or more.', $product->get_name(), $min ),
				'error'
			);
			return false;
		}

		return $passed;
	},
	10,
	3
);

// Checkout is blocked while a basket line is below its minimum.
add_action(
	'woocommerce_check_cart_items',
	function () {
		if ( ! WC()->cart ) {
			return;
		}
		foreach ( WC()->cart->get_cart() as $item ) {
			$min = clo_min_qty( $item['data'] );
			if ( (int) $item['quantity'] < $min ) {
				wc_add_notice(
					sprintf( 'The minimum order for %1$s is %2$d. Please change the quantity in your basket.', $item['data']->get_name(), $min ),
					'error'
				);
			}
		}
	}
);

/*
 * Pallet delivery: quoted after the order.
 */

/**
 * Whether a product goes by pallet (shipping class "Pallet").
 *
 * @param WC_Product|false|null $product Product.
 * @return bool
 */
function clo_is_pallet( $product ) {
	return $product instanceof WC_Product && 'pallet' === $product->get_shipping_class();
}

add_action(
	'woocommerce_single_product_summary',
	function () {
		global $product;
		if ( clo_is_pallet( $product ) ) {
			echo '<p class="clo-pallet-note">This item is delivered by pallet. The delivery cost is quoted after you order: we will email you the price to approve before we send it.</p>';
		}
	},
	25
);

add_filter(
	'woocommerce_get_item_data',
	function ( $data, $item ) {
		if ( isset( $item['data'] ) && clo_is_pallet( $item['data'] ) ) {
			$data[] = array(
				'key'   => 'Delivery',
				'value' => 'Pallet, quoted after your order',
			);
		}

		return $data;
	},
	10,
	2
);

add_filter(
	'woocommerce_package_rates',
	function ( $rates, $package ) {
		$has_pallet = false;
		foreach ( $package['contents'] as $item ) {
			if ( isset( $item['data'] ) && clo_is_pallet( $item['data'] ) ) {
				$has_pallet = true;
				break;
			}
		}
		if ( $has_pallet ) {
			foreach ( $rates as $rate ) {
				$rate->set_label( $rate->get_label() . ' (pallet delivery quoted after your order)' );
			}
		}

		return $rates;
	},
	10,
	2
);
