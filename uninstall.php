<?php
/**
 * Removes all plugin data when Layout Now is deleted from the Plugins screen.
 *
 * Deletes saved layouts (and their meta), the cache version option and cached query results.
 * Posts and other site content are never touched.
 *
 * @package LayoutNow
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Deletes Layout Now data for the current site.
 */
function layout_now_uninstall_site() {
	global $wpdb;

	$layout_ids = get_posts(
		array(
			'post_type'      => 'layout_now',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $layout_ids as $layout_id ) {
		wp_delete_post( $layout_id, true );
	}

	delete_option( 'layout_now_cache_version' );

	// Cached query results are transients named layout_now_{hash}; there is no core API to delete by prefix.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time cleanup on uninstall.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_layout_now_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_layout_now_' ) . '%'
		)
	);
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $layout_now_site_id ) {
		switch_to_blog( $layout_now_site_id );
		layout_now_uninstall_site();
		restore_current_blog();
	}
} else {
	layout_now_uninstall_site();
}
