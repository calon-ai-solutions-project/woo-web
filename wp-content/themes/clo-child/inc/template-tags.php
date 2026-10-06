<?php
/**
 * Template tags used by the header and footer.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the logo, linked to Home.
 *
 * @param string $variant 'reversed' for navy backgrounds, 'default' for light ones.
 * @param int    $height  Rendered height in pixels.
 */
function clo_logo( $variant = 'reversed', $height = 72 ) {
	$file = 'reversed' === $variant ? 'logo-reversed.svg' : 'logo.svg';
	// The logo artwork is 426 x 270.
	$width = (int) round( $height * 426 / 270 );
	printf(
		'<a class="clo-logo" href="%1$s" rel="home"><img src="%2$s" alt="%3$s" width="%4$d" height="%5$d" decoding="async"></a>',
		esc_url( home_url( '/' ) ),
		esc_url( CLO_URI . '/assets/img/' . $file ),
		esc_attr( get_bloginfo( 'name' ) ),
		(int) $width,
		(int) $height
	);
}

/**
 * Print the header search form. Searches products when WooCommerce is active.
 */
function clo_search_form() {
	$is_shop = function_exists( 'WC' );
	$label   = $is_shop ? 'Search products' : 'Search';
	?>
	<form class="clo-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="clo-search-field"><?php echo esc_html( $label ); ?></label>
		<input class="clo-search__field" id="clo-search-field" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $label ); ?>" autocomplete="off">
		<?php if ( $is_shop ) : ?>
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>
		<button class="clo-search__submit" type="submit">
			<?php clo_icon( 'search' ); ?>
			<span class="clo-search__submit-label">Search</span>
		</button>
	</form>
	<?php
}

/**
 * The basket count bubble. Also returned as a WooCommerce cart fragment.
 *
 * @return string
 */
function clo_basket_count_html() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;

	return sprintf(
		'<span class="clo-basket__count" data-count="%1$d"><span class="screen-reader-text">Items in basket: </span>%1$d</span>',
		$count
	);
}

/**
 * Print the account and basket links. Nothing is printed without WooCommerce.
 */
function clo_account_and_basket() {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	?>
	<a class="clo-action clo-account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
		<?php clo_icon( 'account' ); ?>
		<span class="clo-action__label">Account</span>
	</a>
	<a class="clo-action clo-basket" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<?php clo_icon( 'basket' ); ?>
		<span class="clo-action__label">Basket</span>
		<?php echo clo_basket_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
	<?php
}

/**
 * Print an inline icon. Icons are decorative; the text label carries the meaning.
 *
 * @param string $name search, account, basket, menu or close.
 */
function clo_icon( $name ) {
	$paths = array(
		'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>',
		'account' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'basket'  => '<path d="M3 9h18l-1.6 10.2a2 2 0 0 1-2 1.8H6.6a2 2 0 0 1-2-1.8L3 9Z"/><path d="m8 9 4-6 4 6"/>',
		'menu'    => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'   => '<path d="M6 6l12 12M18 6 6 18"/>',
		'chevron' => '<path d="m6 9 6 6 6-6"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return;
	}
	printf(
		'<svg class="clo-icon clo-icon--%1$s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		esc_attr( $name ),
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Fallback for the main menu before one is assigned: a single Shop link.
 */
function clo_primary_menu_fallback() {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	printf(
		'<ul id="clo-primary-menu" class="clo-menu"><li class="menu-item"><a href="%s">Shop</a></li></ul>',
		esc_url( wc_get_page_permalink( 'shop' ) )
	);
}

/**
 * Print a footer link column if a menu is assigned to the location.
 *
 * @param string $location Menu location.
 * @param string $heading  Column heading.
 */
function clo_footer_menu( $location, $heading ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	$id = 'clo-footer-heading-' . sanitize_html_class( $location );
	?>
	<nav class="clo-footer__col" aria-labelledby="<?php echo esc_attr( $id ); ?>">
		<h2 class="clo-footer__heading" id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $heading ); ?></h2>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'clo-footer__links',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		?>
	</nav>
	<?php
}
