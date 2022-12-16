<?php

namespace Vrts\Features;

class Cron_Jobs {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! wp_next_scheduled( 'vrts_connection_check_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'vrts_connection_check_cron' );
		}
		add_action( 'vrts_connection_check_cron', [ $this, 'connection_check_cron' ] );
	}

	/**
	 * Daily check connection status.
	 */
	public static function connection_check_cron() {
		$site_urls = get_option( 'vrts_site_urls' );
		if ( ! $site_urls ) {
			$service_project_id = get_option( 'vrts_project_id' );
			$service_api_route = 'sites/' . $service_project_id;
			$response = Service::rest_service_request( $service_api_route, [], 'get' );

			$parse_home_url = wp_parse_url( home_url() );
			$parse_site_url = wp_parse_url( site_url() );

			$comparison_base_url = $response['response']['base_url'];
			$comparison_home_url = ( str_contains( $comparison_base_url, $parse_home_url['host'] ) ? $comparison_base_url : null );
			$comparison_site_url = ( str_contains( $comparison_base_url, $parse_site_url['host'] ) ? $comparison_base_url : null );
			$comparison_rest_url = $response['response']['rest_url'];
			$comparison_admin_ajax_url = $response['response']['admin_ajax_url'];

			// Store the site urls if not previously saved.
			$on_activation = false;
			Service::store_site_urls( $on_activation, $home_url, $site_url, $comparison_rest_url, $comparison_admin_ajax_url );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- It's benign. Used to check if the installation moved from production to local.
		$stored_urls = json_decode( base64_decode( $site_urls ), true );

		$comparison_rest_url = $stored_urls['rest_url'];
		$comparison_admin_ajax_url = $stored_urls['admin_ajax_url'];

		$rest_url = get_rest_url();
		$admin_ajax_url = admin_url( 'admin-ajax.php' );

		if ( $rest_url !== $comparison_rest_url ) {
			update_option( 'vrts_connection_inactive', true );
		}

		if ( $admin_ajax_url !== $comparison_admin_ajax_url ) {
			update_option( 'vrts_connection_inactive', true );
		}
	}
}
