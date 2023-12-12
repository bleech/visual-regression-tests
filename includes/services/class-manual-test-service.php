<?php

namespace Vrts\Services;

use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Models\Test;

class Manual_Test_Service {
	const OPTION_NAME_STATUS = 'vrts_run_manual_test_is_active';

	/**
	 * Check whether the option is set to true.
	 *
	 * @return bool
	 */
	public function is_active() {
		return (bool) get_option( self::OPTION_NAME_STATUS );
	}

	/**
	 * Sets the option.
	 */
	public function set_option() {
		update_option( self::OPTION_NAME_STATUS, true );
	}

	/**
	 * Delete the option.
	 */
	public function delete_option() {
		delete_option( self::OPTION_NAME_STATUS );
	}

	/**
	 * Run manual tests.
	 *
	 * @param array|null $test_ids Array of test ids.
	 */
	public function run_tests( $test_ids = null ) {
		$has_subscription = Subscription::get_subscription_status();
		if ( ! $has_subscription ) {
			return false;
		}
		if ( empty( $test_ids ) ) {
			$tests = Test::get_all_running();
		} else {
			$tests = Test::get_items_by_ids( $test_ids );
		}
		$service_test_ids = array_map( function( $test ) {
			return $test->service_test_id;
		}, $tests );
		self::set_option();
		$request = Service::run_manual_tests( $service_test_ids );
		if ( 200 === $request['status_code'] ) {
			$response = $request['response'];
			if ( array_key_exists( 'triggered_ids', $response ) ) {
				$triggered_ids = $response['triggered_ids'];
				if ( ! empty( $triggered_ids ) ) {
					Test::set_tests_running( $triggered_ids );
				}
			}
		}
	}
}
