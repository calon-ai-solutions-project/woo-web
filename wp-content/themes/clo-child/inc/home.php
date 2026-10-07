<?php
/**
 * Homepage sections that need live data: product rows, category tiles,
 * reviews and the email signup. Each is a shortcode, used by the block
 * patterns in patterns/ and editable from the block editor.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_block_pattern_category( 'clo-home', array( 'label' => 'CL Outlet: homepage' ) );
	}
);

/**
 * URL of a page by its slug, falling back to a plain path.
 *
 * @param string $slug Page slug, for example "wholesale-enquiries".
 * @return string
 */
function clo_page_url( $slug ) {
	$page = get_page_by_path( $slug );

	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * The URL of the shop page.
 *
 * @return string
 */
function clo_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

/**
 * [clo_product_row] A titled row of products. Prints nothing when there are no products.
 *
 * Attributes:
 *   heading    Section heading.
 *   type       newest, or clearance (products tagged Clearance).
 *   limit      Number of products. Default 8.
 *   link       Slug of the page the "see all" link goes to.
 *   link_text  Text of that link.
 */
add_shortcode(
	'clo_product_row',
	function ( $atts ) {
		if ( ! class_exists( 'CLO_Products_Query' ) ) {
			return '';
		}
		$atts = shortcode_atts(
			array(
				'heading'   => 'Just Arrived',
				'type'      => 'newest',
				'limit'     => 8,
				'link'      => '',
				'link_text' => 'See all',
			),
			$atts,
			'clo_product_row'
		);

		$query = array(
			'limit'   => absint( $atts['limit'] ),
			'columns' => 4,
			'orderby' => 'date',
			'order'   => 'DESC',
		);
		if ( 'clearance' === $atts['type'] ) {
			$query['tag'] = 'clearance';
		}

		$products = new CLO_Products_Query( $query, 'products' );
		if ( ! $products->has_products() ) {
			return '';
		}

		ob_start();
		?>
		<section class="clo-band clo-product-row clo-product-row--<?php echo esc_attr( sanitize_html_class( $atts['type'] ) ); ?>">
			<div class="clo-container">
				<div class="clo-section-head">
					<h2 class="clo-section-head__title"><?php echo esc_html( $atts['heading'] ); ?></h2>
					<?php if ( $atts['link'] ) : ?>
						<a class="clo-section-head__more" href="<?php echo esc_url( clo_page_url( $atts['link'] ) ); ?>"><?php echo esc_html( $atts['link_text'] ); ?></a>
					<?php endif; ?>
				</div>
				<?php echo $products->get_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
);

/**
 * Line icons for the category tiles, keyed by category slug.
 *
 * @return array<string,string> SVG inner markup on a 24 x 24 grid.
 */
function clo_category_icons() {
	return array(
		'health-beauty'                  => '<path d="M10 2h4M12 2v4M9 6h6v3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z"/><path d="M7 14h10"/>',
		'household'                      => '<path d="M3 11 12 4l9 7"/><path d="M5 9.5V20h14V9.5"/><path d="M10 20v-6h4v6"/>',
		'electronics'                    => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/>',
		'clothing-fashion'               => '<path d="M9 3 4 6l2 4 2-1v12h8V9l2 1 2-4-5-3a3 3 0 0 1-6 0Z"/>',
		'home-garden'                    => '<path d="M12 21v-9"/><path d="M12 12c0-5 3-8 8-8 0 5-3 8-8 8Z"/><path d="M12 15c0-4-2.5-6.5-7-6.5 0 4 2.5 6.5 7 6.5Z"/>',
		'toys-games'                     => '<rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="9" cy="9" r="1.2"/><circle cx="15" cy="9" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="9" cy="15" r="1.2"/><circle cx="15" cy="15" r="1.2"/>',
		'pet-supplies'                   => '<circle cx="6.5" cy="10" r="1.8"/><circle cx="10" cy="5.5" r="1.8"/><circle cx="14" cy="5.5" r="1.8"/><circle cx="17.5" cy="10" r="1.8"/><path d="M8 18c0-3.5 2-6 4-6s4 2.5 4 6c0 1.7-1.6 2.6-3 2-.7-.3-1.3-.3-2 0-1.4.6-3-.3-3-2Z"/>',
		'diy-tools'                      => '<path d="M14.7 6.3a4 4 0 0 0 5 5L11 20a2.1 2.1 0 0 1-3-3l8.7-8.7a4 4 0 0 1-2-2Z"/><path d="M15 3.5a4 4 0 0 1 5.5 5.5L18 6.5Z"/>',
		'kitchen'                        => '<path d="M4 9h12v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V9Z"/><path d="M16 11h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M8 3v3M12 3v3"/>',
		'office-stationery'              => '<path d="M4 20l1.2-4.4L16 4.8a2 2 0 0 1 2.8 0l.4.4a2 2 0 0 1 0 2.8L8.4 18.8Z"/><path d="M14 7l3 3"/>',
		'sports-leisure'                 => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17M12 3.5c3.2 3.2 3.2 13.8 0 17M12 3.5c-3.2 3.2-3.2 13.8 0 17"/>',
		'seasonal'                       => '<path d="M12 2.5v19M3.8 7.25l16.4 9.5M3.8 16.75l16.4-9.5"/><path d="m9.5 3.5 2.5 2 2.5-2M9.5 20.5l2.5-2 2.5 2"/>',
		'mixed-products'                 => '<path d="m3 7.5 9-4.5 9 4.5v9L12 21l-9-4.5Z"/><path d="m3 7.5 9 4.5 9-4.5M12 12v9"/>',
		'wholesale-liquidation-bulk-buy' => '<rect x="5" y="4" width="14" height="10" rx="1"/><path d="M3 14h18M3 18h18M5 14v4M12 14v4M19 14v4M9 4v4h6V4"/>',
		'pallets-lots'                   => '<rect x="5" y="4" width="14" height="10" rx="1"/><path d="M3 14h18M3 18h18M5 14v4M12 14v4M19 14v4M9 4v4h6V4"/>',
	);
}

/**
 * [clo_category_tiles] A tile for each top-level product category, in menu order.
 *
 * A category image set in Products > Categories replaces the icon.
 *
 * Attributes:
 *   exclude  Comma-separated category slugs to leave out.
 */
add_shortcode(
	'clo_category_tiles',
	function ( $atts ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '';
		}
		$atts    = shortcode_atts( array( 'exclude' => 'wholesale-liquidation-bulk-buy' ), $atts, 'clo_category_tiles' );
		$exclude = array_filter( array_map( 'trim', explode( ',', $atts['exclude'] ) ) );
		$terms   = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		$default = (int) get_option( 'default_product_cat' );
		$terms   = array_filter(
			$terms,
			function ( $term ) use ( $exclude, $default ) {
				return ! in_array( $term->slug, $exclude, true ) && ( 'uncategorized' !== $term->slug || (int) $term->term_id !== $default );
			}
		);
		usort(
			$terms,
			function ( $a, $b ) {
				return (int) get_term_meta( $a->term_id, 'order', true ) - (int) get_term_meta( $b->term_id, 'order', true );
			}
		);
		$icons = clo_category_icons();

		ob_start();
		?>
		<ul class="clo-tiles">
			<?php foreach ( $terms as $term ) : ?>
				<?php
				$thumbnail = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$count     = (int) $term->count;
				?>
				<li class="clo-tile">
					<a class="clo-tile__link" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<span class="clo-tile__media" aria-hidden="true">
							<?php if ( $thumbnail ) : ?>
								<?php echo wp_get_attachment_image( $thumbnail, 'woocommerce_thumbnail', false, array( 'alt' => '' ) ); ?>
							<?php else : ?>
								<svg class="clo-tile__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo isset( $icons[ $term->slug ] ) ? $icons[ $term->slug ] : $icons['mixed-products']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
							<?php endif; ?>
						</span>
						<span class="clo-tile__name"><?php echo esc_html( $term->name ); ?></span>
						<?php if ( $count ) : ?>
							<span class="clo-tile__count"><?php echo esc_html( sprintf( 1 === $count ? '%d product' : '%d products', $count ) ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return ob_get_clean();
	}
);

/**
 * [clo_reviews] Recent reviews from verified buyers.
 *
 * Hidden until at least three real reviews have been approved, so the
 * homepage never shows an empty or made-up reviews block.
 */
add_shortcode(
	'clo_reviews',
	function () {
		if ( ! post_type_exists( 'product' ) ) {
			return '';
		}
		$args = array(
			'post_type'  => 'product',
			'status'     => 'approve',
			'type'       => 'review',
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'   => 'verified',
					'value' => '1',
				),
			),
		);
		$minimum = (int) apply_filters( 'clo_reviews_minimum', 3 );
		if ( (int) get_comments( array_merge( $args, array( 'count' => true ) ) ) < $minimum ) {
			return '';
		}
		$reviews = get_comments( array_merge( $args, array( 'number' => 3 ) ) );

		ob_start();
		?>
		<section class="clo-band clo-band--white clo-reviews">
			<div class="clo-container">
				<h2 class="clo-section-head__title">What Our Customers Say</h2>
				<ul class="clo-reviews__list">
					<?php foreach ( $reviews as $review ) : ?>
						<?php
						$rating  = (int) get_comment_meta( $review->comment_ID, 'rating', true );
						$name    = strtok( trim( $review->comment_author ), ' ' );
						$product = get_post( $review->comment_post_ID );
						?>
						<li class="clo-review">
							<?php if ( $rating ) : ?>
								<p class="clo-review__stars" role="img" aria-label="<?php echo esc_attr( sprintf( 'Rated %d out of 5', $rating ) ); ?>"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></p>
							<?php endif; ?>
							<blockquote class="clo-review__text"><p><?php echo esc_html( wp_trim_words( $review->comment_content, 45 ) ); ?></p></blockquote>
							<p class="clo-review__by">
								<?php echo esc_html( $name ? $name : 'A customer' ); ?>, verified buyer
								<?php if ( $product ) : ?>
									<br><a href="<?php echo esc_url( get_permalink( $product ) ); ?>"><?php echo esc_html( get_the_title( $product ) ); ?></a>
								<?php endif; ?>
							</p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
);

/**
 * [clo_signup] Email signup for new stock alerts.
 *
 * Shown once MailPoet is active and its form ID is set in inc/site-config.php.
 */
add_shortcode(
	'clo_signup',
	function () {
		$form_id = function_exists( 'clo_signup_form_id' ) ? (int) clo_signup_form_id() : 0;
		if ( ! $form_id || ! shortcode_exists( 'mailpoet_form' ) ) {
			return '';
		}

		ob_start();
		?>
		<section class="clo-band clo-band--yellow clo-signup">
			<div class="clo-container clo-signup__inner">
				<div class="clo-signup__copy">
					<h2 class="clo-section-head__title">Get new stock alerts first.</h2>
					<p>Hear about new stock and clearance deals by email. Unsubscribe at any time.</p>
				</div>
				<div class="clo-signup__form">
					<?php echo do_shortcode( sprintf( '[mailpoet_form id="%d"]', $form_id ) ); ?>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
);
