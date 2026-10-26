<?php
/**
 * Bento grid layout.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Layouts;

use LayoutNow\Render\View;

defined( 'ABSPATH' ) || exit;

/**
 * Asymmetric showcase grid: a large hero card with smaller tiles packed around it. Pure CSS grid, no JS.
 *
 * Each pattern is a fixed tile map on a 4-column grid, so the block always fills completely.
 */
final class Bento extends AbstractLayout {

	/**
	 * Tile maps: item index => tile size. Unlisted indexes are small tiles.
	 *
	 * @var array
	 */
	const PATTERNS = array(
		'hero-left'   => array(
			'count' => 5,
			'tiles' => array( 0 => 'hero' ),
		),
		'hero-center' => array(
			'count' => 5,
			'tiles' => array( 0 => 'hero' ),
		),
		'mosaic'      => array(
			'count' => 6,
			'tiles' => array(
				0 => 'hero',
				1 => 'wide',
				4 => 'wide',
				5 => 'wide',
			),
		),
		'duo'         => array(
			'count' => 10,
			'tiles' => array(
				0 => 'hero',
				5 => 'hero-alt',
			),
		),
	);

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'bento';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Bento Grid', 'layout-now' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_fields() {
		return array(
			'pattern' => array(
				'type'    => 'select',
				'label'   => __( 'Pattern', 'layout-now' ),
				'default' => 'hero-left',
				'choices' => array(
					'hero-left'   => __( 'Hero left · 5 posts', 'layout-now' ),
					'hero-center' => __( 'Hero center · 5 posts', 'layout-now' ),
					'mosaic'      => __( 'Mosaic · 6 posts', 'layout-now' ),
					'duo'         => __( 'Two heroes · 10 posts', 'layout-now' ),
				),
			),
			'height'  => array(
				'type'    => 'select',
				'label'   => __( 'Tile height', 'layout-now' ),
				'default' => 'regular',
				'choices' => array(
					'compact' => __( 'Compact', 'layout-now' ),
					'regular' => __( 'Regular', 'layout-now' ),
					'tall'    => __( 'Tall', 'layout-now' ),
				),
				'live'    => true,
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_item_limit( array $options ) {
		return self::PATTERNS[ $this->pattern( $options ) ]['count'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function render( array $items, View $view ) {
		$pattern = self::PATTERNS[ $this->pattern( $view->options() ) ];

		$tiles = array();
		foreach ( array_keys( $items ) as $index ) {
			$tiles[ $index ] = isset( $pattern['tiles'][ $index ] ) ? $pattern['tiles'][ $index ] : 'sm';
		}

		return $view->template( 'layouts/bento', $items, array( 'tiles' => $tiles ) );
	}

	/**
	 * Valid pattern key.
	 *
	 * @param array $options Layout options.
	 * @return string
	 */
	private function pattern( array $options ) {
		return isset( $options['pattern'], self::PATTERNS[ $options['pattern'] ] ) ? $options['pattern'] : 'hero-left';
	}
}
