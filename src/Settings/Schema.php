<?php
/**
 * Single source of truth for layout settings: defaults, sanitization and admin export.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Settings;

use LayoutNow\Registry;
use const LayoutNow\VERSION;

defined( 'ABSPATH' ) || exit;

/**
 * Config shape:
 *
 *     version        int
 *     layout         string  Active layout ID.
 *     layout_options array   Options keyed by layout ID (kept when switching layouts).
 *     source         string  Active source ID.
 *     source_options array   Options keyed by source ID.
 *     style          array   Skin and global style controls.
 *
 * Field definition keys: type (select|toggle|number|color|terms|class|text), default, label,
 * choices (select), min/max/step (number), taxonomy (terms), live (true when the admin preview
 * can apply the change without a server re-render).
 */
final class Schema {

	const VERSION = 1;

	/**
	 * Registry.
	 *
	 * @var Registry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param Registry $registry Registry.
	 */
	public function __construct( Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Global style fields.
	 *
	 * @return array
	 */
	public function style_fields() {
		$skins = array();
		foreach ( $this->registry->skins() as $id => $skin ) {
			$skins[ $id ] = $skin->label;
		}

		$auto = __( 'Skin default', 'layout-now' );

		$fields = array(
			'skin'           => array(
				'type'    => 'select',
				'label'   => __( 'Skin', 'layout-now' ),
				'default' => isset( $skins['clean'] ) ? 'clean' : (string) key( $skins ),
				'choices' => $skins,
				'live'    => true,
			),
			'accent'         => array(
				'type'    => 'color',
				'label'   => __( 'Accent color', 'layout-now' ),
				'default' => '',
				'live'    => true,
			),
			'mode'           => array(
				'type'    => 'select',
				'label'   => __( 'Color mode', 'layout-now' ),
				'default' => 'light',
				'choices' => array(
					'light' => __( 'Light', 'layout-now' ),
					'dark'  => __( 'Dark', 'layout-now' ),
					'auto'  => __( 'Auto', 'layout-now' ),
				),
				'live'    => true,
			),
			'align'          => array(
				'type'    => 'select',
				'label'   => __( 'Width', 'layout-now' ),
				'default' => 'content',
				'choices' => array(
					'content' => __( 'Content', 'layout-now' ),
					'wide'    => __( 'Wide', 'layout-now' ),
					'full'    => __( 'Full', 'layout-now' ),
				),
			),
			'radius'         => array(
				'type'    => 'select',
				'label'   => __( 'Corners', 'layout-now' ),
				'default' => 'auto',
				'choices' => array(
					'auto'  => $auto,
					'sharp' => __( 'Sharp', 'layout-now' ),
					'soft'  => __( 'Soft', 'layout-now' ),
					'round' => __( 'Round', 'layout-now' ),
				),
				'live'    => true,
			),
			'spacing'        => array(
				'type'    => 'select',
				'label'   => __( 'Spacing', 'layout-now' ),
				'default' => 'auto',
				'choices' => array(
					'auto'        => $auto,
					'compact'     => __( 'Compact', 'layout-now' ),
					'comfortable' => __( 'Comfortable', 'layout-now' ),
					'airy'        => __( 'Airy', 'layout-now' ),
				),
				'live'    => true,
			),
			'hover'          => array(
				'type'    => 'select',
				'label'   => __( 'Hover effect', 'layout-now' ),
				'default' => 'auto',
				'choices' => array(
					'auto' => $auto,
					'none' => __( 'None', 'layout-now' ),
					'lift' => __( 'Lift', 'layout-now' ),
					'zoom' => __( 'Zoom', 'layout-now' ),
					'glow' => __( 'Glow', 'layout-now' ),
				),
				'live'    => true,
			),
			'ratio'          => array(
				'type'    => 'select',
				'label'   => __( 'Image ratio', 'layout-now' ),
				'default' => 'landscape',
				'choices' => array(
					'square'    => '1:1',
					'landscape' => '4:3',
					'wide'      => '16:9',
					'portrait'  => '3:4',
				),
				'live'    => true,
			),
			'title_size'     => array(
				'type'    => 'select',
				'label'   => __( 'Title size', 'layout-now' ),
				'default' => 'm',
				'choices' => array(
					's' => __( 'S', 'layout-now' ),
					'm' => __( 'M', 'layout-now' ),
					'l' => __( 'L', 'layout-now' ),
				),
				'live'    => true,
			),
			'font'           => array(
				'type'    => 'select',
				'label'   => __( 'Font', 'layout-now' ),
				'default' => 'skin',
				'choices' => array(
					'skin'    => __( 'Skin font', 'layout-now' ),
					'inherit' => __( 'Inherit theme font', 'layout-now' ),
				),
				'live'    => true,
			),
			'show_image'     => array(
				'type'    => 'toggle',
				'label'   => __( 'Image', 'layout-now' ),
				'default' => true,
			),
			'show_badge'     => array(
				'type'    => 'toggle',
				'label'   => __( 'Category badge', 'layout-now' ),
				'default' => true,
			),
			'show_excerpt'   => array(
				'type'    => 'toggle',
				'label'   => __( 'Excerpt', 'layout-now' ),
				'default' => true,
			),
			'excerpt_length' => array(
				'type'    => 'number',
				'label'   => __( 'Excerpt length (words)', 'layout-now' ),
				'default' => 18,
				'min'     => 5,
				'max'     => 60,
				'step'    => 1,
			),
			'show_date'      => array(
				'type'    => 'toggle',
				'label'   => __( 'Date', 'layout-now' ),
				'default' => true,
			),
			'show_author'    => array(
				'type'    => 'toggle',
				'label'   => __( 'Author', 'layout-now' ),
				'default' => false,
			),
			'show_more'      => array(
				'type'    => 'toggle',
				'label'   => __( 'Read more', 'layout-now' ),
				'default' => false,
			),
			'max_width'      => array(
				'type'    => 'number',
				'label'   => __( 'Max width (px, 0 = full)', 'layout-now' ),
				'default' => 0,
				'min'     => 0,
				'max'     => 2400,
				'step'    => 10,
				'live'    => true,
			),
			'heading'        => array(
				'type'    => 'select',
				'label'   => __( 'Card title tag', 'layout-now' ),
				'default' => 'h3',
				'choices' => array(
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
				),
			),
			'css_class'      => array(
				'type'    => 'class',
				'label'   => __( 'Extra CSS class', 'layout-now' ),
				'default' => '',
			),
		);

		/**
		 * Filters the global style fields.
		 *
		 * @param array $fields Field definitions.
		 */
		return apply_filters( 'layout_now_style_fields', $fields );
	}

	/**
	 * Full default config.
	 *
	 * @return array
	 */
	public function defaults() {
		return $this->normalize( array() );
	}

	/**
	 * Sanitizes a raw config and fills in every missing value.
	 *
	 * @param mixed $raw Raw config (e.g. decoded JSON or a REST payload).
	 * @return array
	 */
	public function normalize( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();

		$layouts = $this->registry->layouts();
		$sources = $this->registry->sources();

		// is_string() first: an array or object here would make isset() throw on PHP 8.
		$layout = isset( $raw['layout'] ) && is_string( $raw['layout'] ) && isset( $layouts[ $raw['layout'] ] ) ? $raw['layout'] : (string) key( $layouts );
		$source = isset( $raw['source'] ) && is_string( $raw['source'] ) && isset( $sources[ $raw['source'] ] ) ? $raw['source'] : (string) key( $sources );

		return array(
			'version'        => self::VERSION,
			'layout'         => $layout,
			'layout_options' => $this->normalize_group( $layouts, $layout, isset( $raw['layout_options'] ) ? $raw['layout_options'] : array() ),
			'source'         => $source,
			'source_options' => $this->normalize_group( $sources, $source, isset( $raw['source_options'] ) ? $raw['source_options'] : array() ),
			'style'          => self::sanitize_fields( $this->style_fields(), isset( $raw['style'] ) ? $raw['style'] : array() ),
		);
	}

	/**
	 * Everything the admin app needs to build its controls.
	 *
	 * @return array
	 */
	public function export() {
		$describe = static function ( $item ) {
			$fields = $item->get_fields();
			return array(
				'id'       => $item->get_id(),
				'label'    => $item->get_label(),
				'fields'   => $fields,
				'defaults' => self::sanitize_fields( $fields, array() ),
			);
		};

		$layouts = array();
		foreach ( $this->registry->layouts() as $layout ) {
			$entry                  = $describe( $layout );
			$entry['style']         = $layout->get_style_url() ? add_query_arg( 'ver', VERSION, $layout->get_style_url() ) : '';
			$entry['script']        = $layout->get_script_url() ? add_query_arg( 'ver', VERSION, $layout->get_script_url() ) : '';
			$entry['ignored_style'] = array_values( $layout->get_ignored_style_fields() );
			$entry['fixed_count']   = null !== $layout->get_item_limit( $entry['defaults'] );
			$layouts[]              = $entry;
		}

		$skins = array();
		foreach ( $this->registry->skins() as $skin ) {
			$entry          = $skin->to_array();
			$entry['style'] = $skin->style ? add_query_arg( 'ver', VERSION, $skin->style ) : '';
			$skins[]        = $entry;
		}

		return array(
			'version'  => self::VERSION,
			'layouts'  => $layouts,
			'sources'  => array_values( array_map( $describe, $this->registry->sources() ) ),
			'skins'    => $skins,
			'style'    => $this->style_fields(),
			'presets'  => $this->presets(),
			'defaults' => $this->defaults(),
		);
	}

	/**
	 * One-click style presets: curated combinations of style values.
	 *
	 * @return array[]
	 */
	public function presets() {
		$presets = array(
			array(
				'id'     => 'crisp',
				'label'  => __( 'Crisp', 'layout-now' ),
				'values' => array(
					'skin'    => 'clean',
					'accent'  => '',
					'mode'    => 'light',
					'radius'  => 'auto',
					'spacing' => 'auto',
					'hover'   => 'auto',
				),
			),
			array(
				'id'     => 'mono',
				'label'  => __( 'Mono', 'layout-now' ),
				'values' => array(
					'skin'    => 'clean',
					'accent'  => '#18181b',
					'mode'    => 'light',
					'radius'  => 'sharp',
					'spacing' => 'compact',
					'hover'   => 'lift',
				),
			),
			array(
				'id'     => 'ocean',
				'label'  => __( 'Ocean', 'layout-now' ),
				'values' => array(
					'skin'    => 'clean',
					'accent'  => '#0284c7',
					'mode'    => 'light',
					'radius'  => 'round',
					'spacing' => 'airy',
					'hover'   => 'zoom',
				),
			),
			array(
				'id'     => 'night-shift',
				'label'  => __( 'Night shift', 'layout-now' ),
				'values' => array(
					'skin'    => 'midnight',
					'accent'  => '',
					'radius'  => 'auto',
					'spacing' => 'auto',
					'hover'   => 'auto',
				),
			),
			array(
				'id'     => 'magazine',
				'label'  => __( 'Magazine', 'layout-now' ),
				'values' => array(
					'skin'    => 'editorial',
					'accent'  => '',
					'mode'    => 'light',
					'radius'  => 'auto',
					'spacing' => 'auto',
					'hover'   => 'auto',
				),
			),
			array(
				'id'     => 'showcase',
				'label'  => __( 'Showcase', 'layout-now' ),
				'values' => array(
					'skin'    => 'cinematic',
					'accent'  => '',
					'radius'  => 'auto',
					'spacing' => 'auto',
					'hover'   => 'auto',
				),
			),
		);

		/**
		 * Filters the one-click style presets.
		 *
		 * @param array[] $presets Presets with id, label and values (style field => value).
		 */
		return apply_filters( 'layout_now_presets', $presets );
	}

	/**
	 * Sanitizes option groups keyed by extension ID. Options of inactive extensions are kept so
	 * switching back restores them; options of unregistered extensions are dropped.
	 *
	 * @param array  $registered Extensions keyed by ID.
	 * @param string $active     Active extension ID.
	 * @param mixed  $raw        Raw options keyed by extension ID.
	 * @return array
	 */
	private function normalize_group( array $registered, $active, $raw ) {
		$raw   = is_array( $raw ) ? $raw : array();
		$clean = array();

		foreach ( $registered as $id => $extension ) {
			if ( $id === $active || isset( $raw[ $id ] ) ) {
				$clean[ $id ] = self::sanitize_fields( $extension->get_fields(), isset( $raw[ $id ] ) ? $raw[ $id ] : array() );
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes a set of values against field definitions.
	 *
	 * @param array $fields Field definitions keyed by name.
	 * @param mixed $input  Raw values.
	 * @return array
	 */
	public static function sanitize_fields( array $fields, $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		foreach ( $fields as $key => $field ) {
			$clean[ $key ] = array_key_exists( $key, $input )
				? self::sanitize_value( $field, $input[ $key ] )
				: self::field_default( $field );
		}

		return $clean;
	}

	/**
	 * Sanitizes one value. Invalid values fall back to the field default.
	 *
	 * @param array $field Field definition.
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	public static function sanitize_value( array $field, $value ) {
		$default = self::field_default( $field );

		switch ( isset( $field['type'] ) ? $field['type'] : 'text' ) {
			case 'select':
				$value = is_scalar( $value ) ? (string) $value : '';
				return isset( $field['choices'] ) && array_key_exists( $value, $field['choices'] ) ? $value : $default;

			case 'toggle':
				return rest_sanitize_boolean( $value );

			case 'number':
				if ( ! is_numeric( $value ) ) {
					return $default;
				}
				$value = (int) $value;
				if ( isset( $field['min'] ) ) {
					$value = max( (int) $field['min'], $value );
				}
				if ( isset( $field['max'] ) ) {
					$value = min( (int) $field['max'], $value );
				}
				return $value;

			case 'color':
				// Validated here rather than with sanitize_hex_color(), which older WordPress only loads with the Customizer.
				$value = is_string( $value ) ? trim( $value ) : '';
				return preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value ) ? strtolower( $value ) : '';

			case 'terms':
				$ids = array_filter( array_map( 'absint', (array) $value ) );
				return array_slice( array_values( array_unique( $ids ) ), 0, 100 );

			case 'class':
				$classes = preg_split( '/\s+/', is_string( $value ) ? $value : '', -1, PREG_SPLIT_NO_EMPTY );
				return implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );

			default:
				return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $default;
		}
	}

	/**
	 * Default value of a field.
	 *
	 * @param array $field Field definition.
	 * @return mixed
	 */
	private static function field_default( array $field ) {
		if ( array_key_exists( 'default', $field ) ) {
			return $field['default'];
		}
		return 'terms' === ( isset( $field['type'] ) ? $field['type'] : '' ) ? array() : '';
	}
}
