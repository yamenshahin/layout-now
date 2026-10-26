<?php
/**
 * Plugin bootstrap and service container.
 *
 * @package LayoutNow
 */

namespace LayoutNow;

use LayoutNow\Render\Assets;
use LayoutNow\Render\ItemCache;
use LayoutNow\Render\Renderer;
use LayoutNow\Rest\Controller;
use LayoutNow\Settings\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Wires services together and registers WordPress hooks.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Services.
	 *
	 * @var Registry
	 */
	private $registry;

	/**
	 * Settings schema.
	 *
	 * @var Schema
	 */
	private $schema;

	/**
	 * Saved layout storage.
	 *
	 * @var LayoutStore
	 */
	private $store;

	/**
	 * Asset manager.
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
	 * Renderer.
	 *
	 * @var Renderer
	 */
	private $renderer;

	/**
	 * Returns the shared instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Capability required to manage layouts.
	 *
	 * @return string
	 */
	public static function capability() {
		return (string) apply_filters( 'layout_now_capability', 'manage_options' );
	}

	/**
	 * Absolute URL to a plugin file.
	 *
	 * @param string $path Path relative to the plugin root.
	 * @return string
	 */
	public static function url( $path ) {
		return plugins_url( $path, PLUGIN_FILE );
	}

	/**
	 * Absolute filesystem path to a plugin file.
	 *
	 * @param string $path Path relative to the plugin root.
	 * @return string
	 */
	public static function path( $path ) {
		return plugin_dir_path( PLUGIN_FILE ) . ltrim( $path, '/' );
	}

	/**
	 * Builds services and registers hooks.
	 */
	public function boot() {
		$this->registry = new Registry();
		$this->schema   = new Schema( $this->registry );
		$this->store    = new LayoutStore( $this->schema );
		$this->assets   = new Assets( $this->registry, $this->store );
		$this->cache    = new ItemCache();
		$this->renderer = new Renderer( $this->registry, $this->schema, $this->store, $this->assets, $this->cache );

		$shortcode  = new Shortcode( $this->renderer );
		$controller = new Controller( $this->store, $this->schema, $this->renderer, $this->assets );

		add_action( 'init', array( PostType::class, 'register' ) );
		add_action( 'init', array( $shortcode, 'register' ) );
		add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this->assets, 'register' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this->assets, 'enqueue_for_current_post' ) );

		$this->cache->hooks();

		if ( is_admin() ) {
			( new Admin\Page() )->hooks();
		}
	}

	/**
	 * Source, layout and skin registry.
	 *
	 * @return Registry
	 */
	public function registry() {
		return $this->registry;
	}

	/**
	 * Saved layout storage.
	 *
	 * @return LayoutStore
	 */
	public function store() {
		return $this->store;
	}

	/**
	 * Renderer.
	 *
	 * @return Renderer
	 */
	public function renderer() {
		return $this->renderer;
	}
}
