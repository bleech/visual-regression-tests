<?php

namespace Vrts\Features;

use Vrts\Models\Test;

class Service {
	const DB_VERSION = '1.0';
	const SERVICE = 'vrts_service';
	const BASE_URL = 'https://bleech-vrts-app.blee.ch/api/v1/';

	/**
	 * Connect current website to VRTs Service.
	 */
	public static function connect_service() {
		$option_name = self::SERVICE . '_version';
		$installed_version = get_option( $option_name );

		if ( self::DB_VERSION !== $installed_version ) {
			$time = current_time( 'mysql' );
			$rest_base_url = get_rest_url();
			$service_api_route = 'sites';
			$create_token = md5( 'verysecret' . $time );
			$access_token = self::generate_random_string( 50 );

			// Save options temporarily for verification.
			update_option( 'vrts_create_token', $create_token );
			update_option( 'vrts_access_token', $access_token );

			$parameters = [
				'create_token' => $create_token,
				'home_url' => home_url(),
				'site_url' => site_url(),
				'rest_url' => $rest_base_url . 'vrts/v1/service',
				'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
				'requested_at' => $time,
				'access_token' => $access_token,
			];

			$response = self::rest_service_request( $service_api_route, $parameters, 'post' );
			update_option( $option_name, self::DB_VERSION );
		}//end if
	}

	/**
	 * Connect current website to VRTs Service.
	 *
	 * @param string $service_api_route the service api route.
	 * @param array  $parameters the parameters.
	 * @param string $request_type the request type.
	 */
	public static function rest_service_request( $service_api_route, $parameters = [], $request_type = '' ) {
		$request_url = self::BASE_URL . $service_api_route;
		$service_project_id = get_option( 'vrts_project_id' );
		$service_project_token = get_option( 'vrts_project_token' );
		$response = [];

		$args = [
			'project_id' => $service_project_id,
			'headers'     => [
				'Content-Type' => 'application/json; charset=utf-8',
				'Authorization' => 'Bearer ' . $service_project_token,
			],
			'body'        => wp_json_encode( $parameters ),
			'data_format' => 'body',
		];

		// If project already created, attach project id and service token.
		if ( $service_project_id && $service_project_token ) {
			$args['project_id']  = $service_project_id;
			$args['headers']['Authorization'] = 'Bearer ' . $service_project_token;
		}

		switch ( $request_type ) {
			case 'get':
				$args = [
					'method' => 'GET',
					'project_id' => $service_project_id,
					'headers'     => [
						'Authorization' => 'Bearer ' . $service_project_token,
					],
					'body'        => $parameters,
					'data_format' => 'body',
				];
				$data = wp_remote_post( $request_url, $args );
				$response = [
					'response' => json_decode( wp_remote_retrieve_body( $data ), true ),
					'status_code' => wp_remote_retrieve_response_code( $data ),
				];
				break;

			case 'delete':
				$args['method'] = 'DELETE';
				$data = wp_remote_post( $request_url, $args );
				break;

			case 'put':
				$args['method'] = 'PUT';
				$data = wp_remote_post( $request_url, $args );
				break;

			default:
				$data = wp_remote_post( $request_url, $args );
				break;
		}//end switch

		if ( empty( $response ) ) {
			$response = [
				'response' => $data,
				'status_code' => wp_remote_retrieve_response_code( $data ),
			];
		}
		return $response;
	}

	/**
	 * Generate Random String.
	 *
	 * @param int $length the length of the string.
	 */
	public static function generate_random_string( $length = 50 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters_length = strlen( $characters );
		$random_string = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
		}
		return $random_string;
	}

	/**
	 * Send request to server to resume test.
	 *
	 * @param int $alert_id the alert id.
	 */
	public static function resume_test( $alert_id ) {
		$service_test_id = Test::get_service_test_id_by_post_id( $alert_id );

		if ( $service_test_id ) {
			$service_api_route = 'tests/' . $service_test_id . '/resume';

			$response = self::rest_service_request( $service_api_route, [], 'post' );
		}
	}

	/**
	 * Send request to server to delete test.
	 *
	 * @param int $alert_id the alert id.
	 */
	public static function delete_test( $alert_id ) {
		$service_test_id = Test::get_service_test_id_by_post_id( $alert_id );

		if ( $service_test_id ) {
			$service_api_route = 'tests/' . $service_test_id;

			$response = self::rest_service_request( $service_api_route, [], 'delete' );
		}
	}

	/**
	 * Add homepage as a default test.
	 */
	public static function add_homepage_test() {
		$option_name = 'vrts_homepage_added';
		$installed_version = get_option( $option_name );

		// If plugin was previously activated, don’t add homepage again.
		if ( ! $installed_version ) {
			$homepage_id = get_option( 'page_on_front' );
			$args = [
				'id' => Test::get_item_id( $homepage_id ),
				'post_id' => $homepage_id,
				'status' => 1,
			];

			// Save data to custom database table.
			Test::save( $args );

			update_post_meta(
				$homepage_id,
				Metaboxes::get_post_meta_key_status(),
				1
			);

			update_option( $option_name, 1 );
		}
	}

	/**
	 * Delete project from the service.
	 */
	public static function disconnect_service() {
		$service_project_id = get_option( 'vrts_project_id' );
		$service_api_route = 'sites/' . $service_project_id;
		self::rest_service_request( $service_api_route, [], 'delete' );
	}

	/**
	 * Drop the database table for tests.
	 */
	public static function delete_option() {
		delete_option( 'vrts_homepage_added' );
		delete_option( self::SERVICE . '_version' );
	}

	/**
	 * Check if external service was able to connect
	 */
	public static function is_connected() {
		return (bool) get_option( 'vrts_project_id' ) && (bool) get_option( 'vrts_project_token' );
	}
}
