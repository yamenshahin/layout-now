<?php
/**
 * Edge-peek carousel layout.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Layouts;

defined( 'ABSPATH' ) || exit;

/**
 * Horizontal, swipeable row of cards where the next card peeks in from the edge.
 * Native CSS scroll-snap does the swiping; a small script adds arrows and dots.
 */
final class Carousel extends AbstractLayout {

	/**
	 * {@inheritDoc}
	 */
	protected $has_script = true;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'carousel';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Edge-Peek Carousel', 'layout-now' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_fields() {
		return array(
			'size' => array(
				'type'    => 'select',
				'label'   => __( 'Card size', 'layout-now' ),
				'default' => 'm',
				'choices' => array(
					's' => __( 'Small', 'layout-now' ),
					'm' => __( 'Medium', 'layout-now' ),
					'l' => __( 'Large', 'layout-now' ),
				),
			),
			'peek' => array(
				'type'    => 'select',
				'label'   => __( 'Peek', 'layout-now' ),
				'default' => 'm',
				'choices' => array(
					's' => __( 'Subtle', 'layout-now' ),
					'm' => __( 'Medium', 'layout-now' ),
					'l' => __( 'Generous', 'layout-now' ),
				),
			),
			'nav'  => array(
				'type'    => 'select',
				'label'   => __( 'Navigation', 'layout-now' ),
				'default' => 'both',
				'choices' => array(
					'arrows' => __( 'Arrows', 'layout-now' ),
					'dots'   => __( 'Dots', 'layout-now' ),
					'both'   => __( 'Both', 'layout-now' ),
					'none'   => __( 'None', 'layout-now' ),
				),
			),
		);
	}
}
