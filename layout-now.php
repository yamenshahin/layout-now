<?php
/**
 * Plugin Name:       Layout Now
 * Plugin URI:        https://github.com/yamenshahin
 * Description:      Modern post layouts — bento grids, edge-peek carousels, infinite marquees and instant-filter grids. Fast, lightweight and theme-proof.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Yamen Shahin
 * Author URI:        https://github.com/yamenshahin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       layout-now
 *
 * @package LayoutNow
 */

namespace LayoutNow;

defined( 'ABSPATH' ) || exit;

const VERSION     = '0.1.0';
const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/src/Autoloader.php';

Autoloader::register( __DIR__ . '/src' );

/**
 * Plugin instance accessor.
 *
 * @return Plugin
 */
function plugin() {
	return Plugin::instance();
}

plugin()->boot();
