<?php

namespace Vrts\Features;

class Admin_Notices {

	const OPTION_BASE_NAME = 'vrts_admin_notice_dismissed_';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_vrts_admin_notice_dismiss', [ $this, 'wp_ajax_save_dismiss_status_ajax' ] );
	}

	/**
	 * Save admin notice dismiss status as option.
	 */
	public function wp_ajax_save_dismiss_status_ajax() {
		$check_nonce = check_ajax_referer( 'vrts_admin_notice_nonce', 'security' );
		$view = isset( $_POST['view'] ) ? sanitize_text_field( wp_unslash( $_POST['view'] ) ) : null;

		if ( null !== $view && 1 === $check_nonce ) {
			$option_id = self::OPTION_BASE_NAME . $view;
			update_option( $option_id, true );
			wp_die();
		}
	}

	/**
	 * Get dismissed status of the notification from options.
	 *
	 * @param string $admin_notice_view_name The name of the view.
	 */
	private static function is_dismissed( $admin_notice_view_name ) {
		return (bool) get_option( self::OPTION_BASE_NAME . $admin_notice_view_name, false );
	}

	/**
	 * Render the admin notification.
	 *
	 * @param string $admin_notice_view_name The name of the view.
	 * @param bool   $is_dismissible Notification dismissible option.
	 * @param array  $data Data to pass to the view.
	 */
	public static function render_notification( $admin_notice_view_name, $is_dismissible = false, $data = [] ) {
		$data = array_merge([
			'view' => $admin_notice_view_name,
		], $data);

		if ( $is_dismissible ) {
			$is_dismissed = self::is_dismissed( $admin_notice_view_name );
			if ( true !== $is_dismissed ) {
				vrts()->component( 'admin-notification', $data );
			}
		} else {
			vrts()->component( 'admin-notification', $data );
		}
	}

	/**
	 * Remove all dismissed status of notifications from options.
	 */
	public static function delete_dismissed_options() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's OK.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `$wpdb->options` WHERE `option_name` Like %s",
				$wpdb->esc_like( self::OPTION_BASE_NAME . '%' )
			)
		);
	}
}
