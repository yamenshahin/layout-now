<?php
/**
 * Persistence for saved layouts.
 *
 * @package LayoutNow
 */

namespace LayoutNow;

use LayoutNow\Settings\Schema;
use WP_Error;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * CRUD for layout_now posts. The config lives in one JSON meta value.
 */
final class LayoutStore {

	const META = '_layout_now_config';

	/**
	 * Settings schema.
	 *
	 * @var Schema
	 */
	private $schema;

	/**
	 * Constructor.
	 *
	 * @param Schema $schema Settings schema.
	 */
	public function __construct( Schema $schema ) {
		$this->schema = $schema;
	}

	/**
	 * Lists all saved layouts, newest first.
	 *
	 * @return array[]
	 */
	public function all() {
		$posts = get_posts(
			array(
				'post_type'      => PostType::NAME,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		return array_map( array( $this, 'to_array' ), $posts );
	}

	/**
	 * Fetches one layout.
	 *
	 * @param int $id Post ID.
	 * @return array|null
	 */
	public function get( $id ) {
		$post = $this->post( $id );
		return $post ? $this->to_array( $post ) : null;
	}

	/**
	 * Fetches only the normalized config of a layout.
	 *
	 * @param int $id Post ID.
	 * @return array|null
	 */
	public function get_config( $id ) {
		$post = $this->post( $id );
		return $post ? $this->read_config( $post->ID ) : null;
	}

	/**
	 * Creates a layout.
	 *
	 * @param string $title  Title.
	 * @param array  $config Raw config.
	 * @return int|WP_Error Post ID.
	 */
	public function create( $title, array $config ) {
		$title = sanitize_text_field( $title );

		return wp_insert_post(
			wp_slash(
				array(
					'post_type'   => PostType::NAME,
					'post_status' => 'publish',
					'post_title'  => '' !== $title ? $title : __( 'Untitled layout', 'layout-now' ),
					'meta_input'  => array(
						self::META => wp_json_encode( $this->schema->normalize( $config ) ),
					),
				)
			),
			true
		);
	}

	/**
	 * Updates a layout. Null arguments are left unchanged.
	 *
	 * @param int         $id     Post ID.
	 * @param string|null $title  New title.
	 * @param array|null  $config New raw config.
	 * @return true|WP_Error
	 */
	public function update( $id, $title = null, $config = null ) {
		$post = $this->post( $id );
		if ( ! $post ) {
			return new WP_Error( 'layout_now_not_found', __( 'Layout not found.', 'layout-now' ), array( 'status' => 404 ) );
		}

		$data = array( 'ID' => $post->ID );
		if ( null !== $title ) {
			$title              = sanitize_text_field( $title );
			$data['post_title'] = '' !== $title ? $title : __( 'Untitled layout', 'layout-now' );
		}
		if ( null !== $config ) {
			$data['meta_input'] = array(
				self::META => wp_json_encode( $this->schema->normalize( $config ) ),
			);
		}

		// Always bump the modified date so the list reflects the latest save.
		$result = wp_update_post( wp_slash( $data ), true );

		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Duplicates a layout.
	 *
	 * @param int $id Post ID.
	 * @return int|WP_Error New post ID.
	 */
	public function duplicate( $id ) {
		$layout = $this->get( $id );
		if ( ! $layout ) {
			return new WP_Error( 'layout_now_not_found', __( 'Layout not found.', 'layout-now' ), array( 'status' => 404 ) );
		}

		/* translators: %s: original layout title. */
		return $this->create( sprintf( __( '%s (copy)', 'layout-now' ), $layout['title'] ), $layout['config'] );
	}

	/**
	 * Permanently deletes a layout.
	 *
	 * @param int $id Post ID.
	 * @return bool
	 */
	public function delete( $id ) {
		$post = $this->post( $id );
		return $post ? (bool) wp_delete_post( $post->ID, true ) : false;
	}

	/**
	 * Loads a published layout post.
	 *
	 * @param int $id Post ID.
	 * @return WP_Post|null
	 */
	private function post( $id ) {
		$post = get_post( absint( $id ) );
		if ( ! $post || PostType::NAME !== $post->post_type || 'publish' !== $post->post_status ) {
			return null;
		}
		return $post;
	}

	/**
	 * Reads and normalizes the stored config.
	 *
	 * @param int $id Post ID.
	 * @return array
	 */
	private function read_config( $id ) {
		$raw    = get_post_meta( $id, self::META, true );
		$config = is_string( $raw ) ? json_decode( $raw, true ) : null;
		return $this->schema->normalize( is_array( $config ) ? $config : array() );
	}

	/**
	 * Shapes a post for API responses.
	 *
	 * @param WP_Post $post Post.
	 * @return array
	 */
	private function to_array( WP_Post $post ) {
		return array(
			'id'        => $post->ID,
			'title'     => $post->post_title,
			'modified'  => gmdate( 'c', (int) strtotime( $post->post_modified_gmt . ' UTC' ) ),
			'shortcode' => sprintf( '[%s id="%d"]', Shortcode::TAG, $post->ID ),
			'config'    => $this->read_config( $post->ID ),
		);
	}
}
