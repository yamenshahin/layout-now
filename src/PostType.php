<?php
/**
 * Private post type that stores saved layouts.
 *
 * @package LayoutNow
 */

namespace LayoutNow;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the layout_now post type.
 */
final class PostType {

	const NAME = 'layout_now';

	/**
	 * Registers the post type. Fully private: managed only through our own REST routes and admin app.
	 */
	public static function register() {
		$cap = Plugin::capability();

		register_post_type(
			self::NAME,
			array(
				'labels'              => array(
					'name'          => __( 'Layouts', 'layout-now' ),
					'singular_name' => __( 'Layout', 'layout-now' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'can_export'          => true,
				'delete_with_user'    => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => false,
				'capabilities'        => array(
					'edit_post'              => $cap,
					'read_post'              => $cap,
					'delete_post'            => $cap,
					'edit_posts'             => $cap,
					'edit_others_posts'      => $cap,
					'edit_published_posts'   => $cap,
					'publish_posts'          => $cap,
					'read_private_posts'     => $cap,
					'delete_posts'           => $cap,
					'delete_others_posts'    => $cap,
					'delete_published_posts' => $cap,
					'create_posts'           => $cap,
				),
			)
		);
	}
}
