<?php
/**
 * REST API for the admin app.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Rest;

use LayoutNow\LayoutStore;
use LayoutNow\Plugin;
use LayoutNow\Render\Assets;
use LayoutNow\Render\Renderer;
use LayoutNow\Settings\Schema;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Routes under layout-now/v1. Every route requires the layout management capability.
 */
final class Controller {

	const NAMESPACE_V1 = 'layout-now/v1';

	/**
	 * Store.
	 *
	 * @var LayoutStore
	 */
	private $store;

	/**
	 * Schema.
	 *
	 * @var Schema
	 */
	private $schema;

	/**
	 * Renderer.
	 *
	 * @var Renderer
	 */
	private $renderer;

	/**
	 * Assets.
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Constructor.
	 *
	 * @param LayoutStore $store    Store.
	 * @param Schema      $schema   Schema.
	 * @param Renderer    $renderer Renderer.
	 * @param Assets      $assets   Assets.
	 */
	public function __construct( LayoutStore $store, Schema $schema, Renderer $renderer, Assets $assets ) {
		$this->store    = $store;
		$this->schema   = $schema;
		$this->renderer = $renderer;
		$this->assets   = $assets;
	}

	/**
	 * Registers routes.
	 */
	public function register_routes() {
		$permission = array( $this, 'can_manage' );

		$id_arg = array(
			'id' => array(
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
		);

		$write_args = array(
			'title'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'config' => array(
				'type' => 'object',
			),
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/layouts',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_layouts' ),
					'permission_callback' => $permission,
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_layout' ),
					'permission_callback' => $permission,
					'args'                => $write_args,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/layouts/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_layout' ),
					'permission_callback' => $permission,
					'args'                => $id_arg,
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_layout' ),
					'permission_callback' => $permission,
					'args'                => array_merge( $id_arg, $write_args ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_layout' ),
					'permission_callback' => $permission,
					'args'                => $id_arg,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/layouts/(?P<id>\d+)/duplicate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate_layout' ),
				'permission_callback' => $permission,
				'args'                => $id_arg,
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/schema',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_schema' ),
				'permission_callback' => $permission,
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/preview',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'preview' ),
				'permission_callback' => $permission,
				'args'                => array(
					'config' => array(
						'type'     => 'object',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * Permission callback.
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( Plugin::capability() );
	}

	/**
	 * GET /layouts
	 *
	 * @return WP_REST_Response
	 */
	public function list_layouts() {
		return rest_ensure_response( $this->store->all() );
	}

	/**
	 * GET /layouts/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_layout( WP_REST_Request $request ) {
		$layout = $this->store->get( $request['id'] );
		return $layout ? rest_ensure_response( $layout ) : $this->not_found();
	}

	/**
	 * POST /layouts
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_layout( WP_REST_Request $request ) {
		$id = $this->store->create(
			(string) $request->get_param( 'title' ),
			(array) $request->get_param( 'config' )
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$response = rest_ensure_response( $this->store->get( $id ) );
		$response->set_status( 201 );
		return $response;
	}

	/**
	 * PUT/PATCH /layouts/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_layout( WP_REST_Request $request ) {
		$config = $request->get_param( 'config' );
		$result = $this->store->update(
			$request['id'],
			$request->has_param( 'title' ) ? (string) $request->get_param( 'title' ) : null,
			is_array( $config ) ? $config : null
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return rest_ensure_response( $this->store->get( $request['id'] ) );
	}

	/**
	 * DELETE /layouts/{id}
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_layout( WP_REST_Request $request ) {
		if ( ! $this->store->delete( $request['id'] ) ) {
			return $this->not_found();
		}
		return rest_ensure_response(
			array(
				'deleted' => true,
				'id'      => (int) $request['id'],
			)
		);
	}

	/**
	 * POST /layouts/{id}/duplicate
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate_layout( WP_REST_Request $request ) {
		$id = $this->store->duplicate( $request['id'] );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$response = rest_ensure_response( $this->store->get( $id ) );
		$response->set_status( 201 );
		return $response;
	}

	/**
	 * GET /schema
	 *
	 * @return WP_REST_Response
	 */
	public function get_schema() {
		return rest_ensure_response( $this->schema->export() );
	}

	/**
	 * POST /preview — renders real frontend markup plus the asset URLs the iframe must load.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function preview( WP_REST_Request $request ) {
		$config = $this->schema->normalize( (array) $request->get_param( 'config' ) );
		$layout = Plugin::instance()->registry()->layout( $config['layout'] );
		$skin   = $this->renderer->skin( $config['style']['skin'] );

		return rest_ensure_response(
			array(
				'html'   => $this->renderer->render( $config, array( 'preview' => true ) ),
				'assets' => $layout && $skin ? $this->assets->urls( $layout, $skin ) : array(),
				'config' => $config,
			)
		);
	}

	/**
	 * 404 error.
	 *
	 * @return WP_Error
	 */
	private function not_found() {
		return new WP_Error( 'layout_now_not_found', __( 'Layout not found.', 'layout-now' ), array( 'status' => 404 ) );
	}
}
