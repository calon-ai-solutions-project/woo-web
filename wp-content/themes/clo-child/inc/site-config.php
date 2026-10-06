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
