<?php
/**
 * Contract for layouts.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Layouts;

use LayoutNow\Render\View;
use LayoutNow\Sources\Item;

defined( 'ABSPATH' ) || exit;

/**
 * A layout arranges Items. It knows nothing about where they come from.
 */
interface LayoutInterface {

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
	 * Exact number of items the layout needs, or null to use the source's own count.
	 *
	 * @param array $options Sanitized layout options.
	 * @return int|null
	 */
	public function get_item_limit( array $options );

	/**
	 * Global style fields that have no effect on this layout (hidden in the admin).
	 *
	 * @return string[]
	 */
	public function get_ignored_style_fields();

	/**
	 * Stylesheet URL, or empty string.
	 *
	 * @return string
	 */
	public function get_style_url();

	/**
	 * Script URL, or empty string when the layout needs no JavaScript.
	 *
	 * @return string
	 */
	public function get_script_url();

	/**
	 * Renders the layout's inner markup.
	 *
	 * @param Item[] $items Items.
	 * @param View   $view  View helpers and settings.
	 * @return string
	 */
	public function render( array $items, View $view );
}
