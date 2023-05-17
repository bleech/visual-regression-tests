<?php

namespace Vrts\Rest_Api;

use WP_Error;
use WP_REST_Request;
use Vrts\Features\Subscription;
use Vrts\Services\Test_Service;

class Rest_Service_Controller {

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
		$this->resource_name = 'service';

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
		add_action( 'wp_ajax_nopriv_vrts_service', [ $this, 'ajax_action' ] );
		add_action( 'wp_ajax_priv_vrts_service', [ $this, 'ajax_action' ] );
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route($this->namespace, $this->resource_name, [
			'methods' => [ 'POST' ],
			'callback' => [ $this, 'service_callback' ],
			'permission_callback' => [ $this, 'get_items_permissions_check' ],
		]);
	}

	/**
	 * Actions for admin-ajax.php
	 */
	public function ajax_action() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- It's ok.
		$data = json_decode( wp_unslash( $_REQUEST['data'] ?? '' ), true );
		$rest_response = $this->perform_action( $data ?? [] );

		// If rest response is WP error, get the status code.
		if ( is_wp_error( $rest_response ) ) {
			$error_data = $rest_response->get_error_data();
			status_header( $error_data['status'] );
			wp_send_json( $rest_response->get_error_message() );
		} else {
			status_header( $rest_response->get_status() );
			wp_send_json( $rest_response->get_data() );
		}
	}

	/**
	 * Gets some data.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function service_callback( WP_REST_Request $request ) {
		$data = $request->get_params();

		return $this->perform_action( $data );
	}

	/**
	 * Perform ajax actions.
	 *
	 * @param array $data Current ajax data.
	 */
	public function perform_action( $data ) {
		if ( ! array_key_exists( 'action', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Action parameter is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		switch ( $data['action'] ) {

			case 'test_updated':
				$response = $this->test_updated_request( $data );
				break;

			case 'subscription_changed':
				$response = $this->subscription_changed_request( $data );
				break;

			default:
				$response = $this->unknown_action_request( $data );
				break;
		}//end switch

		return $response;
	}

	/**
	 * Test updated request
	 *
	 * @param array $data Rest api response body.
	 */
	private function test_updated_request( $data ) {
		if ( ! array_key_exists( 'project_id', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Project id is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		} elseif ( get_option( 'vrts_project_id' ) !== $data['project_id'] ) {
			return new WP_Error( 'error', esc_html__( 'Project id does not match.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		} elseif ( ! array_key_exists( 'test_id', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Test id is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		if ( ! self::verify_signature( $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Signature is not valid.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		};

		$test_service = new Test_Service();
		if ( $test_service->update_test_from_api_data( $data ) ) {

			Subscription::update_available_tests( $data['remaining_credits'], $data['total_credits'], $data['has_subscription'], $data['tier_id'] );

			return rest_ensure_response([
				'message' => 'Action test_updated successful.',
			]);
		}//end if

		return new WP_Error( 'error', esc_html__( 'Test not found.', 'visual-regression-tests' ), [ 'status' => 404 ] );
	}

	/**
	 * Verify signature
	 *
	 * @param array $data Rest api response body.
	 *
	 * @return bool
	 */
	private function verify_signature( $data ) {
		$signature = $data['signature'];
		unset( $data['signature'] );

		$secret = get_option( 'vrts_project_secret' ) || 'verysecret';

		return hash_equals( $signature, hash_hmac( 'sha256', wp_json_encode( $data ), $secret ) );
	}

	/**
	 * Subscription changed request
	 *
	 * @param array $data Rest api response body.
	 */
	private function subscription_changed_request( $data ) {
		// When notified about subscription change from service, update the tests with the new status.
		Subscription::get_latest_status();

		return rest_ensure_response([
			'message' => esc_html__( 'Subscription changed action was successful.', 'visual-regression-tests' ),
		]);
	}

	/**
	 * Unknown action request
	 *
	 * @param string $data Rest api response body.
	 */
	private function unknown_action_request( $data ) {
		return new WP_Error( 'error', esc_html__( 'Unknown action.', 'visual-regression-tests' ), [ 'status' => 403 ] );
	}

	/**
	 * Check permissions for the requets.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_items_permissions_check( WP_REST_Request $request ) {
		return true;
	}

	/**
	 * Sets up the proper HTTP status code for authorization.
	 */
	public function authorization_status_code() {
		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}
