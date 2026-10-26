<?php
/**
 * PSR-4 autoloader for the LayoutNow namespace.
 *
 * @package LayoutNow
 */

namespace LayoutNow;

defined( 'ABSPATH' ) || exit;

/**
 * Maps LayoutNow\Foo\Bar to src/Foo/Bar.php.
 */
final class Autoloader {

	/**
	 * Registers the autoloader.
	 *
	 * @param string $base_dir Absolute path to the src directory.
	 */
	public static function register( $base_dir ) {
		spl_autoload_register(
			static function ( $class_name ) use ( $base_dir ) {
				$prefix = __NAMESPACE__ . '\\';

				if ( 0 !== strpos( $class_name, $prefix ) ) {
					return;
				}

				$relative = substr( $class_name, strlen( $prefix ) );
				$file     = $base_dir . '/' . str_replace( '\\', '/', $relative ) . '.php';

				if ( is_readable( $file ) ) {
					require_once $file;
				}
			}
		);
	}
}
