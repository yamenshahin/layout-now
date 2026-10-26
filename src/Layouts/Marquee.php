<?php
/**
 * Infinite marquee layout.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Layouts;

use LayoutNow\Render\View;

defined( 'ABSPATH' ) || exit;

/**
 * Continuous auto-scrolling stream of cards. Pure CSS animation over a duplicated track;
 * a small script only adds the pause button and pauses off-screen marquees.
 */
final class Marquee extends AbstractLayout {

	/**
	 * Minimum cards per loop copy, so one copy is wider than typical containers.
	 */
	const MIN_PER_COPY = 8;

	/**
	 * Seconds each medium card takes to scroll past, per speed.
	 */
	const SECONDS_PER_CARD = array(
		'slow'   => 7,
		'medium' => 4.5,
		'fast'   => 2.8,
	);

	/**
	 * Card widths in px, per size (kept in sync with marquee.css).
	 */
	const WIDTHS = array(
		's' => 220,
		'm' => 280,
		'l' => 340,
	);

	/**
	 * {@inheritDoc}
	 */
	protected $has_script = true;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'marquee';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Infinite Marquee', 'layout-now' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_fields() {
		return array(
			'speed'        => array(
				'type'    => 'select',
				'label'   => __( 'Speed', 'layout-now' ),
				'default' => 'medium',
				'choices' => array(
					'slow'   => __( 'Slow', 'layout-now' ),
					'medium' => __( 'Medium', 'layout-now' ),
					'fast'   => __( 'Fast', 'layout-now' ),
				),
			),
			'direction'    => array(
				'type'    => 'select',
				'label'   => __( 'Direction', 'layout-now' ),
				'default' => 'left',
				'choices' => array(
					'left'  => __( 'Left', 'layout-now' ),
					'right' => __( 'Right', 'layout-now' ),
				),
			),
			'rows'         => array(
				'type'    => 'select',
				'label'   => __( 'Rows', 'layout-now' ),
				'default' => '1',
				'choices' => array(
					'1' => __( 'One row', 'layout-now' ),
					'2' => __( 'Two rows', 'layout-now' ),
				),
			),
			'size'         => array(
				'type'    => 'select',
				'label'   => __( 'Card size', 'layout-now' ),
				'default' => 'm',
				'choices' => array(
					's' => __( 'Small', 'layout-now' ),
					'm' => __( 'Medium', 'layout-now' ),
					'l' => __( 'Large', 'layout-now' ),
				),
			),
			'fade'         => array(
				'type'    => 'select',
				'label'   => __( 'Edge fade', 'layout-now' ),
				'default' => 'soft',
				'choices' => array(
					'none'   => __( 'None', 'layout-now' ),
					'soft'   => __( 'Soft', 'layout-now' ),
					'strong' => __( 'Strong', 'layout-now' ),
				),
			),
			'pause_button' => array(
				'type'    => 'toggle',
				'label'   => __( 'Show pause button', 'layout-now' ),
				'default' => true,
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function render( array $items, View $view ) {
		$items = array_values( $items );
		$count = count( $items );
		$rows  = '2' === $view->option( 'rows' ) && $count > 1 ? 2 : 1;

		$row_items = array( $items );
		if ( 2 === $rows ) {
			// Second row starts halfway through the list so the rows don't mirror each other.
			$half        = (int) floor( $count / 2 );
			$row_items[] = array_merge( array_slice( $items, $half ), array_slice( $items, 0, $half ) );
		}

		// Repeat the posts within one copy until it is wide enough to loop without gaps.
		$repeat   = max( 1, (int) ceil( self::MIN_PER_COPY / $count ) );
		$per_card = self::SECONDS_PER_CARD[ $view->option( 'speed', 'medium' ) ] ?? self::SECONDS_PER_CARD['medium'];
		$width    = self::WIDTHS[ $view->option( 'size', 'm' ) ] ?? self::WIDTHS['m'];
		$duration = round( $count * $repeat * $per_card * $width / self::WIDTHS['m'], 1 );

		return $view->template(
			'layouts/marquee',
			$items,
			array(
				'rows'     => $row_items,
				'repeat'   => $repeat,
				'duration' => $duration,
			)
		);
	}
}
