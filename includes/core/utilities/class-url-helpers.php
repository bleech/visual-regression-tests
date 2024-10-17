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
			'page' => 'vrts-tests',
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
			'page' => 'vrts-tests',
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

	/**
	 * Get the test runs page URL.
	 *
	 * @return string
	 */
	public static function get_test_runs_page() {
		return add_query_arg( [
			'page' => 'vrts-runs',
		], admin_url( 'admin.php' ) );
	}

	/**
	 * Get the mark run as read page URL.
	 *
	 * @param int  $test_run_id Test run id.
	 * @param bool $redirect_to_overview Redirect to overview.
	 *
	 * @return string
	 */
	public static function get_mark_as_read_url( $test_run_id, $redirect_to_overview = false ) {
		$url = static::get_test_run_page( $test_run_id );
		return add_query_arg( [
			'action' => 'mark_as_read',
			'redirect' => $redirect_to_overview ? 'overview' : '',
		], $url );
	}

	/**
	 * Get the mark run as unread page URL.
	 *
	 * @param int  $test_run_id Test run id.
	 * @param bool $redirect_to_overview Redirect to overview.
	 *
	 * @return string
	 */
	public static function get_mark_as_unread_url( $test_run_id, $redirect_to_overview = false ) {
		$url = static::get_test_run_page( $test_run_id );
		return add_query_arg( [
			'action' => 'mark_as_unread',
			'redirect' => $redirect_to_overview ? 'overview' : '',
		], $url );
	}

	/**
	 * Get the tests page URL.
	 *
	 * @return string
	 */
	public static function get_tests_url() {
		return admin_url( 'admin.php?page=vrts-tests' );
	}

	/**
	 * Get the set false positive page URL.
	 *
	 * @param int  $test_run_id Test run id.
	 * @param int  $alert_id Alert id.
	 * @param bool $is_false_positive Is false positive.
	 *
	 * @return string
	 */
	public static function get_set_false_positive_url( $test_run_id, $alert_id, $is_false_positive = false ) {
		$url = static::get_test_run_page( $test_run_id ) . '&alert_id=' . $alert_id;
		return add_query_arg( [
			'action' => $is_false_positive ? 'remove_false_positive' : 'flag_false_positive',
			'redirect' => '',
		], $url );
	}
}
