<?php
/**
 * Helpers shared by the setup steps. Loaded by each step file.
 *
 * Every helper checks before it acts, so the steps are safe to run again.
 *
 * @package clo-setup
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

// Content in this repo is trusted. Stop KSES from stripping forms and block markup,
// which it would otherwise do because WP-CLI runs without a logged-in user.
kses_remove_filters();

/**
 * Print a line of progress.
 *
 * @param string $message Message.
 */
function clo_setup_log( $message ) {
	WP_CLI::log( '  ' . $message );
}

/**
 * Whether an option word was passed to the step, for example reset-menus.
 *
 * setup.sh passes its own flags on as plain words, because wp eval-file
 * rejects unknown --flags.
 *
 * @param array<int,string> $args Positional arguments given to wp eval-file.
 * @param string            $flag Option word.
 * @return bool
 */
function clo_setup_flag( $args, $flag ) {
	return in_array( $flag, (array) $args, true );
}

/**
 * Set an option and report whether it changed.
 *
 * @param string $name  Option name.
 * @param mixed  $value Value.
 */
function clo_setup_option( $name, $value ) {
	$current = get_option( $name, null );
	if ( $current === $value || ( is_scalar( $current ) && is_scalar( $value ) && (string) $current === (string) $value ) ) {
		return;
	}
	update_option( $name, $value );
	clo_setup_log( sprintf( 'Set %s', $name ) );
}

/**
 * Create a term if it does not exist yet, and return its ID.
 *
 * The description is only written when the term has none, so text edited in
 * the dashboard is kept.
 *
 * @param string              $taxonomy Taxonomy.
 * @param string              $name     Term name.
 * @param string              $slug     Term slug.
 * @param array<string,mixed> $extra    Optional: parent (ID), description.
 * @return int Term ID, or 0 on failure.
 */
function clo_setup_term( $taxonomy, $name, $slug, $extra = array() ) {
	$parent = isset( $extra['parent'] ) ? (int) $extra['parent'] : 0;
	$term   = get_term_by( 'slug', $slug, $taxonomy );

	if ( ! $term ) {
		$result = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'        => $slug,
				'parent'      => $parent,
				'description' => isset( $extra['description'] ) ? $extra['description'] : '',
			)
		);
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( sprintf( 'Could not create %s "%s": %s', $taxonomy, $name, $result->get_error_message() ) );
			return 0;
		}
		clo_setup_log( sprintf( 'Created %s: %s', $taxonomy, $name ) );
		return (int) $result['term_id'];
	}

	$update = array();
	// WordPress stores "&" in term names as "&amp;".
	if ( wp_specialchars_decode( $term->name ) !== $name ) {
		$update['name'] = $name;
	}
	if ( (int) $term->parent !== $parent ) {
		$update['parent'] = $parent;
	}
	if ( '' === trim( $term->description ) && ! empty( $extra['description'] ) ) {
		$update['description'] = $extra['description'];
	}
	if ( $update ) {
		wp_update_term( $term->term_id, $taxonomy, $update );
		clo_setup_log( sprintf( 'Updated %s: %s', $taxonomy, $name ) );
	}

	return (int) $term->term_id;
}

/**
 * Read a page body from scripts/setup/pages/.
 *
 * @param string $file File name.
 * @return string
 */
function clo_setup_page_content( $file ) {
	$path = __DIR__ . '/pages/' . $file;
	if ( ! is_readable( $path ) ) {
		WP_CLI::error( 'Missing page content file: ' . $path );
	}

	return trim( (string) file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * Create or update a page.
 *
 * A page is found by the key this script stores on it, then by its slug, then
 * by an existing page ID (for pages WordPress or WooCommerce made, such as the
 * privacy page). Content is only replaced while it is still exactly what this
 * script last wrote, so edits made in the dashboard are never overwritten.
 *
 * @param array<string,mixed> $page key, title, slug, and optional content, parent, adopt_id.
 * @return int Page ID.
 */
function clo_setup_page( $page ) {
	$key     = $page['key'];
	$content = isset( $page['content'] ) ? $page['content'] : null;
	$parent  = isset( $page['parent'] ) ? (int) $page['parent'] : 0;

	$found = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => '_clo_page_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	$id      = $found ? (int) $found[0] : 0;
	$adopted = false;

	if ( ! $id ) {
		$by_path = get_page_by_path( $page['slug'], OBJECT, 'page' );
		if ( $by_path && 'trash' !== $by_path->post_status ) {
			$id      = (int) $by_path->ID;
			$adopted = true;
		}
	}
	if ( ! $id && ! empty( $page['adopt_id'] ) ) {
		$existing = get_post( (int) $page['adopt_id'] );
		if ( $existing && 'page' === $existing->post_type && 'trash' !== $existing->post_status ) {
			$id      = (int) $existing->ID;
			$adopted = true;
		}
	}

	if ( ! $id ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $page['slug'],
				'post_parent'  => $parent,
				'post_content' => (string) $content,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( sprintf( 'Could not create page "%s": %s', $page['title'], $id->get_error_message() ) );
		}
		update_post_meta( $id, '_clo_page_key', $key );
		if ( null !== $content ) {
			update_post_meta( $id, '_clo_content_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
			update_post_meta( $id, '_clo_source_hash', md5( $content ) );
		}
		clo_setup_log( sprintf( 'Created page: %s', $page['title'] ) );
		return (int) $id;
	}

	$post   = get_post( $id );
	$update = array();

	if ( $post->post_title !== $page['title'] && ( $adopted || ! empty( $page['force_title'] ) ) ) {
		$update['post_title'] = $page['title'];
	}
	if ( $post->post_name !== $page['slug'] && ( $adopted || ! empty( $page['force_title'] ) ) ) {
		$update['post_name'] = $page['slug'];
	}
	if ( 'publish' !== $post->post_status && ( $adopted || ! get_post_meta( $id, '_clo_page_key', true ) ) ) {
		$update['post_status'] = 'publish';
	}

	if ( null !== $content ) {
		// _clo_content_hash is the page as saved, to spot dashboard edits.
		// _clo_source_hash is the repo file it came from, to spot new versions of the text.
		$saved_hash  = get_post_meta( $id, '_clo_content_hash', true );
		$source_hash = get_post_meta( $id, '_clo_source_hash', true );
		if ( $saved_hash ) {
			$untouched = hash_equals( $saved_hash, md5( $post->post_content ) );
		} else {
			// Pages WordPress and WooCommerce create as drafts hold their own sample text: replace it.
			$untouched = ( $adopted && 'publish' !== $post->post_status ) || '' === trim( $post->post_content );
		}
		if ( $untouched && $source_hash !== md5( $content ) ) {
			$update['post_content'] = $content;
		} elseif ( ! $untouched && $source_hash !== md5( $content ) ) {
			clo_setup_log( sprintf( 'Kept page "%s": it has been edited in the dashboard', $post->post_title ) );
		}
	}

	if ( $update ) {
		$update['ID'] = $id;
		wp_update_post( $update );
		clo_setup_log( sprintf( 'Updated page: %s (%s)', $page['title'], implode( ', ', array_keys( array_diff_key( $update, array( 'ID' => 1 ) ) ) ) ) );
	}
	update_post_meta( $id, '_clo_page_key', $key );
	if ( isset( $update['post_content'] ) ) {
		update_post_meta( $id, '_clo_content_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
		update_post_meta( $id, '_clo_source_hash', md5( $content ) );
	}

	return (int) $id;
}

/**
 * Find a page this script created, by its key.
 *
 * @param string $key Page key.
 * @return int Page ID or 0.
 */
function clo_setup_page_id( $key ) {
	$found = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => '_clo_page_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	return $found ? (int) $found[0] : 0;
}
