<?php

namespace Vrts\Features;

use Vrts\Models\Test;
use Vrts\Services\Test_Service;

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
			update_option( $option_name, self::DB_VERSION );
		}//end if
		if ( ! self::is_connected()) {
			self::create_site();
		}
		if ( self::is_connected() && ! self::has_secret()) {
			self::create_secret();
		}
	}

	/**
	 * Rerty connection.
	 */
	public static function retry_connection() {
		return static::create_site( true );
	}

	/**
	 * Helper to create site on service.
	 *
	 * @param boolean $force Create site synchronously.
	 */
	private static function create_site() {
		if ( ! empty( get_option( 'vrts_project_id' ) ) || ! empty( get_option( 'vrts_project_token' ) ) ) {
			return;
		}
		$time = current_time( 'mysql' );
		$rest_base_url = self::get_rest_url();
		$service_api_route = 'sites';
		$create_token = md5( 'verysecret' . $time );

		$parameters = [
			'create_token' => $create_token,
			'rest_url' => $rest_base_url,
			'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
			'requested_at' => $time,
		];

		$service_request = self::rest_service_request( $service_api_route, $parameters, 'post' );

		if ( $service_request['status_code'] === 201 ) {
			$data = $service_request['response'];

			update_option( 'vrts_project_id', $data['id'] );
			update_option( 'vrts_project_token', $data['token'] );
			update_option( 'vrts_project_secret', $data['secret'] );

			Subscription::update_available_tests( $data['remaining_credits'], $data['total_credits'], $data['has_subscription'] );

			self::add_homepage_test();

			return true;
		}
		return false;
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
				$response = [
					'response' => json_decode( wp_remote_retrieve_body( $data ), true ),
					'status_code' => wp_remote_retrieve_response_code( $data ),
				];
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
		$homepage_added = get_option( $option_name );

		// If plugin was previously activated, don’t add homepage again.
		if ( ! $homepage_added ) {
			$homepage_id = get_option( 'page_on_front' );
			$args = [
				'id' => Test::get_item_id( $homepage_id ),
				'post_id' => $homepage_id,
				'status' => 1,
			];

			// Save data to custom database table.
			$testService = new Test_Service();
			$testService->create_test( $args );

			update_post_meta(
				$homepage_id,
				Metaboxes::get_post_meta_key_status(),
				1
			);

			update_option( $option_name, 1 );
		}
	}

	/**
	 * Get rest url for default language if WPML is installed.
	 */
	private static function get_rest_url() {
		// Exlusion for WPML installations.
		global $sitepress;

		$rest_url = rest_url( 'vrts/v1/service' );
		if ( $sitepress ) {
			// WPML Get languages.
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- It's ok.
			$wpml_current_lang = apply_filters( 'wpml_current_language', null );
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- It's ok.
			$wpml_default_lang = apply_filters( 'wpml_default_language', null );
			// If current language is not default, switch to default language to get the rest url.
			if ( $wpml_current_lang !== $wpml_default_lang ) {
				$sitepress->switch_lang( $wpml_default_lang );
				$rest_url = rest_url( 'vrts/v1/service' );
				// Switch back to the current language.
				$sitepress->switch_lang( $wpml_current_lang );
			}
		}

		return $rest_url;
	}

	public static function fetch_updates() {
		$service_project_id = get_option( 'vrts_project_id' );
		$service_api_route = 'sites/' . $service_project_id . '/updates';
		return self::rest_service_request( $service_api_route, [], 'get' );
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
		delete_option( 'vrts_project_id' );
		delete_option( 'vrts_project_token' );
		delete_option( 'vrts_project_secret' );
		delete_option( 'vrts_create_token' );
		delete_option( 'vrts_access_token' );
		delete_option( 'vrts_homepage_added' );
		delete_option( 'vrts_site_urls' );
		delete_option( 'vrts_connection_inactive' );
		delete_option( self::SERVICE . '_version' );
	}

	/**
	 * Check if external service was able to connect
	 */
	public static function is_connected() {
		return (bool) get_option( 'vrts_project_id' ) && (bool) get_option( 'vrts_project_token' );
	}

	public static function has_secret() {
		return (bool) get_option( 'vrts_project_secret' );
	}

	private static function create_secret() {
		$service_project_id = get_option( 'vrts_project_id' );
		$service_api_route = 'sites/' . $service_project_id . '/secret';
		$service_request = self::rest_service_request( $service_api_route, [], 'post' );
		if ( $service_request['status_code'] === 200 ) {
			update_option( 'vrts_project_secret', $service_request['response']['secret'] );
		}
	}
}
