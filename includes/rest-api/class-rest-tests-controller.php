<?php

namespace Vrts\Rest_Api;

use Vrts\Features\Subscription;
use WP_REST_Request;
use WP_Error;
use WP_REST_Server;
use Vrts\Models\Test;
use Vrts\Services\Test_Service;

class Rest_Tests_Controller {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	private $namespace;
	/**
	 * Resource name.
	 *
	 * @var string
	 */
	private $resource_name;
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

		register_rest_route($this->namespace, $this->resource_name . '/post/(?P<post_id>\d+)', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [ $this, 'create_test_callback' ],
			'permission_callback' => [ $this, 'user_can_create' ],
		]);

		register_rest_route($this->namespace, $this->resource_name . '/post/(?P<post_id>\d+)', [
			'methods' => WP_REST_Server::DELETABLE,
			'callback' => [ $this, 'delete_test_callback' ],
			'permission_callback' => [ $this, 'user_can_create' ],
		]);

		register_rest_route($this->namespace, $this->resource_name . '/post/(?P<post_id>\d+)', [
			'methods' => WP_REST_Server::EDITABLE,
			'callback' => [ $this, 'update_test_callback' ],
			'permission_callback' => [ $this, 'user_can_create' ],
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
			$test = Test::get_item_by_post_id( $post_id );
			if ( ! empty( $test ) ) {
				$test = Test::cast_values( $test );
				return rest_ensure_response( $test, 200 );
			}
		}
		$error = new WP_Error(
			'rest_not_found',
			esc_html__( 'The test does not exist.', 'visual-regression-tests' ),
		[ 'status' => 404 ] );
		return rest_ensure_response( $error );
	}

	/**
	 * Get remaining and total tests.
	 */
	public function tests_remaining_total_callback() {

		return rest_ensure_response([
			'remaining_tests' => (int) Subscription::get_remaining_tests(),
			'total_tests' => (int) Subscription::get_total_tests(),
		], 200);
	}

	/**
	 * Creates a test.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function create_test_callback( WP_REST_Request $request ) {
		$data = $request->get_params();
		$post_id = $data['post_id'] ?? 0;

		if ( 0 !== $post_id ) {
			$service = new Test_Service();
			$test = $service->create_test( $post_id );
			return rest_ensure_response( Test::cast_values( $test ), 201 );
		}
		$error = new WP_Error(
			'rest_create_test_failed',
			esc_html__( 'The test could not be created.', 'visual-regression-tests' ),
		[ 'status' => 400 ] );
		return rest_ensure_response( $error );
	}

	/**
	 * Deletes a test.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function delete_test_callback( WP_REST_Request $request ) {
		$data = $request->get_params();
		$post_id = $data['post_id'] ?? 0;

		if ( 0 !== $post_id ) {
			$test = Test::get_item_by_post_id( $post_id );
			$service = new Test_Service();
			$service->delete_test( (int) $test->id );
			return rest_ensure_response( [], 200 );
		}
		$error = new WP_Error(
			'rest_delete_test_failed',
			esc_html__( 'The test could not be deleted.', 'visual-regression-tests' ),
		[ 'status' => 400 ] );
		return rest_ensure_response( $error );
	}

	/**
	 * Updates a test.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function update_test_callback( WP_REST_Request $request ) {
		$data = $request->get_params();
		$post_id = $data['post_id'] ?? 0;
		$test_id = $data['test_id'] ?? 0;
		$hide_css_selectors = $data['hide_css_selectors'] ?? [];

		if ( 0 !== $post_id && 0 !== $test_id && ! empty( $hide_css_selectors ) ) {
			$service = new Test_Service();
			$updated = $service->update_css_hide_selectors( $test_id, $hide_css_selectors );
			if ( $updated && ! is_wp_error( $updated ) ) {
				$service->resume_test( $post_id );
				$test = Test::get_item_by_post_id( $post_id );
				if ( ! empty( $test ) ) {
					return rest_ensure_response( Test::cast_values( $test ), 200 );
				}
			};
		}
		$error = new WP_Error(
			'rest_update_test_failed',
			esc_html__( 'The test could not be updated.', 'visual-regression-tests' ),
		[ 'status' => 400 ] );
		return rest_ensure_response( $error );
	}

	/**
	 * Checks if a given request has access to create items.
	 *
	 * @return bool True if the request has access to create items, false otherwise.
	 */
	public function user_can_create() {
		return current_user_can( 'manage_options' );
	}
}
