<?php

namespace Vrts\Rest_Api;

use WP_Error;
use WP_REST_Request;
use Vrts\Models\Test;
use Vrts\Tables\Tests_Table;
use Vrts\Tables\Alerts_Table;
use Vrts\Features\Email_Notifications;
use Vrts\Features\Service;
use Vrts\Features\Subscription;

class Rest_Service_Controller {
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
			case 'verify':
				$response = $this->verify_service_request( $data );
				break;

			case 'site_created':
				$response = $this->site_created_request( $data );
				break;

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
	 * Verify service request
	 *
	 * @param array $data Rest api response body.
	 */
	private function verify_service_request( $data ) {
		$service_project_id = get_option( 'vrts_project_id' );

		if ( $service_project_id ) {
			return new WP_Error( 'error', esc_html__( 'Project already exists.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		if ( ! array_key_exists( 'token', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Access token is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		update_option( 'vrts_project_token', $data['token'] );

		return rest_ensure_response([
			'create_token' => get_option( 'vrts_create_token' ),
		]);
	}

	/**
	 * Site created request
	 *
	 * @param array $data Rest api response body.
	 */
	private function site_created_request( $data ) {
		$service_project_id = get_option( 'vrts_project_id' );

		if ( $service_project_id ) {
			return new WP_Error( 'error', esc_html__( 'Project already exists.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		if ( ! array_key_exists( 'id', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Project id is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		if ( ! array_key_exists( 'token', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Access token is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}

		update_option( 'vrts_project_token', $data['token'] );
		update_option( 'vrts_project_id', $data['id'] );
		Subscription::update_available_tests( $data['remaining_credits'], $data['total_credits'], $data['has_subscription'] );

		// Add homepage as a test right after the service is linked to plugin.
		Service::add_homepage_test();

		return rest_ensure_response([
			'create_token' => get_option( 'vrts_create_token' ),
		]);
	}

	/**
	 * Test updated request
	 *
	 * @param array $data Rest api response body.
	 */
	private function test_updated_request( $data ) {
		if ( ! array_key_exists( 'test_id', $data ) ) {
			return new WP_Error( 'error', esc_html__( 'Test id is missing.', 'visual-regression-tests' ), [ 'status' => 403 ] );
		}
		global $wpdb;

		$table_alert = Alerts_Table::get_table_name();
		$table_test = Tests_Table::get_table_name();

		$post_id = Test::get_post_id_by_service_test_id( $data['test_id'] );

		if ( $post_id ) {
			if ( array_key_exists( 'is_paused', $data ) && $data['is_paused'] ) {
				if ( $data['comparison']['pixels_diff'] > 0 ) {
					$prepare_alert = [];
					$prepare_alert['post_id'] = $post_id;
					$prepare_alert['screenshot_test_id'] = $data['test_id'];
					$prepare_alert['target_screenshot_url'] = $data['comparison']['screenshot']['image_url'];
					$prepare_alert['target_screenshot_finish_date'] = $data['comparison']['screenshot']['updated_at'];
					$prepare_alert['base_screenshot_url'] = $data['comparison']['base_screenshot']['image_url'];
					$prepare_alert['base_screenshot_finish_date'] = $data['comparison']['base_screenshot']['updated_at'];
					$prepare_alert['comparison_screenshot_url'] = $data['comparison']['image_url'];
					$prepare_alert['differences'] = $data['comparison']['pixels_diff'];

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- It's ok.
					if ( $wpdb->insert( $table_alert, $prepare_alert ) ) {
						$alert_id = $wpdb->insert_id;

						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
						$wpdb->update($table_alert,
							[ 'title' => '#' . $alert_id ],
							[ 'id' => $alert_id ]
						);
					}

					// Update test row with new id foreign key and add latest screenshot.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
					$wpdb->update( $table_test,
						[
							'current_alert_id' => $alert_id,
							'target_screenshot_url' => $data['comparison']['screenshot']['image_url'],
							'snapshot_date' => $data['comparison']['updated_at'],
						],
						[ 'service_test_id' => $data['test_id'] ]
					);

					// Send email only if alert was created.
					if ( $alert_id ) {
						// Send e-mail notification.
						$email_notifications = new Email_Notifications();
						$email_notifications->send_email( $data['comparison']['pixels_diff'], $post_id, $alert_id );
					}
				}//end if
			} elseif ( $data['schedule']['base_screenshot'] ) {
				// Update test row with new id foreign key and add latest screenshot.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
				$wpdb->update( $table_test,
					[
						'target_screenshot_url' => $data['schedule']['base_screenshot']['image_url'],
						'snapshot_date' => $data['schedule']['base_screenshot']['updated_at'],
					],
					[ 'service_test_id' => $data['test_id'] ]
				);
			}//end if

			Subscription::update_available_tests( $data['remaining_credits'], $data['total_credits'], $data['has_subscription'] );

			return rest_ensure_response([
				'message' => 'Action test_updated successful.',
			]);

		}//end if

		return new WP_Error( 'error', esc_html__( 'Test not found.', 'visual-regression-tests' ), [ 'status' => 404 ] );
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
