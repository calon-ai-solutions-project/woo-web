<?php
/**
 * Title: Homepage: two ways to shop
 * Slug: clo/home-paths
 * Categories: clo-home
 * Description: Side-by-side links for individual shoppers and for wholesale, liquidation and bulk buyers.
 * Keywords: retail, wholesale, paths
 *
 * @package clo-child
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"clo-band clo-band--white clo-paths","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull clo-band clo-band--white clo-paths"><!-- wp:group {"className":"clo-container clo-paths__grid","layout":{"type":"default"}} -->
<div class="wp-block-group clo-container clo-paths__grid"><!-- wp:group {"className":"clo-path clo-path--retail","layout":{"type":"default"}} -->
<div class="wp-block-group clo-path clo-path--retail"><!-- wp:heading {"className":"clo-path__title"} -->
<h2 class="wp-block-heading clo-path__title">Shop Individual Products</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Browse single items and small quantities, delivered across the UK.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( clo_shop_url() ); ?>">Shop the range</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"clo-path clo-path--trade","layout":{"type":"default"}} -->
<div class="wp-block-group clo-path clo-path--trade"><!-- wp:heading {"className":"clo-path__title"} -->
<h2 class="wp-block-heading clo-path__title">Shop Wholesale, Liquidation &amp; Bulk Buy</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wholesale, liquidation and clearance stock for businesses, resellers and larger orders.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( clo_page_url( 'wholesale-bulk-buy' ) ); ?>">Shop wholesale</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
