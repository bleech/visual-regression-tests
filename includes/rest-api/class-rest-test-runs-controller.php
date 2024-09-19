<?php

namespace Vrts\Rest_Api;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use Vrts\Models\Alert;
use Vrts\Models\Test_Run;

class Rest_Test_Runs_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'vrts/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'test-runs';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Registers the routes for alerts.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/read-status', [
			'methods' => [ WP_REST_Server::CREATABLE, WP_REST_Server::DELETABLE ],
			'callback' => [ $this, 'read_status_callback' ],
			'permission_callback' => [ $this, 'get_items_permissions_check' ],
		] );
	}

	/**
	 * Checks if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Read status.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function read_status_callback( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		$alert = Test_Run::get_item( $id );

		if ( ! $alert ) {
			return new WP_Error( 'error', esc_html__( 'Run not found.', 'visual-regression-tests' ), [ 'status' => 404 ] );
		}

		$should_mark_as_read = $request->get_method() === WP_REST_Server::CREATABLE ? 1 : 0;

		Alert::set_read_status_by_test_run( $id, $should_mark_as_read );

		// return rest_ensure_response( $response );
		return new WP_REST_Response( true, 200 );
	}
}
