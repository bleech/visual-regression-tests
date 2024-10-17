<?php

namespace Vrts\Services;

use Vrts\Features\Cron_Jobs;
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
	public function get_option() {
		return get_option( self::OPTION_NAME_STATUS );
	}

	/**
	 * Sets the option.
	 *
	 * @param int $status Status 1 for success, 2 for failure.
	 */
	public function set_option( $status = 1 ) {
		update_option( self::OPTION_NAME_STATUS, $status );
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
		$request = Service::run_manual_tests( $service_test_ids, [
			'trigger_meta' => [ 'user_id' => get_current_user_id() ],
		] );

		$test_ids = array_map( function( $test ) {
			return $test->id;
		}, $tests );

		if ( 201 === $request['status_code'] ) {
			self::set_option( 1 );
			$response = $request['response'];
			$service = new Test_Run_Service();
			$id = $service->create_test_run( $response['id'], [
				'tests' => maybe_serialize( $test_ids ),
				'trigger' => 'manual',
				'started_at' => current_time( 'mysql' ),
			] );
			Cron_Jobs::schedule_initial_fetch_test_run_updates( $id );
		} else {
			self::set_option( 2 );
		}
	}
}
