<?php
/**
 * New Stock tag: removed automatically 30 days after it was added.
 *
 * The date the tag was added is kept on the product. Products tagged before
 * this was in place count from their publish date.
 *
 * @package clo-store
 */

defined( 'ABSPATH' ) || exit;

const CLO_NEW_STOCK_TAG  = 'new-stock';
const CLO_NEW_STOCK_DAYS = 30;
const CLO_NEW_STOCK_META = '_clo_new_stock_since';
const CLO_NEW_STOCK_HOOK = 'clo_expire_new_stock';

// Note when the tag is added to or taken off a product.
add_action(
	'set_object_terms',
	function ( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( 'product_tag' !== $taxonomy ) {
			return;
		}
		$tag = get_term_by( 'slug', CLO_NEW_STOCK_TAG, 'product_tag' );
		if ( ! $tag ) {
			return;
		}
		$had = in_array( (int) $tag->term_taxonomy_id, array_map( 'intval', $old_tt_ids ), true );
		$has = in_array( (int) $tag->term_taxonomy_id, array_map( 'intval', $tt_ids ), true );
		if ( $has && ! $had ) {
			update_post_meta( $object_id, CLO_NEW_STOCK_META, time() );
		} elseif ( ! $has && $had ) {
			delete_post_meta( $object_id, CLO_NEW_STOCK_META );
		}
	},
	10,
	6
);

add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( CLO_NEW_STOCK_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', CLO_NEW_STOCK_HOOK );
		}
	}
);

add_action( CLO_NEW_STOCK_HOOK, 'clo_expire_new_stock' );

/**
 * Take the New Stock tag off products that have had it for more than 30 days.
 *
 * @return int Number of products changed.
 */
function clo_expire_new_stock() {
	$cutoff  = time() - CLO_NEW_STOCK_DAYS * DAY_IN_SECONDS;
	$changed = 0;
	$ids     = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'slug',
					'terms'    => CLO_NEW_STOCK_TAG,
				),
			),
		)
	);

	foreach ( $ids as $id ) {
		$since = (int) get_post_meta( $id, CLO_NEW_STOCK_META, true );
		if ( ! $since ) {
			$since = (int) get_post_time( 'U', true, $id );
		}
		if ( $since && $since < $cutoff ) {
			wp_remove_object_terms( $id, CLO_NEW_STOCK_TAG, 'product_tag' );
			delete_post_meta( $id, CLO_NEW_STOCK_META );
			if ( function_exists( 'wc_delete_product_transients' ) ) {
				wc_delete_product_transients( $id );
			}
			++$changed;
		}
	}

	return $changed;
}
