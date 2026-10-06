<?php
/**
 * Site footer. Replaces Kadence's footer builder output.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

if ( \Kadence\kadence()->has_content() ) {
	\Kadence\kadence()->print_styles( 'kadence-content' );
}

$clo_company  = clo_company_details();
$clo_payments = clo_payment_methods();
?>
<footer id="colophon" class="site-footer clo-footer" role="contentinfo">
	<div class="clo-footer__grid clo-container">
		<div class="clo-footer__col clo-footer__brand">
			<?php clo_logo( 'reversed', 84 ); ?>
			<p>UK online store selling clearance, liquidation, wholesale and retail stock, one item or by the pallet.</p>
		</div>

		<?php clo_footer_menu( 'clo-footer-shop', 'Shop' ); ?>
		<?php clo_footer_menu( 'clo-footer-policies', 'Help and policies' ); ?>

		<div class="clo-footer__col clo-footer__company">
			<h2 class="clo-footer__heading">Company</h2>
			<address>
				<span><?php echo esc_html( $clo_company['name'] ); ?></span>
				<span>Company number: <?php echo esc_html( $clo_company['number'] ); ?></span>
				<span><?php echo esc_html( $clo_company['address'] ); ?></span>
				<?php if ( is_email( $clo_company['email'] ) ) : ?>
					<a href="<?php echo esc_url( 'mailto:' . antispambot( $clo_company['email'] ) ); ?>"><?php echo esc_html( antispambot( $clo_company['email'] ) ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $clo_company['email'] ); ?></span>
				<?php endif; ?>
			</address>
		</div>
	</div>

	<div class="clo-footer__base">
		<div class="clo-footer__base-row clo-container">
			<?php if ( $clo_payments ) : ?>
				<div class="clo-payments">
					<span class="clo-payments__label" id="clo-payments-label">We accept</span>
					<ul class="clo-payments__list" aria-labelledby="clo-payments-label">
						<?php foreach ( $clo_payments as $clo_method ) : ?>
							<li><?php echo esc_html( $clo_method ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<p class="clo-footer__copyright">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $clo_company['name'] ); ?></p>
		</div>
	</div>
</footer><!-- #colophon -->
