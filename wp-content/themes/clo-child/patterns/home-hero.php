<?php
/**
 * Title: Homepage: hero
 * Slug: clo/home-hero
 * Categories: clo-home
 * Description: Headline, the five ways to buy, and the Shop Now and Wholesale buttons.
 * Keywords: hero, banner, headline
 *
 * @package clo-child
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"clo-hero","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull clo-hero"><!-- wp:group {"className":"clo-container clo-hero__inner","layout":{"type":"default"}} -->
<div class="wp-block-group clo-container clo-hero__inner"><!-- wp:group {"className":"clo-hero__copy","layout":{"type":"default"}} -->
<div class="wp-block-group clo-hero__copy"><!-- wp:heading {"level":1,"className":"clo-hero__title"} -->
<h1 class="wp-block-heading clo-hero__title">Clearance and Wholesale Stock, One Item or by the Pallet</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"clo-hero__kicker"} -->
<p class="clo-hero__kicker">New · Clearance · Wholesale · Liquidation · Bulk Buy</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"clo-hero__text"} -->
<p class="clo-hero__text">Quality products at competitive prices, from single items to wholesale and bulk-buy deals.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"clo-hero__buttons"} -->
<div class="wp-block-buttons clo-hero__buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( clo_shop_url() ); ?>">Shop Now</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( clo_page_url( 'wholesale-bulk-buy' ) ); ?>">Wholesale &amp; Bulk Buy</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"clo-hero__stickers","layout":{"type":"default"}} -->
<div class="wp-block-group clo-hero__stickers"><!-- wp:paragraph {"className":"clo-sticker clo-sticker--one"} -->
<p class="clo-sticker clo-sticker--one">Single items</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"clo-sticker clo-sticker--two"} -->
<p class="clo-sticker clo-sticker--two">Job lots</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"clo-sticker clo-sticker--three"} -->
<p class="clo-sticker clo-sticker--three">Full pallets</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
