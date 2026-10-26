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
	 * Gap-free fallbacks when a site or category has fewer posts than the pattern needs.
	 *
	 * @var array
	 */
	const FALLBACKS = array(
		1 => array( 0 => 'full' ),
		2 => array(
			0 => 'hero',
			1 => 'hero-alt',
		),
		3 => array(
			0 => 'hero',
			1 => 'wide',
			2 => 'wide',
		),
		4 => array(
			0 => 'hero',
			1 => 'wide',
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
			),
		);
	}

	/**
	 * Tile shapes come from the grid, so the image ratio control does not apply.
	 *
	 * {@inheritDoc}
	 */
	public function get_ignored_style_fields() {
		return array( 'ratio' );
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
		list( $pattern, $map ) = $this->resolve( $this->pattern( $view->options() ), count( $items ) );

		$items = array_slice( array_values( $items ), 0, self::capacity( $pattern, count( $items ) ) );
		$tiles = array();
		foreach ( array_keys( $items ) as $index ) {
			$tiles[ $index ] = isset( $map[ $index ] ) ? $map[ $index ] : 'sm';
		}

		return $view->template(
			'layouts/bento',
			$items,
			array(
				'pattern' => $pattern,
				'tiles'   => $tiles,
			)
		);
	}

	/**
	 * Picks the tile map that fills the grid without holes for the number of items available.
	 *
	 * Full pattern when there are enough items; otherwise the largest pattern that fits
	 * (Two heroes → Mosaic → Hero left), then dedicated maps for 1–4 items.
	 *
	 * @param string $pattern Requested pattern.
	 * @param int    $count   Items available.
	 * @return array { 0: effective pattern key, 1: tile map }
	 */
	private function resolve( $pattern, $count ) {
		if ( $count >= self::PATTERNS[ $pattern ]['count'] ) {
			return array( $pattern, self::PATTERNS[ $pattern ]['tiles'] );
		}
		foreach ( array( 'mosaic', 'hero-left' ) as $fallback ) {
			if ( $count >= self::PATTERNS[ $fallback ]['count'] ) {
				// Keep the centered hero when the user picked it and there are enough items for it.
				$fallback = 'hero-left' === $fallback && 'hero-center' === $pattern ? 'hero-center' : $fallback;
				return array( $fallback, self::PATTERNS[ $fallback ]['tiles'] );
			}
		}
		return array( 'fit-' . $count, self::FALLBACKS[ max( 1, $count ) ] );
	}

	/**
	 * How many items an effective pattern displays.
	 *
	 * @param string $pattern Effective pattern key.
	 * @param int    $count   Items available.
	 * @return int
	 */
	private static function capacity( $pattern, $count ) {
		return isset( self::PATTERNS[ $pattern ] ) ? self::PATTERNS[ $pattern ]['count'] : $count;
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
