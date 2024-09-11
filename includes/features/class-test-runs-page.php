<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\List_Tables\Test_Runs_List_Table;
use Vrts\List_Tables\Test_Runs_Queue_List_Table;
use Vrts\Models\Alert;
use Vrts\Models\Test_Run;

class Test_Runs_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
		add_action( 'admin_body_class', [ $this, 'add_body_class' ] );
		add_action( 'admin_init', [ $this, 'submit_mark_as_read' ] );
		add_action( 'admin_init', [ $this, 'submit_mark_as_unread' ] );
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		$submenu_page = add_submenu_page(
			'vrts',
			__( 'Runs', 'visual-regression-tests' ),
			__( 'Runs', 'visual-regression-tests' ),
			'manage_options',
			'vrts-runs',
			[ $this, 'render_page' ],
			1
		);

		if ( ! isset( $_GET['run_id'] ) ) {
			add_action( 'load-' . $submenu_page, [ $this, 'screen_option' ] );
			add_action( 'load-' . $submenu_page, [ $this, 'init_notifications' ] );
		}
	}

	/**
	 * Add screen options.
	 */
	public function screen_option() {
		// Set Screen Option.
		$option = 'per_page';
		$args   = [
			'default' => 20,
			'option' => 'vrts_test_runs_per_page',
		];

		// screen_option are user meta.
		add_screen_option( $option, $args );
	}

	/**
	 * Add body class.
	 *
	 * @param string $classes Body classes.
	 */
	public function add_body_class( $classes ) {
		if ( isset( $_GET['run_id'] ) ) {
			$classes .= ' vrts-test-run-wrap';
		}

		return $classes;
	}

	/**
	 * Render page
	 */
	public function render_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$run_id = intval( $_GET['run_id'] ?? 0 );
		$run = Test_Run::get_item( $run_id );

		if ( $run ) {
			// $alerts_ids = maybe_unserialize( $run->alerts );
			$alerts = Alert::get_items_by_test_run( $run_id );
			$alert_id = isset( $_GET['alert_id'] ) ? intval( $_GET['alert_id'] ) : ( isset( $alerts[0] ) ? $alerts[0]->id : 0 );
			$base_link = add_query_arg( [
				'run_id' => $run_id,
			], admin_url( 'admin.php?page=vrts-runs' ) );

			vrts()->component('test-run-page', [
				'run' => $run,
				'alerts' => $alerts,
				'alert' => Alert::get_item( $alert_id ),
				'pagination' => [
					'prev_alert_id' => Alert::get_pagination_prev_alert_id( $alert_id, $run_id ),
					'next_alert_id' => Alert::get_pagination_next_alert_id( $alert_id, $run_id ),
					'current' => Alert::get_pagination_current_position( $alert_id, $run_id ),
					'total' => count( $alerts ),
					'prev_link' => add_query_arg( [
						'alert_id' => Alert::get_pagination_prev_alert_id( $alert_id, $run_id ),
					], $base_link ),
					'next_link' => add_query_arg( [
						'alert_id' => Alert::get_pagination_next_alert_id( $alert_id, $run_id ),
					], $base_link ),
				],
			]);
		} else {
			vrts()->component('test-runs-page', [
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
				'search_query' => sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) ),
				'list_table' => new Test_Runs_List_Table(),
				'list_queue_table' => new Test_Runs_Queue_List_Table(),
			]);
		}//end if
	}

	/**
	 * Init notifications.
	 */
	public function init_notifications() {
		if ( ! Service::is_connected() ) {
			add_action( 'admin_notices', [ $this, 'render_notification_connection_failed' ] );
		}
	}

	/**
	 * Render connection_failed Notification.
	 */
	public function render_notification_connection_failed() {
		Admin_Notices::render_notification( 'connection_failed' );
	}

	public function submit_mark_as_read() {
		if ( ! isset( $_GET['run_id'] ) || ! isset( $_GET['page'] ) || 'vrts-runs' !== $_GET['page'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		if ( ! isset( $_GET['action'] ) || 'mark_as_read' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mark_as_read' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		$run_id = intval( $_GET['run_id'] );
		$run = Test_Run::get_item( $run_id );

		if ( ! $run ) {
			return;
		}

		Alert::mark_as_read_by_test_run( $run_id );
		$redirect_url = ( isset( $_GET['redirect'] ) && 'overview' === $_GET['redirect'] ) ? Url_Helpers::get_test_runs_page() : Url_Helpers::get_test_run_page( $run_id );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	public function submit_mark_as_unread() {
		if ( ! isset( $_GET['run_id'] ) || ! isset( $_GET['page'] ) || 'vrts-runs' !== $_GET['page'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		if ( ! isset( $_GET['action'] ) || 'mark_as_unread' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mark_as_unread' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		$run_id = intval( $_GET['run_id'] );
		$run = Test_Run::get_item( $run_id );

		if ( ! $run ) {
			return;
		}

		Alert::mark_as_unread_by_test_run( $run_id );
		$redirect_url = ( isset( $_GET['redirect'] ) && 'overview' === $_GET['redirect'] ) ? Url_Helpers::get_test_runs_page() : Url_Helpers::get_test_run_page( $run_id );
		wp_safe_redirect( $redirect_url );
		exit;
	}
}
