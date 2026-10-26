<?php
/**
 * The [layout_now id="..."] shortcode.
 *
 * @package LayoutNow
 */

namespace LayoutNow;

use LayoutNow\Render\Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * Thin adapter between the shortcode API and the renderer.
 */
final class Shortcode {

	const TAG = 'layout_now';

	/**
	 * Renderer.
	 *
	 * @var Renderer
	 */
	private $renderer;

	/**
	 * Constructor.
	 *
	 * @param Renderer $renderer Renderer.
	 */
	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Registers the shortcode.
	 */
	public function register() {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, self::TAG );
		return $this->renderer->render_saved( absint( $atts['id'] ) );
	}
}
