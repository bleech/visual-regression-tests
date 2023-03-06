<?php

namespace Vrts\Rest_Api;

use Vrts\Features\Subscription;
use WP_REST_Request;
use WP_Error;
use WP_REST_Server;
use Vrts\Models\Test;

class Rest_Tests_Controller {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'vrts/v1';
		$this->resource_name = 'tests';

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route($this->namespace, $this->resource_name, [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [ $this, 'tests_remaining_total_callback' ],
			'permission_callback' => '__return_true',
		]);

		register_rest_route($this->namespace, $this->resource_name . '/post/(?P<post_id>\d+)', [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [ $this, 'tests_callback' ],
			'permission_callback' => '__return_true',
		]);
	}

	/**
	 * Gets some tests data.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function tests_callback( WP_REST_Request $request ) {
		$data = $request->get_params();
		$post_id = $data['post_id'] ?? 0;

		if ( 0 !== $post_id ) {
			return rest_ensure_response([
				'test_id' => Test::get_item_id( $post_id ),
			], 200);
		} else {
			$error = new WP_Error(
				'rest_not_found',
				esc_html__( 'The test does not exist.', 'visual-regression-tests' ),
			[ 'status' => 404 ] );
			return rest_ensure_response( $error );
		}
	}

	/**
	 * Get remaining and total tests.
	 */
	public function tests_remaining_total_callback() {

		return rest_ensure_response([
			'remaining_tests' => Subscription::get_remaining_tests(),
			'total_tests' => Subscription::get_total_tests(),
		], 200);
	}
}
