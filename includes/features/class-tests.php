<?php

namespace Vrts\Features;

use Vrts\Models\Test;

class Tests {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Allows developers to run tests by calling `do_action( 'vrts_run_tests' )`.
		add_action( 'vrts_run_tests', [ $this, 'run_api_tests' ] );
		add_action( 'upgrader_process_complete', [ $this, 'run_upgrader_tests' ], 10, 99 );
	}

	/**
	 * Run api tests.
	 *
	 * @param string $notes Notes.
	 */
	public static function run_api_tests( $notes = '' ) {
		self::run_tests( 'api', $notes );
	}

	/**
	 * Run upgrader tests.
	 *
	 * @param \WP_Upgrader $upgrader Upgrader.
	 * @param array        $options Options.
	 */
	public static function run_upgrader_tests( $upgrader, $options ) {
		error_log( 'run_upgrader_tests: ' . print_r( func_get_args(), true ) );
	}

	/**
	 * Run tests.
	 *
	 * @param string $trigger Trigger.
	 * @param string $trigger_notes Trigger notes.
	 */
	private static function run_tests( $trigger, $trigger_notes ) {
		$has_subscription = Subscription::get_subscription_status();

		if ( ! $has_subscription ) {
			return false;
		}

		$tests = Test::get_all_running();
		$service_test_ids = wp_list_pluck( $tests, 'service_test_id' );
		Service::run_manual_tests( $service_test_ids, [
			'trigger' => $trigger,
			'trigger_notes' => $trigger_notes,
		] );
	}
}
