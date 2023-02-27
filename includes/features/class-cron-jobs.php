<?php

namespace Vrts\Features;

use Vrts\Models\Test;
use Vrts\Services\Test_Service;

class Cron_Jobs {
	private $max_tries = 10;
	private $wait_multiplicator = 2;
	private $initial_wait = 20;
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! wp_next_scheduled( 'vrts_fetch_updates_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'vrts_fetch_updates_cron' );
		}
		add_action( 'vrts_fetch_updates_cron', [ $this, 'fetch_updates' ] );
		add_action( 'vrts_fetch_test_updates', [ $this, 'fetch_test_updates' ], 10, 2 );
	}

	/**
	 * Daily check connection status.
	 */
	public function fetch_updates() {
		$service = new Test_Service();
		$service->fetch_and_update_tests();
	}

	public static function remove_jobs() {
		wp_clear_scheduled_hook( 'vrts_connection_check_cron' );
		wp_clear_scheduled_hook( 'vrts_fetch_updates_cron' );
	}

	public function fetch_test_updates( $test_id, $try_number = 1 ) {
		$test = Test::get_item( $test_id );
		if ( empty( $test ) || empty( $test->snapshot_date ) ) {
			$service = new Test_Service();
			$service->fetch_and_update_tests();

			if ( $try_number < $this->max_tries ) {
				$next_execution = time() + $this->initial_wait * $this->wait_multiplicator * $try_number;
				wp_schedule_single_event( $next_execution, 'vrts_fetch_test_updates', [ $test_id, $try_number + 1 ] );
			}
		}
	}

	public static function schedule_initial_fetch_test_updates( $test_id) {
		wp_schedule_single_event( time(), 'vrts_fetch_test_updates', [ $test_id, 1 ] );
	}
}
