<?php
/**
 * CL Outlet child theme for Kadence.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

define( 'CLO_VERSION', '0.2.0' );
define( 'CLO_DIR', get_stylesheet_directory() );
define( 'CLO_URI', get_stylesheet_directory_uri() );

require CLO_DIR . '/inc/site-config.php';
require CLO_DIR . '/inc/brand.php';
require CLO_DIR . '/inc/setup.php';
require CLO_DIR . '/inc/template-tags.php';
require CLO_DIR . '/inc/woocommerce.php';
require CLO_DIR . '/inc/home.php';
