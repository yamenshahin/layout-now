<?php
/**
 * Caches source results.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Render;

use LayoutNow\PostType;
use LayoutNow\Sources\Item;
use LayoutNow\Sources\SourceInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Transient cache keyed by the source query and a global version that is bumped whenever content
 * changes, so stale entries are never read and simply expire.
 */
final class ItemCache {

	const VERSION_OPTION = 'layout_now_cache_version';

	const TTL = 12 * HOUR_IN_SECONDS;

	/**
	 * Bump when the cached Item shape changes, so old entries are never read back.
	 */
	const FORMAT = 2;

	/**
	 * Registers invalidation hooks.
	 */
	public function hooks() {
		add_action( 'save_post', array( $this, 'flush_for_post' ) );
		add_action( 'deleted_post', array( $this, 'flush' ) );
		add_action( 'trashed_post', array( $this, 'flush' ) );
		add_action( 'edited_term', array( $this, 'flush' ) );
		add_action( 'delete_term', array( $this, 'flush' ) );
		add_action( 'profile_update', array( $this, 'flush' ) );
		// Items cache permalinks.
		add_action( 'update_option_permalink_structure', array( $this, 'flush' ) );
	}

	/**
	 * Returns items from cache or the source.
	 *
	 * @param SourceInterface $source  Source.
	 * @param array           $options Source options.
	 * @param array           $context Render context.
	 * @return Item[]
	 */
	public function get( SourceInterface $source, array $options, array $context ) {
		$key = $source->get_cache_key( $options, $context );

		/**
		 * Filters whether source results are cached.
		 *
		 * @param bool $enabled Whether caching is enabled.
		 */
		if ( null === $key || ! apply_filters( 'layout_now_cache_enabled', true ) ) {
			return $source->get_items( $options, $context );
		}

		$transient = 'layout_now_' . md5( $key . '|' . $this->version() . '|' . self::FORMAT );
		$cached    = get_transient( $transient );

		if ( is_array( $cached ) ) {
			return array_map( array( Item::class, 'from_array' ), $cached );
		}

		$items = $source->get_items( $options, $context );

		set_transient(
			$transient,
			array_map(
				static function ( Item $item ) {
					return $item->to_array();
				},
				$items
			),
			self::TTL
		);

		return $items;
	}

	/**
	 * Flushes when real content (not our own layouts or revisions) is saved.
	 *
	 * @param int $post_id Post ID.
	 */
	public function flush_for_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || PostType::NAME === get_post_type( $post_id ) ) {
			return;
		}
		$this->flush();
	}

	/**
	 * Invalidates every cached result.
	 */
	public function flush() {
		update_option( self::VERSION_OPTION, (string) microtime( true ), true );
	}

	/**
	 * Current cache version.
	 *
	 * @return string
	 */
	private function version() {
		return (string) get_option( self::VERSION_OPTION, '1' );
	}
}
