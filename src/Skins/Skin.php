<?php
/**
 * Skin definition.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Skins;

defined( 'ABSPATH' ) || exit;

/**
 * A skin is a stylesheet of CSS custom properties plus a card structure. It never ships markup,
 * so any skin works with any layout.
 */
final class Skin {

	/**
	 * Unique ID, also used in the data-skin attribute.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Human-readable name.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Stylesheet URL.
	 *
	 * @var string
	 */
	public $style = '';

	/**
	 * Card structure: "stacked" (text below image) or "overlay" (text over image).
	 *
	 * @var string
	 */
	public $card = 'stacked';

	/**
	 * Whether the skin supports light/dark/auto color modes.
	 *
	 * @var bool
	 */
	public $modes = true;

	/**
	 * Values used for style controls left on "Skin default" (radius, spacing, hover).
	 *
	 * @var array
	 */
	public $defaults = array();

	/**
	 * Builds a skin from an array.
	 *
	 * @param array $data Skin data.
	 * @return Skin
	 */
	public static function from_array( array $data ) {
		$skin           = new self();
		$skin->id       = isset( $data['id'] ) ? sanitize_key( $data['id'] ) : '';
		$skin->label    = isset( $data['label'] ) ? (string) $data['label'] : $skin->id;
		$skin->style    = isset( $data['style'] ) ? (string) $data['style'] : '';
		$skin->card     = isset( $data['card'] ) && 'overlay' === $data['card'] ? 'overlay' : 'stacked';
		$skin->modes    = ! isset( $data['modes'] ) || (bool) $data['modes'];
		$skin->defaults = array_merge(
			array(
				'radius'  => 'soft',
				'spacing' => 'comfortable',
				'hover'   => 'lift',
			),
			isset( $data['defaults'] ) && is_array( $data['defaults'] ) ? $data['defaults'] : array()
		);
		return $skin;
	}

	/**
	 * Array representation for the admin app.
	 *
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}
}
