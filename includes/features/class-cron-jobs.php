<?php

namespace Vrts\Features;

use Vrts\Services\Test_Service;

class Cron_Jobs {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! wp_next_scheduled( 'vrts_fetch_updates_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'vrts_fetch_updates_cron' );
		}
		add_action( 'vrts_fetch_updates_cron', [ $this, 'fetch_updates' ] );
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
}
