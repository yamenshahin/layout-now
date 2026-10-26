<?php
/**
 * Base class for built-in layouts.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Layouts;

use LayoutNow\Plugin;
use LayoutNow\Render\View;

defined( 'ABSPATH' ) || exit;

/**
 * Conventions: assets/css/layouts/{id}.css, optional assets/js/{id}.js, templates/layouts/{id}.php.
 */
abstract class AbstractLayout implements LayoutInterface {

	/**
	 * Whether the layout ships a script.
	 *
	 * @var bool
	 */
	protected $has_script = false;

	/**
	 * {@inheritDoc}
	 */
	public function get_item_limit( array $options ) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_style_url() {
		return Plugin::url( 'assets/css/layouts/' . $this->get_id() . '.css' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_script_url() {
		return $this->has_script ? Plugin::url( 'assets/js/' . $this->get_id() . '.js' ) : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function render( array $items, View $view ) {
		return $view->template( 'layouts/' . $this->get_id(), $items );
	}
}
