<?php
/**
 * Contract for data sources.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Sources;

defined( 'ABSPATH' ) || exit;

/**
 * A data source turns its own options into a list of normalized Items. Layout templates only
 * ever see Items, so new sources never require template changes.
 */
interface SourceInterface {

	/**
	 * Unique ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Human-readable name.
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Option field definitions (see Settings\Schema).
	 *
	 * @return array
	 */
	public function get_fields();

	/**
	 * Fetches items.
	 *
	 * @param array $options Sanitized options.
	 * @param array $context {
	 *     Render context.
	 *
	 *     @type int|null $limit      Item count forced by the layout, overriding the source's own count.
	 *     @type int      $current_id ID of the post being viewed, 0 if none.
	 * }
	 * @return Item[]
	 */
	public function get_items( array $options, array $context );

	/**
	 * Cache key for a query, or null when results must not be cached (e.g. random order).
	 *
	 * @param array $options Sanitized options.
	 * @param array $context Render context.
	 * @return string|null
	 */
	public function get_cache_key( array $options, array $context );
}
