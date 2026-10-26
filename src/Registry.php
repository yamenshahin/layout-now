<?php
/**
 * Registry of data sources, layouts and skins.
 *
 * @package LayoutNow
 */

namespace LayoutNow;

use LayoutNow\Layouts\Bento;
use LayoutNow\Layouts\LayoutInterface;
use LayoutNow\Skins\Skin;
use LayoutNow\Sources\PostsSource;
use LayoutNow\Sources\SourceInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Collects extensions lazily through public filters so add-ons can register their own.
 */
final class Registry {

	/**
	 * Sources keyed by ID.
	 *
	 * @var SourceInterface[]|null
	 */
	private $sources = null;

	/**
	 * Layouts keyed by ID.
	 *
	 * @var LayoutInterface[]|null
	 */
	private $layouts = null;

	/**
	 * Skins keyed by ID.
	 *
	 * @var Skin[]|null
	 */
	private $skins = null;

	/**
	 * Registered data sources.
	 *
	 * @return SourceInterface[]
	 */
	public function sources() {
		if ( null === $this->sources ) {
			/**
			 * Filters the available data sources.
			 *
			 * @param SourceInterface[] $sources Sources keyed by ID.
			 */
			$sources       = apply_filters( 'layout_now_sources', array( 'posts' => new PostsSource() ) );
			$this->sources = $this->collect( $sources, SourceInterface::class );
		}
		return $this->sources;
	}

	/**
	 * Registered layouts.
	 *
	 * @return LayoutInterface[]
	 */
	public function layouts() {
		if ( null === $this->layouts ) {
			/**
			 * Filters the available layouts.
			 *
			 * @param LayoutInterface[] $layouts Layouts keyed by ID.
			 */
			$layouts       = apply_filters( 'layout_now_layouts', array( 'bento' => new Bento() ) );
			$this->layouts = $this->collect( $layouts, LayoutInterface::class );
		}
		return $this->layouts;
	}

	/**
	 * Registered skins.
	 *
	 * @return Skin[]
	 */
	public function skins() {
		if ( null === $this->skins ) {
			/**
			 * Filters the available skins. Entries may be Skin objects or arrays accepted by Skin::from_array().
			 *
			 * @param array $skins Skins keyed by ID.
			 */
			$skins = apply_filters( 'layout_now_skins', $this->builtin_skins() );

			$this->skins = array();
			foreach ( (array) $skins as $skin ) {
				if ( is_array( $skin ) ) {
					$skin = Skin::from_array( $skin );
				}
				if ( $skin instanceof Skin && '' !== $skin->id ) {
					$this->skins[ $skin->id ] = $skin;
				}
			}
		}
		return $this->skins;
	}

	/**
	 * Looks up a source.
	 *
	 * @param string $id Source ID.
	 * @return SourceInterface|null
	 */
	public function source( $id ) {
		$sources = $this->sources();
		return isset( $sources[ $id ] ) ? $sources[ $id ] : null;
	}

	/**
	 * Looks up a layout.
	 *
	 * @param string $id Layout ID.
	 * @return LayoutInterface|null
	 */
	public function layout( $id ) {
		$layouts = $this->layouts();
		return isset( $layouts[ $id ] ) ? $layouts[ $id ] : null;
	}

	/**
	 * Looks up a skin.
	 *
	 * @param string $id Skin ID.
	 * @return Skin|null
	 */
	public function skin( $id ) {
		$skins = $this->skins();
		return isset( $skins[ $id ] ) ? $skins[ $id ] : null;
	}

	/**
	 * Keeps only valid implementations, keyed by their own ID.
	 *
	 * @param mixed  $candidates Filtered value.
	 * @param string $contract   Required interface.
	 * @return array
	 */
	private function collect( $candidates, $contract ) {
		$valid = array();
		foreach ( (array) $candidates as $candidate ) {
			if ( $candidate instanceof $contract ) {
				$valid[ $candidate->get_id() ] = $candidate;
			}
		}
		return $valid;
	}

	/**
	 * Skins shipped with the plugin.
	 *
	 * @return Skin[]
	 */
	private function builtin_skins() {
		$skins = array(
			array(
				'id'       => 'clean',
				'label'    => __( 'Clean', 'layout-now' ),
				'card'     => 'stacked',
				'modes'    => true,
				'defaults' => array(
					'radius'  => 'soft',
					'spacing' => 'comfortable',
					'hover'   => 'lift',
				),
			),
			array(
				'id'       => 'midnight',
				'label'    => __( 'Midnight', 'layout-now' ),
				'card'     => 'stacked',
				'modes'    => false,
				'defaults' => array(
					'radius'  => 'soft',
					'spacing' => 'comfortable',
					'hover'   => 'glow',
				),
			),
			array(
				'id'       => 'editorial',
				'label'    => __( 'Editorial', 'layout-now' ),
				'card'     => 'stacked',
				'modes'    => true,
				'defaults' => array(
					'radius'  => 'sharp',
					'spacing' => 'airy',
					'hover'   => 'zoom',
				),
			),
			array(
				'id'       => 'cinematic',
				'label'    => __( 'Cinematic', 'layout-now' ),
				'card'     => 'overlay',
				'modes'    => false,
				'defaults' => array(
					'radius'  => 'round',
					'spacing' => 'compact',
					'hover'   => 'zoom',
				),
			),
		);

		$built = array();
		foreach ( $skins as $skin ) {
			$skin['style']        = Plugin::url( 'assets/css/skins/' . $skin['id'] . '.css' );
			$built[ $skin['id'] ] = Skin::from_array( $skin );
		}
		return $built;
	}
}
