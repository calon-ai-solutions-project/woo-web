<?php
/**
 * Site details the owner needs to fill in.
 *
 * Values in [SQUARE BRACKETS] are placeholders. Replace them here and they
 * update everywhere the theme prints them.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Company details shown in the footer.
 *
 * @return array<string,string>
 */
function clo_company_details() {
	return apply_filters(
		'clo_company_details',
		array(
			'name'    => '[COMPANY NAME]',
			'number'  => '[COMPANY NUMBER]',
			'address' => '[REGISTERED ADDRESS]',
			'email'   => '[CONTACT EMAIL]',
		)
	);
}

/**
 * Where wholesale enquiries and contact messages are sent.
 *
 * Until this is a real address, messages go to the site admin email
 * (Settings > General).
 *
 * @return string
 */
function clo_enquiry_inbox() {
	return apply_filters( 'clo_enquiry_inbox', '[SHARED INBOX]' );
}

/**
 * How soon a wholesale enquiry gets an answer, as shown on the site.
 * For example "one working day".
 *
 * @return string
 */
function clo_response_time() {
	return apply_filters( 'clo_response_time', '[RESPONSE TIME]' );
}

/**
 * UK delivery prices in pounds, for example '4.99'.
 *
 * Used once, by scripts/setup.sh, when it creates the UK delivery method.
 * While a price is still [PRICE], setup leaves delivery unset rather than
 * go live with a made-up rate. After that, change prices in
 * WooCommerce > Settings > Shipping. Pallet delivery is quoted after the
 * order, so it has no price here.
 *
 * @return array<string,string>
 */
function clo_delivery_rates() {
	return apply_filters(
		'clo_delivery_rates',
		array(
			'standard-parcel' => '[PRICE]',
			'large-parcel'    => '[PRICE]',
		)
	);
}

/**
 * Whether pay-later messaging (Klarna, Clearpay, PayPal Pay in 3) may be shown.
 *
 * Off until the owner confirms eligibility with each provider. Turn it on by
 * adding this line to wp-config.php:
 *
 *     define( 'CLO_SHOW_PAY_LATER', true );
 *
 * @return bool
 */
function clo_show_pay_later() {
	return (bool) apply_filters( 'clo_show_pay_later', defined( 'CLO_SHOW_PAY_LATER' ) && CLO_SHOW_PAY_LATER );
}

/**
 * Messages for the announcement bar above the header.
 *
 * @return string[]
 */
function clo_announcements() {
	$messages = array( 'UK delivery on every order' );
	if ( clo_show_pay_later() ) {
		$messages[] = 'Pay in 3 with Klarna, Clearpay or PayPal';
	}
	$messages[] = 'New stock every week';

	return apply_filters( 'clo_announcements', $messages );
}

/**
 * Payment methods listed in the footer.
 *
 * Shown as text labels. Swap in the providers' official logo files once the
 * accounts are connected; do not redraw their marks.
 *
 * @return string[]
 */
function clo_payment_methods() {
	$methods = array( 'Visa', 'Mastercard', 'Apple Pay', 'Google Pay', 'PayPal', 'Bank transfer' );
	if ( clo_show_pay_later() ) {
		$methods[] = 'Klarna';
		$methods[] = 'Clearpay';
	}

	return apply_filters( 'clo_payment_methods', $methods );
}
