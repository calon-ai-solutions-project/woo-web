<?php
/**
 * Title: Homepage: wholesale enquiries banner
 * Slug: clo/home-wholesale-banner
 * Categories: clo-home
 * Description: Navy banner inviting wholesale enquiries, with a yellow button.
 * Keywords: wholesale, enquiry, trade
 *
 * @package clo-child
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"clo-band clo-band--navy clo-trade-banner","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull clo-band clo-band--navy clo-trade-banner"><!-- wp:group {"className":"clo-container clo-trade-banner__inner","layout":{"type":"default"}} -->
<div class="wp-block-group clo-container clo-trade-banner__inner"><!-- wp:group {"className":"clo-trade-banner__copy","layout":{"type":"default"}} -->
<div class="wp-block-group clo-trade-banner__copy"><!-- wp:heading {"className":"clo-trade-banner__title"} -->
<h2 class="wp-block-heading clo-trade-banner__title">Wholesale Enquiries</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Looking for specific products, larger quantities or regular supply? Tell us what you need and we will come back to you within <?php echo esc_html( clo_response_time() ); ?>.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( clo_page_url( 'wholesale-enquiries' ) ); ?>">Send an enquiry</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
