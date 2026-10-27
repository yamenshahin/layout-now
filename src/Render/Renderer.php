<?php
/**
 * Turns a config into markup. Shared by the shortcode, the admin preview and future blocks.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Render;

use LayoutNow\Layouts\LayoutInterface;
use LayoutNow\LayoutStore;
use LayoutNow\Plugin;
use LayoutNow\Registry;
use LayoutNow\Settings\Schema;
use LayoutNow\Skins\Skin;

defined( 'ABSPATH' ) || exit;

/**
 * Source → Items → Layout template → Skin attributes → HTML.
 */
final class Renderer {

	/**
	 * Registry.
	 *
	 * @var Registry
	 */
	private $registry;

	/**
	 * Schema.
	 *
	 * @var Schema
	 */
	private $schema;

	/**
	 * Store.
	 *
	 * @var LayoutStore
	 */
	private $store;

	/**
	 * Assets.
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Item cache.
	 *
	 * @var ItemCache
	 */
	private $cache;

	/**
	 * DOM IDs used so far on this request.
	 *
	 * @var int[]
	 */
	private $ids = array();

	/**
	 * Constructor.
	 *
	 * @param Registry    $registry Registry.
	 * @param Schema      $schema   Schema.
	 * @param LayoutStore $store    Store.
	 * @param Assets      $assets   Assets.
	 * @param ItemCache   $cache    Item cache.
	 */
	public function __construct( Registry $registry, Schema $schema, LayoutStore $store, Assets $assets, ItemCache $cache ) {
		$this->registry = $registry;
		$this->schema   = $schema;
		$this->store    = $store;
		$this->assets   = $assets;
		$this->cache    = $cache;
	}

	/**
	 * Renders a saved layout.
	 *
	 * @param int $id Layout post ID.
	 * @return string
	 */
	public function render_saved( $id ) {
		$config = $id ? $this->store->get_config( $id ) : null;
		if ( ! $config ) {
			return $this->notice( __( 'Layout Now: this layout no longer exists.', 'layout-now' ) );
		}
		return $this->render(
			$config,
			array(
				'id'    => 'lnow-' . $id,
				'label' => get_the_title( $id ),
			)
		);
	}

	/**
	 * Renders a config.
	 *
	 * @param array $config Raw or normalized config.
	 * @param array $args {
	 *     Render arguments.
	 *
	 *     @type string $id      Base DOM ID.
	 *     @type string $label   Accessible name for the layout region (defaults to "Posts").
	 *     @type bool   $preview Admin preview: skips enqueueing and current-post context.
	 * }
	 * @return string
	 */
	public function render( array $config, array $args = array() ) {
		$args   = wp_parse_args(
			$args,
			array(
				'id'      => 'lnow',
				'label'   => '',
				'preview' => false,
			)
		);
		$config = $this->schema->normalize( $config );

		$layout = $this->registry->layout( $config['layout'] );
		$source = $this->registry->source( $config['source'] );
		$skin   = $this->skin( $config['style']['skin'] );
		if ( ! $layout || ! $source || ! $skin ) {
			return '';
		}

		$layout_options = $config['layout_options'][ $layout->get_id() ];
		$context        = array(
			'limit'      => $layout->get_item_limit( $layout_options ),
			'current_id' => $args['preview'] ? 0 : $this->current_id(),
		);

		$items = $this->cache->get( $source, $config['source_options'][ $source->get_id() ], $context );

		if ( ! $items ) {
			return $this->notice( __( 'Layout Now: no posts match this layout\'s content settings yet.', 'layout-now' ), $args['preview'] );
		}

		if ( ! $args['preview'] ) {
			$this->assets->enqueue( $layout, $skin );
		}

		$html = $layout->render( $items, new View( $config['style'], $layout_options, wp_strip_all_tags( (string) $args['label'] ) ) );

		return sprintf(
			'<div%s>%s</div>',
			$this->attributes( $this->unique_id( $args['id'] ), $config['style'], $layout, $skin ),
			$html
		);
	}

	/**
	 * Resolves the skin, falling back to the first registered one.
	 *
	 * @param string $id Skin ID.
	 * @return Skin|null
	 */
	public function skin( $id ) {
		$skin = $this->registry->skin( $id );
		if ( ! $skin ) {
			$skins = $this->registry->skins();
			$skin  = $skins ? reset( $skins ) : null;
		}
		return $skin;
	}

	/**
	 * Root element attributes. Stepped controls become data attributes the CSS maps to tokens;
	 * "Skin default" values are resolved here so the CSS only deals with concrete steps.
	 *
	 * @param string          $id     DOM ID.
	 * @param array           $style  Style settings.
	 * @param LayoutInterface $layout Layout.
	 * @param Skin            $skin   Skin.
	 * @return string
	 */
	private function attributes( $id, array $style, LayoutInterface $layout, Skin $skin ) {
		$resolve = static function ( $key ) use ( $style, $skin ) {
			return 'auto' === $style[ $key ] && isset( $skin->defaults[ $key ] ) ? $skin->defaults[ $key ] : $style[ $key ];
		};

		$attrs = array(
			'id'           => $id,
			'class'        => trim( 'lnow lnow--' . $layout->get_id() . ( 'content' !== $style['align'] ? ' align' . $style['align'] : '' ) . ' ' . $style['css_class'] ),
			'data-skin'    => $skin->id,
			'data-card'    => $skin->card,
			'data-radius'  => $resolve( 'radius' ),
			'data-spacing' => $resolve( 'spacing' ),
			'data-hover'   => $resolve( 'hover' ),
			'data-ratio'   => $style['ratio'],
			'data-title'   => $style['title_size'],
			'data-font'    => $style['font'],
		);

		if ( $skin->modes ) {
			$attrs['data-mode'] = $style['mode'];
		}

		$vars = array();
		if ( $style['accent'] ) {
			$vars[] = '--ln-accent:' . $style['accent'];
			$vars[] = '--ln-on-accent:' . self::contrast_color( $style['accent'] );
		}
		if ( $style['max_width'] > 0 ) {
			$vars[] = '--ln-max:' . (int) $style['max_width'] . 'px';
		}
		if ( $vars ) {
			$attrs['style'] = implode( ';', $vars );
		}

		$out = '';
		foreach ( $attrs as $name => $value ) {
			$out .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
		}
		return $out;
	}

	/**
	 * Black or white, whichever reads better on the given color (WCAG relative luminance).
	 *
	 * @param string $hex Hex color.
	 * @return string
	 */
	public static function contrast_color( $hex ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$channels = array_map(
			static function ( $pair ) {
				$c = hexdec( $pair ) / 255;
				return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
			},
			str_split( $hex, 2 )
		);

		$luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

		return $luminance > 0.179 ? '#111111' : '#ffffff';
	}

	/**
	 * ID of the post being viewed.
	 *
	 * @return int
	 */
	private function current_id() {
		return is_singular() ? (int) get_queried_object_id() : 0;
	}

	/**
	 * Unique DOM ID for this request.
	 *
	 * @param string $base Base ID.
	 * @return string
	 */
	private function unique_id( $base ) {
		$base = sanitize_html_class( $base, 'lnow' );
		if ( ! isset( $this->ids[ $base ] ) ) {
			$this->ids[ $base ] = 1;
			return $base;
		}
		return $base . '-' . ( ++$this->ids[ $base ] );
	}

	/**
	 * Message shown only to people who can manage layouts; visitors get nothing.
	 *
	 * @param string $message Message.
	 * @param bool   $force   Show regardless of capability.
	 * @return string
	 */
	private function notice( $message, $force = false ) {
		if ( ! $force && ! current_user_can( Plugin::capability() ) ) {
			return '';
		}
		if ( ! $force ) {
			// The preview iframe loads base.css itself; on the site the notice needs it enqueued.
			$this->assets->enqueue_base();
		}
		return '<p class="lnow-notice">' . esc_html( $message ) . '</p>';
	}
}
