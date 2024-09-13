<?php

namespace Vrts\Core\Utilities;

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
	 * Get the test run page URL.
	 *
	 * @param int|Test_Run $test_run Test run.
	 *
	 * @return string
	 */
	public static function get_alert_page( $alert_id ) {
		$admin_url = get_admin_url();
		return $admin_url . 'admin.php?page=vrts-alerts&action=edit&alert_id=' . $alert_id;
	}

	/**
	 * Get the alerts page URL.
	 *
	 * @param int|Test_Run $test_run Test run.
	 *
	 * @return string
	 */
	public static function get_alerts_page( $test_run = null ) {
		$admin_url = get_admin_url();
		$page = 'admin.php?page=vrts-alerts&status=all';
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( is_numeric( $test_run ) && intval( $test_run ) == $test_run ) {
			$test_run = Test_Run::get_item( $test_run );
		}
		if ( $test_run ) {
			$page .= '&s=' . rawurlencode( $test_run->title );
		}
		return $admin_url . $page;
	}

	public static function get_test_run_page( $test_run_id ) {
		if ( is_object($test_run_id) ) {
			$test_run_id = $test_run_id->ID;
		} else
		$admin_url = get_admin_url();
		return $admin_url . 'admin.php?page=vrts-runs&run_id=' . $test_run_id;
	}

	public static function get_test_runs_page() {
		$admin_url = get_admin_url();
		return $admin_url . 'admin.php?page=vrts-runs';
	}

	public static function get_mark_as_read_url( $test_run_id, $redirect_to_overview = false ) {
		$url = static::get_test_run_page( $test_run_id );
		return add_query_arg( [
			'action' => 'mark_as_read',
			'redirect' => $redirect_to_overview ? 'overview' : '',
		], $url );
	}

	public static function get_mark_as_unread_url( $test_run_id, $redirect_to_overview = false ) {
		$url = static::get_test_run_page( $test_run_id );
		return add_query_arg( [
			'action' => 'mark_as_unread',
			'redirect' => $redirect_to_overview ? 'overview' : '',
		], $url );
	}

	public static function get_tests_url() {
		return admin_url( 'admin.php?page=vrts' );
	}
}
