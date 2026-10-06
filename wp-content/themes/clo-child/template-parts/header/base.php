<?php
/**
 * Site header. Replaces Kadence's header builder output.
 *
 * Layout: announcement bar, then logo, search, account and basket on navy,
 * then the main menu on a white strip. Search stays visible at every width.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

$clo_announcements = clo_announcements();
?>
<?php if ( $clo_announcements ) : ?>
	<div class="clo-announce">
		<ul class="clo-announce__list clo-container">
			<?php foreach ( $clo_announcements as $clo_message ) : ?>
				<li><?php echo esc_html( $clo_message ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
<header id="masthead" class="site-header clo-header" role="banner" <?php \Kadence\kadence()->print_microdata( 'header' ); ?>>
	<div class="clo-header__main">
		<div class="clo-header__row clo-container">
			<?php clo_logo( 'reversed', 72 ); ?>
			<?php clo_search_form(); ?>
			<div class="clo-header__actions">
				<?php clo_account_and_basket(); ?>
				<button class="clo-action clo-menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
					<?php clo_icon( 'menu' ); ?>
					<?php clo_icon( 'close' ); ?>
					<span class="clo-action__label">Menu</span>
				</button>
			</div>
		</div>
	</div>
	<nav id="site-navigation" class="clo-nav" aria-label="Main menu">
		<div class="clo-container">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'clo-primary-menu',
					'menu_class'     => 'clo-menu',
					'depth'          => 2,
					'fallback_cb'    => 'clo_primary_menu_fallback',
				)
			);
			?>
		</div>
	</nav>
</header><!-- #masthead -->
