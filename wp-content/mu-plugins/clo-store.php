<?php
/**
 * Plugin Name: CL Outlet store
 * Description: Store features for Clearance Liquidation Outlet: tiered quantity pricing, minimum order quantities, pallet delivery, wholesale enquiry and contact forms, and New Stock tag expiry.
 * Version: 0.1.0
 *
 * Must-use plugins load from this folder automatically and cannot be switched
 * off in the dashboard, so these features keep working whatever the theme.
 *
 * @package clo-store
 */

defined( 'ABSPATH' ) || exit;

define( 'CLO_STORE_DIR', __DIR__ . '/clo-store' );

require CLO_STORE_DIR . '/new-stock.php';
require CLO_STORE_DIR . '/wholesale.php';
require CLO_STORE_DIR . '/forms.php';
require CLO_STORE_DIR . '/local-mail.php';
