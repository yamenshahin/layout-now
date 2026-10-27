<?php
/**
 * Conditional frontend asset loading.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Render;

use LayoutNow\Layouts\LayoutInterface;
use LayoutNow\LayoutStore;
use LayoutNow\Plugin;
use LayoutNow\Registry;
use LayoutNow\Shortcode;
use LayoutNow\Skins\Skin;
use const LayoutNow\VERSION;

defined( 'ABSPATH' ) || exit;

/**
 * Registers every asset but only enqueues what a page actually renders.
 */
final class Assets {

	const BASE = 'layout-now-base';

	/**
	 * Registry.
	 *
	 * @var Registry
	 */
	private $registry;

	/**
	 * Layout store.
	 *
	 * @var LayoutStore
	 */
	private $store;

	/**
	 * Whether handles are registered.
	 *
	 * @var bool
	 */
	private $registered = false;

	/**
	 * Constructor.
	 *
	 * @param Registry    $registry Registry.
	 * @param LayoutStore $store    Layout store.
	 */
	public function __construct( Registry $registry, LayoutStore $store ) {
		$this->registry = $registry;
		$this->store    = $store;
	}

	/**
	 * Registers all handles. Safe to call more than once.
	 */
	public function register() {
		if ( $this->registered ) {
			return;
		}
		$this->registered = true;

		wp_register_style( self::BASE, Plugin::url( 'assets/css/base.css' ), array(), VERSION );

		foreach ( $this->registry->layouts() as $id => $layout ) {
			if ( $layout->get_style_url() ) {
				wp_register_style( $this->handle( 'layout', $id ), $layout->get_style_url(), array( self::BASE ), VERSION );
			}
			if ( $layout->get_script_url() ) {
				wp_register_script(
					$this->handle( 'layout', $id ),
					$layout->get_script_url(),
					array(),
					VERSION,
					array(
						'in_footer' => true,
						'strategy'  => 'defer',
					)
				);
			}
		}

		foreach ( $this->registry->skins() as $id => $skin ) {
			if ( $skin->style ) {
				wp_register_style( $this->handle( 'skin', $id ), $skin->style, array( self::BASE ), VERSION );
			}
		}
	}

	/**
	 * Enqueues assets for one layout + skin combination.
	 *
	 * @param LayoutInterface $layout Layout.
	 * @param Skin            $skin   Skin.
	 */
	public function enqueue( LayoutInterface $layout, Skin $skin ) {
		$this->register();

		wp_enqueue_style( self::BASE );

		$layout_handle = $this->handle( 'layout', $layout->get_id() );
		if ( wp_style_is( $layout_handle, 'registered' ) ) {
			wp_enqueue_style( $layout_handle );
		}
		if ( wp_script_is( $layout_handle, 'registered' ) ) {
			wp_enqueue_script( $layout_handle );
		}

		$skin_handle = $this->handle( 'skin', $skin->id );
		if ( wp_style_is( $skin_handle, 'registered' ) ) {
			wp_enqueue_style( $skin_handle );
		}
	}

	/**
	 * Enqueues only the base stylesheet (editor notices shown outside the preview).
	 */
	public function enqueue_base() {
		$this->register();
		wp_enqueue_style( self::BASE );
	}

	/**
	 * Asset URLs for one combination, used by the admin preview iframe.
	 *
	 * @param LayoutInterface $layout Layout.
	 * @param Skin            $skin   Skin.
	 * @return array { styles: string[], scripts: string[] }
	 */
	public function urls( LayoutInterface $layout, Skin $skin ) {
		$styles = array( Plugin::url( 'assets/css/base.css' ) );
		if ( $layout->get_style_url() ) {
			$styles[] = $layout->get_style_url();
		}
		if ( $skin->style ) {
			$styles[] = $skin->style;
		}

		$add_version = static function ( $url ) {
			return add_query_arg( 'ver', VERSION, $url );
		};

		return array(
			'styles'  => array_map( $add_version, $styles ),
			'scripts' => $layout->get_script_url() ? array( $add_version( $layout->get_script_url() ) ) : array(),
		);
	}

	/**
	 * On singular views, enqueues assets in <head> for shortcodes found in the content, avoiding a
	 * flash of unstyled content. Shortcodes rendered elsewhere (widgets, templates) still enqueue
	 * themselves at render time and print in the footer.
	 */
	public function enqueue_for_current_post() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post || ! has_shortcode( $post->post_content, Shortcode::TAG ) ) {
			return;
		}

		if ( ! preg_match_all( '/' . get_shortcode_regex( array( Shortcode::TAG ) ) . '/', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			return;
		}

		foreach ( $matches as $match ) {
			$atts   = shortcode_parse_atts( $match[3] );
			$config = is_array( $atts ) && ! empty( $atts['id'] ) ? $this->store->get_config( absint( $atts['id'] ) ) : null;
			if ( ! $config ) {
				continue;
			}

			$layout = $this->registry->layout( $config['layout'] );
			$skin   = $this->registry->skin( $config['style']['skin'] );
			if ( $layout && $skin ) {
				$this->enqueue( $layout, $skin );
			}
		}
	}

	/**
	 * Handle name.
	 *
	 * @param string $type layout|skin.
	 * @param string $id   Extension ID.
	 * @return string
	 */
	private function handle( $type, $id ) {
		return 'layout-now-' . $type . '-' . $id;
	}
}
