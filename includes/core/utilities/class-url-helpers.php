<?php

namespace Vrts\Core\Utilities;

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

	public static function get_alert_page( $alert_id ) {
		$admin_url = get_admin_url();
		return $admin_url . 'admin.php?page=vrts-alerts&action=edit&alert_id=' . $alert_id;
	}

	public static function get_alerts_page( $test_run_id = null ) {
		$admin_url = get_admin_url();
		$page = 'admin.php?page=vrts-alerts';
		if ( $test_run_id ) {
			$page .= '&test_run_id=' . $test_run_id;
		}
		return $admin_url . $page;
	}
}
