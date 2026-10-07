<?php
/**
 * Setup step: WordPress and WooCommerce settings.
 *
 * Tax is left alone on purpose: VAT registration is an owner decision.
 *
 * @package clo-setup
 */

require_once __DIR__ . '/lib.php';

WP_CLI::log( 'Site and store settings' );

// WordPress.
clo_setup_option( 'blogname', 'Clearance Liquidation Outlet' );
clo_setup_option( 'blogdescription', 'Clearance, liquidation and wholesale stock, one item or by the pallet' );
clo_setup_option( 'timezone_string', 'Europe/London' );
clo_setup_option( 'date_format', 'j F Y' );
clo_setup_option( 'time_format', 'H:i' );
clo_setup_option( 'start_of_week', 1 );
// Guides are articles, not a forum: comments stay off to keep spam out.
clo_setup_option( 'default_comment_status', 'closed' );
clo_setup_option( 'default_ping_status', 'closed' );

// WooCommerce: UK only at launch, GBP, kg and cm.
clo_setup_option( 'woocommerce_default_country', 'GB' );
clo_setup_option( 'woocommerce_allowed_countries', 'specific' );
clo_setup_option( 'woocommerce_specific_allowed_countries', array( 'GB' ) );
clo_setup_option( 'woocommerce_ship_to_countries', '' );
clo_setup_option( 'woocommerce_default_customer_address', 'base' );
clo_setup_option( 'woocommerce_currency', 'GBP' );
clo_setup_option( 'woocommerce_currency_pos', 'left' );
clo_setup_option( 'woocommerce_price_thousand_sep', ',' );
clo_setup_option( 'woocommerce_price_decimal_sep', '.' );
clo_setup_option( 'woocommerce_price_num_decimals', 2 );
clo_setup_option( 'woocommerce_weight_unit', 'kg' );
clo_setup_option( 'woocommerce_dimension_unit', 'cm' );

// Stock: hide sold-out items from the catalogue. Products are never deleted.
clo_setup_option( 'woocommerce_manage_stock', 'yes' );
clo_setup_option( 'woocommerce_hide_out_of_stock_items', 'yes' );

// Accounts: guests can check out; customers can open an account at checkout or on My account.
clo_setup_option( 'woocommerce_enable_guest_checkout', 'yes' );
clo_setup_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
clo_setup_option( 'woocommerce_enable_myaccount_registration', 'yes' );

// Reviews only from verified buyers, so every review on the site is real.
clo_setup_option( 'woocommerce_enable_reviews', 'yes' );
clo_setup_option( 'woocommerce_review_rating_verification_required', 'yes' );

clo_setup_option( 'woocommerce_email_from_name', 'Clearance Liquidation Outlet' );

// Bank transfer (BACS) for trade orders. Set on the first run only, so changes made
// later in WooCommerce > Settings > Payments are kept. The owner adds the account details there.
if ( ! get_option( 'woocommerce_bacs_settings' ) ) {
	update_option(
		'woocommerce_bacs_settings',
		array(
			'enabled'      => 'yes',
			'title'        => 'Bank transfer',
			'description'  => 'Pay by bank transfer. Use your order number as the payment reference. We send your order once the payment has cleared.',
			'instructions' => 'Please use your order number as the payment reference. We send your order once the payment has cleared.',
		)
	);
	clo_setup_log( 'Enabled bank transfer (BACS)' );
}

WP_CLI::success( 'Settings done.' );
