<?php
/**
 * Instant-filter grid layout.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Layouts;

use LayoutNow\Render\View;

defined( 'ABSPATH' ) || exit;

/**
 * Multi-column grid topped with term pills that filter cards in place without a reload.
 * Pills are built from the terms of the items actually shown, so no pill is ever empty.
 */
final class FilterGrid extends AbstractLayout {

	/**
	 * {@inheritDoc}
	 */
	protected $has_script = true;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'filter-grid';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Instant-Filter Grid', 'layout-now' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_fields() {
		return array(
			'taxonomy'    => array(
				'type'    => 'select',
				'label'   => __( 'Filter by', 'layout-now' ),
				'default' => 'category',
				'choices' => array(
					'category' => __( 'Category', 'layout-now' ),
					'post_tag' => __( 'Tag', 'layout-now' ),
				),
			),
			'size'        => array(
				'type'    => 'select',
				'label'   => __( 'Card size', 'layout-now' ),
				'default' => 'm',
				'choices' => array(
					's' => __( 'Small', 'layout-now' ),
					'm' => __( 'Medium', 'layout-now' ),
					'l' => __( 'Large', 'layout-now' ),
				),
			),
			'pill_style'  => array(
				'type'    => 'select',
				'label'   => __( 'Pill style', 'layout-now' ),
				'default' => 'solid',
				'choices' => array(
					'solid' => __( 'Solid', 'layout-now' ),
					'soft'  => __( 'Soft', 'layout-now' ),
				),
			),
			'show_all'    => array(
				'type'    => 'toggle',
				'label'   => __( 'Show "All" pill', 'layout-now' ),
				'default' => true,
			),
			'show_counts' => array(
				'type'    => 'toggle',
				'label'   => __( 'Show post counts', 'layout-now' ),
				'default' => false,
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function render( array $items, View $view ) {
		$taxonomy = $view->option( 'taxonomy', 'category' );
		$terms    = array();
		$item_ids = array();

		foreach ( array_values( $items ) as $index => $item ) {
			$item_ids[ $index ] = array();
			foreach ( $item->terms as $term ) {
				if ( $taxonomy !== $term['taxonomy'] ) {
					continue;
				}
				$item_ids[ $index ][] = (string) $term['id'];
				if ( ! isset( $terms[ $term['id'] ] ) ) {
					$terms[ $term['id'] ] = array(
						'id'    => (string) $term['id'],
						'name'  => $term['name'],
						'count' => 0,
					);
				}
				++$terms[ $term['id'] ]['count'];
			}
		}

		// Most-used terms first, then alphabetical.
		usort(
			$terms,
			static function ( $a, $b ) {
				return $b['count'] <=> $a['count'] ?: strcasecmp( $a['name'], $b['name'] );
			}
		);

		return $view->template(
			'layouts/filter-grid',
			array_values( $items ),
			array(
				'terms'    => $terms,
				'item_ids' => $item_ids,
			)
		);
	}
}
