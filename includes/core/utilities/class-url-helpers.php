<?php

namespace Vrts\Core\Utilities;

use Vrts\Models\Alert;
use Vrts\Models\Test_Run;

class Url_Helpers {
	/**
	 * Get the relative permalink of a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public static function get_relative_permalink( $post_id ) {
		$permalink = get_permalink( $post_id );
		$home_url = home_url();

		if ( 0 === strpos( $permalink, $home_url ) ) {
			$permalink = str_replace( $home_url, '', $permalink );
		}

		return $permalink;
	}

	/**
	 * Get the page URL.
	 *
	 * @param string $page Page.
	 *
	 * @return string
	 */
	public static function get_page_url( $page ) {
		$page = 'tests' === $page ? 'vrts' : 'vrts-' . $page;
		return admin_url( 'admin.php?page=' . $page );
	}

	/**
	 * Get the alert page URL.
	 *
	 * @param int|Alert $alert_id Alert ID.
	 * @param int       $test_run_id Test run ID.
	 *
	 * @return string
	 */
	public static function get_alert_page( $alert_id, $test_run_id = null ) {
		if ( is_null( $test_run_id ) ) {
			$alert = Alert::get_item( $alert_id );
			$test_run_id = $alert->test_run_id;
		}
		return add_query_arg( [
			'page' => 'vrts-runs',
			'run_id' => $test_run_id,
			'alert_id' => $alert_id,
		], admin_url( 'admin.php' ) );
	}

	/**
	 * Get the alerts page URL.
	 *
	 * @param int|Test_Run $test_run Test run.
	 *
	 * @return string
	 */
	public static function get_alerts_page( $test_run ) {
		$test_run_id = is_object( $test_run ) ? $test_run->id : $test_run;
		return add_query_arg( [
			'page' => 'vrts-runs',
			'run_id' => $test_run_id,
		], admin_url( 'admin.php' ) );
	}

	/**
	 * Get the run manual test page URL.
	 *
	 * @param int $test_id Test id.
	 *
	 * @return string
	 */
	public static function get_run_manual_test_url( $test_id ) {
		return add_query_arg( [
			'page' => 'vrts',
			'action' => 'run-manual-test',
			'test_id' => $test_id,
		], admin_url( 'admin.php' ) );
	}

	/**
	 * Get the disable testing test page URL.
	 *
	 * @param int $test_id Test id.
	 *
	 * @return string
	 */
	public static function get_disable_testing_url( $test_id ) {
		return add_query_arg( [
			'page' => 'vrts',
			'action' => 'disable-testing',
			'test_id' => $test_id,
		], admin_url( 'admin.php' ) );
	}

	/**
	 * Get the test run page URL.
	 *
	 * @param int $test_run_id Test run id.
	 *
	 * @return string
	 */
	public static function get_test_run_page( $test_run_id ) {
		if ( is_object( $test_run_id ) ) {
			$test_run_id = $test_run_id->id;
		}

		return add_query_arg( [
			'page' => 'vrts-runs',
			'run_id' => $test_run_id,
		], admin_url( 'admin.php' ) );
	}
}
