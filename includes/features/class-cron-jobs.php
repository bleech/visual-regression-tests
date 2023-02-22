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
		// Service::check_connection();
	}
}
