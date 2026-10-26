<?php
/**
 * Admin screen that hosts the React app.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Admin;

use LayoutNow\Plugin;
use LayoutNow\Rest\Controller;
use const LayoutNow\VERSION;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the top-level menu page and loads the admin app on it only.
 */
final class Page {

	const SLUG   = 'layout-now';
	const HANDLE = 'layout-now-admin';

	/**
	 * Page hook suffix.
	 *
	 * @var string
	 */
	private $hook = '';

	/**
	 * Registers hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( \LayoutNow\PLUGIN_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Adds the menu page.
	 */
	public function menu() {
		$this->hook = (string) add_menu_page(
			__( 'Layout Now', 'layout-now' ),
			__( 'Layout Now', 'layout-now' ),
			Plugin::capability(),
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-layout',
			58
		);
	}

	/**
	 * Page markup: a mount point for the app.
	 */
	public function render() {
		echo '<div id="layout-now-app" class="lnow-app-root"></div>';
		echo '<noscript><p>' . esc_html__( 'Layout Now needs JavaScript enabled to edit layouts.', 'layout-now' ) . '</p></noscript>';
	}

	/**
	 * Enqueues the app on our page only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue( $hook ) {
		if ( $hook !== $this->hook ) {
			return;
		}

		$asset_file = Plugin::path( 'admin/build/index.asset.php' );
		if ( ! is_readable( $asset_file ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Layout Now: the admin app has not been built. Run "npm install && npm run build" in the plugin folder.', 'layout-now' ) . '</p></div>';
				}
			);
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script( self::HANDLE, Plugin::url( 'admin/build/index.js' ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( self::HANDLE, Plugin::url( 'admin/build/index.css' ), array( 'wp-components' ), $asset['version'] );
		wp_style_add_data( self::HANDLE, 'rtl', 'replace' );
		wp_set_script_translations( self::HANDLE, 'layout-now' );

		wp_add_inline_script(
			self::HANDLE,
			'window.layoutNowAdmin = ' . wp_json_encode(
				array(
					'namespace' => Controller::NAMESPACE_V1,
					'pageUrl'   => admin_url( 'admin.php?page=' . self::SLUG ),
					'baseStyle' => add_query_arg( 'ver', VERSION, Plugin::url( 'assets/css/base.css' ) ),
					'version'   => VERSION,
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Body class used to give the app the full content area.
	 *
	 * @param string $classes Classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->id === $this->hook ) {
			$classes .= ' lnow-admin-page';
		}
		return $classes;
	}

	/**
	 * "Layouts" link on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ), esc_html__( 'Layouts', 'layout-now' ) )
		);
		return $links;
	}
}
