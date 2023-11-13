<?php

namespace Vrts\Features;

use Vrts\Models\Test;

class Tests {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Allows developers to rerun tests by calling `do_action( 'vrts_rerun_tests' )`.
		add_action( 'vrts_rerun_tests', [ $this, 'rerun_tests' ] );
	}

	/**
	 * Rerun all running tests if a subscription is active.
	 */
	public static function rerun_tests() {
		$has_subscription = Subscription::get_subscription_status();

		if ( ! $has_subscription ) {
			return false;
		}

		$tests = Test::get_all_running();
		$service_test_ids = wp_list_pluck( $tests, 'service_test_id' );
		Service::run_manual_tests( $service_test_ids );
	}
}
