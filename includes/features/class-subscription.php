<?php

namespace Vrts\Features;

use Vrts\Features\Service;
use Vrts\Models\Test;

class Subscription {
	/**
	 * Update the number of tests available.
	 *
	 *  @param mixed $remaining_tests Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 *  @param mixed $available_tests Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 *  @param mixed $has_subscription Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 *  @param mixed $tier_id Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 */
	public static function update_available_tests( $remaining_tests = null, $available_tests = null, $has_subscription = null, $tier_id = null ) {
		if ( null !== $remaining_tests ) {
			update_option( 'vrts_remaining_tests', $remaining_tests );
		}

		if ( null !== $available_tests ) {
			update_option( 'vrts_total_tests', $available_tests );
		}

		if ( null !== $has_subscription ) {
			update_option( 'vrts_has_subscription', (int) $has_subscription );
		}

		if ( null !== $tier_id ) {
			update_option( 'vrts_tier_id', $tier_id );
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
	 * Get subscription tier id.
	 */
	public static function get_subscription_tier_id() {
		return get_option( 'vrts_tier_id' );
	}

	/**
	 * Increase number of tests until server updates the number of available tests.
	 *
	 * @param int $number Number of tests to increase.
	 */
	public static function increase_tests_count( $number = 1 ) {
		$remaining_tests = get_option( 'vrts_remaining_tests' );
		$total_tests = get_option( 'vrts_total_tests' );

		if ( $remaining_tests < $total_tests ) {
			$remaining_tests += $number;
			update_option( 'vrts_remaining_tests', min( $total_tests, $remaining_tests ) );
		}
		return true;
	}

	/**
	 * Decrease number of tests until server updates the number of available tests.
	 *
	 * @param int $number Number of tests to decrease.
	 */
	public static function decrease_tests_count( $number = 1 ) {
		$remaining_tests = get_option( 'vrts_remaining_tests' );

		if ( $remaining_tests >= 1 ) {
			$remaining_tests -= $number;
			update_option( 'vrts_remaining_tests', max( 0, $remaining_tests ) );
		}
	}

	/**
	 * Drop the keys for subscription.
	 */
	public static function delete_options() {
		delete_option( 'vrts_email_notification_address' );
		delete_option( 'vrts_click_selectors' );
		delete_option( 'vrts_license_key' );
		delete_option( 'vrts_remaining_tests' );
		delete_option( 'vrts_total_tests' );
		delete_option( 'vrts_has_subscription' );
		delete_option( 'vrts_tier_id' );
	}

	/**
	 * Send request to server to get the subscription and tests status.
	 */
	public static function get_latest_status() {
		$local_test_ids = Test::get_all_service_test_ids();
		$service_project_id = get_option( 'vrts_project_id' );
		$service_api_route = 'sites/' . $service_project_id;
		$response = Service::rest_service_request( $service_api_route, [], 'get' );

		$remaining_credits = $response['response']['remaining_credits'];
		$total_credits = $response['response']['total_credits'];
		$has_subscription = $response['response']['has_subscription'];
		$tier_id = $response['response']['tier_id'];

		// Active test ids returned by service.
		$active_test_ids = $response['response']['active_test_ids'];
		$paused_test_ids = $response['response']['paused_test_ids'];

		$local_only_test_ids = [];
		foreach ( $local_test_ids as $test_id ) {
			if ( ! $has_subscription ) {
				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- This is a loose comparison by design.
				if ( ! in_array( $test_id, $active_test_ids ) && in_array( $test_id, $paused_test_ids ) ) {
					Test::pause( $test_id );
				}
			} else {
				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- This is a loose comparison by design.
				if ( in_array( $test_id, $paused_test_ids ) ) {
					$service_api_route = 'tests/' . $test_id . '/resume';
					$response = Service::rest_service_request( $service_api_route, [], 'post' );

					Test::unpause( $test_id );
				}
			}
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- This is a loose comparison by design.
			if ( $test_id && ! in_array( $test_id, $active_test_ids ) && ! in_array( $test_id, $paused_test_ids ) ) {
				$local_only_test_ids[] = $test_id;
			}
		}

		if ( ! empty( $local_only_test_ids ) ) {
			Test::clear_remote_test_ids( $local_only_test_ids );
		}

		if ( array_key_exists( 'status_code', $response ) && 200 === $response['status_code'] ) {
			if ( array_key_exists( 'response', $response ) ) {
				self::update_available_tests( $remaining_credits, $total_credits, $has_subscription, $tier_id );
			}
		}
	}
}
