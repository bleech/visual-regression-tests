<?php

namespace Vrts\Features;

use Vrts\Features\Service;

class Subscription {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add license key on setting save.
		add_action( 'add_option_vrts_license_key', [ $this, 'do_after_update_license_key' ], 10, 2 );
		add_action( 'update_option_vrts_license_key', [ $this, 'do_after_update_license_key' ], 10, 2 );
	}

	/**
	 * Update the number of tests available.
	 *
	 *  @param mixed $remaining_tests Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 *  @param mixed $available_tests Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 *  @param mixed $has_subscription Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 */
	public static function update_available_tests( $remaining_tests = null, $available_tests = null, $has_subscription = null ) {
		if ( null !== $remaining_tests ) {
			update_option( 'vrts_remaining_tests', $remaining_tests );
		}

		if ( null !== $available_tests ) {
			update_option( 'vrts_total_tests', $available_tests );
		}

		if ( null !== $has_subscription ) {
			update_option( 'vrts_has_subscription', $has_subscription );
		}
	}

	/**
	 * Register the Gumroad API key with the service.
	 *
	 *  @param mixed $old old value.
	 *  @param mixed $new new value.
	 */
	public function do_after_update_license_key( $old, $new ) {
		if ( $old !== $new ) {
			$service_project_id = get_option( 'vrts_project_id' );
			$service_api_route = 'sites/' . $service_project_id . '/register';

			$parameters = [
				'license_key'   => $new,
			];

			$response = Service::rest_service_request( $service_api_route, $parameters, 'post' );

			// TODO: Add the new number of tests available and show message that the key was added successfully based on response code.
			self::get_latest_status();
		}
	}

	/**
	 * Get the number of tests remaining.
	 */
	public static function get_remaining_tests() {
		return get_option( 'vrts_remaining_tests' );
	}

	/**
	 * Get the number of total tests.
	 */
	public static function get_total_tests() {
		return get_option( 'vrts_total_tests' );
	}

	/**
	 * Get subscription status.
	 */
	public static function get_subscription_status() {
		return get_option( 'vrts_has_subscription' );
	}

	/**
	 * Increase number of tests until server updates the number of available tests.
	 */
	public static function increase_tests_count() {
		$remaining_tests = get_option( 'vrts_remaining_tests' );
		$total_tests = get_option( 'vrts_total_tests' );

		if ( $remaining_tests < $total_tests ) {
			$remaining_tests++;
			update_option( 'vrts_remaining_tests', $remaining_tests );
		}
	}

	/**
	 * Decrease number of tests until server updates the number of available tests.
	 */
	public static function decrease_tests_count() {
		$remaining_tests = get_option( 'vrts_remaining_tests' );

		if ( $remaining_tests >= 1 ) {
			$remaining_tests--;
			update_option( 'vrts_remaining_tests', $remaining_tests );
		}
	}

	/**
	 * Drop the keys for subscription.
	 */
	public static function delete_options() {
		delete_option( 'vrts_license_key' );
		delete_option( 'vrts_remaining_tests' );
		delete_option( 'vrts_total_tests' );
		delete_option( 'vrts_has_subscription' );
	}

	/**
	 * Send request to server to get the subscription and tests status.
	 */
	public static function get_latest_status() {
		$service_project_id = get_option( 'vrts_project_id' );
		$service_api_route = 'sites/' . $service_project_id;
		$response = Service::rest_service_request( $service_api_route, [], 'get' );

		if ( array_key_exists( 'status_code', $response ) && 200 === $response['status_code'] ) {
			if ( array_key_exists( 'response', $response ) ) {
				self::update_available_tests( $response['response']['remaining_credits'], $response['response']['total_credits'], $response['response']['has_subscription'] );
			}
		}
	}
}
