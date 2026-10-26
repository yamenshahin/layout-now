<?php
/**
 * Helpers available to templates.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Render;

use LayoutNow\Plugin;
use LayoutNow\Sources\Item;

defined( 'ABSPATH' ) || exit;

/**
 * Holds the resolved settings for one render and loads templates.
 */
final class View {

	/**
	 * Style settings.
	 *
	 * @var array
	 */
	private $style;

	/**
	 * Layout options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param array $style   Style settings.
	 * @param array $options Layout options.
	 */
	public function __construct( array $style, array $options ) {
		$this->style   = $style;
		$this->options = $options;
	}

	/**
	 * All layout options.
	 *
	 * @return array
	 */
	public function options() {
		return $this->options;
	}

	/**
	 * One layout option.
	 *
	 * @param string $key      Option key.
	 * @param mixed  $fallback Value when unset.
	 * @return mixed
	 */
	public function option( $key, $fallback = '' ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $fallback;
	}

	/**
	 * Whether a card element is enabled (image, badge, excerpt, date, author, more).
	 *
	 * @param string $part Element name.
	 * @return bool
	 */
	public function show( $part ) {
		return ! empty( $this->style[ 'show_' . $part ] );
	}

	/**
	 * Excerpt trimmed to the configured length.
	 *
	 * @param Item $item Item.
	 * @return string
	 */
	public function excerpt( Item $item ) {
		return wp_trim_words( $item->excerpt, (int) $this->style['excerpt_length'], '…' );
	}

	/**
	 * First letter of the title, for image placeholders.
	 *
	 * @param Item $item Item.
	 * @return string
	 */
	public function initial( Item $item ) {
		$title = trim( $item->title );
		return '' === $title ? '' : mb_strtoupper( mb_substr( $title, 0, 1 ) );
	}

	/**
	 * Image markup. Attachments go through core so srcset, lazy loading and image plugins keep working.
	 *
	 * @param Item   $item  Item.
	 * @param string $size  Registered image size.
	 * @param string $sizes Optional sizes attribute.
	 * @return string
	 */
	public function image( Item $item, $size, $sizes = '' ) {
		$attr = array( 'class' => 'lnow-card__img' );
		if ( '' !== $sizes ) {
			$attr['sizes'] = $sizes;
		}

		if ( $item->image_id ) {
			return (string) wp_get_attachment_image( $item->image_id, $size, false, $attr );
		}

		if ( '' !== $item->image_url ) {
			return sprintf(
				'<img class="lnow-card__img" src="%s" alt="%s" loading="lazy" decoding="async">',
				esc_url( $item->image_url ),
				esc_attr( $item->image_alt )
			);
		}

		return '';
	}

	/**
	 * Prints a card.
	 *
	 * @param Item  $item Item.
	 * @param array $args {
	 *     Card arguments.
	 *
	 *     @type string $class Extra classes.
	 *     @type string $size  Image size.
	 *     @type string $sizes Image sizes attribute.
	 * }
	 */
	public function card( Item $item, array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'class' => '',
				'size'  => 'medium_large',
				'sizes' => '',
			)
		);
		$view = $this;

		include $this->locate( 'card' );
	}

	/**
	 * Renders a template to a string. Templates receive $items, $data and $view.
	 *
	 * @param string $name  Template name relative to templates/, without extension.
	 * @param Item[] $items Items.
	 * @param array  $data  Extra template data.
	 * @return string
	 */
	public function template( $name, array $items, array $data = array() ) {
		$view = $this;

		ob_start();
		include $this->locate( $name );
		return (string) ob_get_clean();
	}

	/**
	 * Resolves a template path. Themes and add-ons can override via the layout_now_template filter.
	 *
	 * @param string $name Template name.
	 * @return string
	 */
	private function locate( $name ) {
		$default = Plugin::path( 'templates/' . $name . '.php' );

		/**
		 * Filters the template file path.
		 *
		 * @param string $path Absolute path.
		 * @param string $name Template name.
		 */
		$path = (string) apply_filters( 'layout_now_template', $default, $name );

		return is_readable( $path ) ? $path : $default;
	}
}
