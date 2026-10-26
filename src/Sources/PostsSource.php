<?php
/**
 * WordPress posts data source.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Sources;

use WP_Post;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Queries standard posts and maps them to Items.
 */
final class PostsSource implements SourceInterface {

	/**
	 * Words kept in the stored excerpt; layouts trim further.
	 */
	const EXCERPT_WORDS = 60;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'posts';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Posts', 'layout-now' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_fields() {
		return array(
			'count'              => array(
				'type'    => 'number',
				'label'   => __( 'Number of posts', 'layout-now' ),
				'default' => 6,
				'min'     => 1,
				'max'     => 48,
				'step'    => 1,
			),
			'offset'             => array(
				'type'    => 'number',
				'label'   => __( 'Skip the first', 'layout-now' ),
				'default' => 0,
				'min'     => 0,
				'max'     => 50,
				'step'    => 1,
			),
			'orderby'            => array(
				'type'    => 'select',
				'label'   => __( 'Order by', 'layout-now' ),
				'default' => 'date',
				'choices' => array(
					'date'          => __( 'Newest', 'layout-now' ),
					'modified'      => __( 'Recently updated', 'layout-now' ),
					'title'         => __( 'Title', 'layout-now' ),
					'comment_count' => __( 'Most commented', 'layout-now' ),
					'rand'          => __( 'Random', 'layout-now' ),
				),
			),
			'order'              => array(
				'type'    => 'select',
				'label'   => __( 'Direction', 'layout-now' ),
				'default' => 'desc',
				'choices' => array(
					'desc' => __( 'Descending', 'layout-now' ),
					'asc'  => __( 'Ascending', 'layout-now' ),
				),
			),
			'categories'         => array(
				'type'     => 'terms',
				'taxonomy' => 'category',
				'label'    => __( 'Only these categories', 'layout-now' ),
				'default'  => array(),
			),
			'tags'               => array(
				'type'     => 'terms',
				'taxonomy' => 'post_tag',
				'label'    => __( 'Only these tags', 'layout-now' ),
				'default'  => array(),
			),
			'exclude_categories' => array(
				'type'     => 'terms',
				'taxonomy' => 'category',
				'label'    => __( 'Exclude categories', 'layout-now' ),
				'default'  => array(),
			),
			'exclude_current'    => array(
				'type'    => 'toggle',
				'label'   => __( 'Exclude the post being viewed', 'layout-now' ),
				'default' => true,
			),
			'sticky_first'       => array(
				'type'    => 'toggle',
				'label'   => __( 'Sticky posts first', 'layout-now' ),
				'default' => false,
			),
			'require_image'      => array(
				'type'    => 'toggle',
				'label'   => __( 'Only posts with a featured image', 'layout-now' ),
				'default' => false,
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_items( array $options, array $context ) {
		$count      = isset( $context['limit'] ) ? (int) $context['limit'] : (int) $options['count'];
		$count      = max( 1, $count );
		$current_id = ! empty( $options['exclude_current'] ) && ! empty( $context['current_id'] ) ? (int) $context['current_id'] : 0;

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			// One extra so the current post can be filtered out without a post__not_in query.
			'posts_per_page'      => $current_id ? $count + 1 : $count,
			'offset'              => (int) $options['offset'],
			'orderby'             => $options['orderby'],
			'order'               => 'asc' === $options['order'] ? 'ASC' : 'DESC',
			'ignore_sticky_posts' => empty( $options['sticky_first'] ),
			'no_found_rows'       => true,
		);

		if ( $options['categories'] ) {
			$args['category__in'] = $options['categories'];
		}
		if ( $options['tags'] ) {
			$args['tag__in'] = $options['tags'];
		}
		if ( $options['exclude_categories'] ) {
			$args['category__not_in'] = $options['exclude_categories'];
		}
		if ( ! empty( $options['require_image'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Single indexed EXISTS check on _thumbnail_id; results are cached.
			$args['meta_query'] = array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			);
		}

		/**
		 * Filters the WP_Query arguments used by the Posts source.
		 *
		 * @param array $args    Query arguments.
		 * @param array $options Source options.
		 * @param array $context Render context.
		 */
		$query = new WP_Query( apply_filters( 'layout_now_posts_query_args', $args, $options, $context ) );

		if ( ! $query->posts ) {
			return array();
		}

		update_post_thumbnail_cache( $query );

		$items = array();
		foreach ( $query->posts as $post ) {
			if ( $post->ID === $current_id ) {
				continue;
			}
			$items[] = $this->to_item( $post );
			if ( count( $items ) >= $count ) {
				break;
			}
		}

		return $items;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_cache_key( array $options, array $context ) {
		if ( 'rand' === $options['orderby'] ) {
			return null;
		}

		return wp_json_encode(
			array(
				$this->get_id(),
				$options,
				isset( $context['limit'] ) ? (int) $context['limit'] : null,
				! empty( $options['exclude_current'] ) && ! empty( $context['current_id'] ) ? (int) $context['current_id'] : 0,
			)
		);
	}

	/**
	 * Maps a post to an Item.
	 *
	 * @param WP_Post $post Post.
	 * @return Item
	 */
	private function to_item( WP_Post $post ) {
		$terms = $this->terms( $post );
		$badge = null;
		foreach ( $terms as $term ) {
			if ( 'category' === $term['taxonomy'] ) {
				$badge = $term;
				break;
			}
		}

		return new Item(
			array(
				'id'        => $post->ID,
				'title'     => wp_strip_all_tags( get_the_title( $post ) ),
				'url'       => get_permalink( $post ),
				'excerpt'   => $this->excerpt( $post ),
				'image_id'  => (int) get_post_thumbnail_id( $post ),
				'timestamp' => (int) get_post_timestamp( $post ),
				'author'    => get_the_author_meta( 'display_name', (int) $post->post_author ),
				'terms'     => $terms,
				'badge'     => $badge,
			)
		);
	}

	/**
	 * Plain-text excerpt without running the_content filters (fast, and immune to theme "read more" markup).
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	private function excerpt( WP_Post $post ) {
		$text = has_excerpt( $post ) ? $post->post_excerpt : strip_shortcodes( excerpt_remove_blocks( $post->post_content ) );
		$text = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, get_bloginfo( 'charset' ) );
		return wp_trim_words( $text, self::EXCERPT_WORDS, '…' );
	}

	/**
	 * Categories first (the first one becomes the badge), then tags. The generic
	 * "Uncategorized" category is skipped.
	 *
	 * @param WP_Post $post Post.
	 * @return array[]
	 */
	private function terms( WP_Post $post ) {
		$out = array();
		foreach ( array( 'category', 'post_tag' ) as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );
			if ( ! is_array( $terms ) ) {
				continue;
			}
			foreach ( $terms as $term ) {
				if ( 'category' === $taxonomy && 'uncategorized' === $term->slug ) {
					continue;
				}
				$out[] = array(
					'id'       => $term->term_id,
					'name'     => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'slug'     => $term->slug,
					'taxonomy' => $term->taxonomy,
				);
			}
		}
		return $out;
	}
}
