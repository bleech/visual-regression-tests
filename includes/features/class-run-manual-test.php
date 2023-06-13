<?php

namespace Vrts\Features;

class Run_Manual_Test {
	const OPTION_NAME_STATUS = 'vrts_run_manual_test_is_active';

	/**
	 * Check whether the option is set to true.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return (bool) get_option( self::OPTION_NAME_STATUS );
	}

	/**
	 * Sets the option.
	 */
	public static function set_option() {
		update_option( self::OPTION_NAME_STATUS, true );
	}

	/**
	 * Delete the option.
	 */
	public static function delete_option() {
		delete_option( self::OPTION_NAME_STATUS );
	}

	/**
	 * Create manual test(s).
	 *
	 * @param array   $tests Array of tests.
	 * @param boolean $all Whether to run all tests or not.
	 * @return void
	 */
	public static function create( array $tests, bool $all = false ) {
		self::set_option();
	}
}
