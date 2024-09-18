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
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		$count = Alert::get_total_items_grouped_by_test_run();

		$submenu_page = add_submenu_page(
			'vrts',
			__( 'Runs', 'visual-regression-tests' ),
			$count ? esc_html__( 'Runs', 'visual-regression-tests' ) . '&nbsp;<span class="update-plugins" title="' . esc_attr( $count ) . '">' . esc_html( $count ) . '</span>' : esc_html__( 'Runs', 'visual-regression-tests' ),
			'manage_options',
			'vrts-runs',
			[ $this, 'render_page' ],
			1
		);

		if ( ! isset( $_GET['run_id'] ) ) {
			add_action( 'load-' . $submenu_page, [ $this, 'screen_option' ] );
			add_action( 'load-' . $submenu_page, [ $this, 'init_notifications' ] );
		}

		add_action( 'load-' . $submenu_page, [ $this, 'handle_read_status' ] );
		add_action( 'load-' . $submenu_page, [ $this, 'handle_false_positive' ] );
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

			Alert::set_alert_state( $alert_id, 1 );

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

	public function handle_read_status() {
		if ( ! isset( $_GET['run_id'] ) || ! isset( $_GET['page'] ) || 'vrts-runs' !== $_GET['page'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		if ( ! isset( $_GET['action'] ) || ( 'mark_as_read' !== $_GET['action'] && 'mark_as_unread' !== $_GET['action'] ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mark_as_read' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		$run_id = intval( $_GET['run_id'] );
		$run = Test_Run::get_item( $run_id );
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( ! $run ) {
			return;
		}

		$read_status = 'mark_as_read' === $action;
		Alert::set_read_status_by_test_run( $run_id, $read_status );

		$redirect_url = ( isset( $_GET['redirect'] ) && 'overview' === $_GET['redirect'] ) ? Url_Helpers::get_test_runs_page() : Url_Helpers::get_test_run_page( $run_id );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Submit false positive.
	 */
	public function handle_false_positive() {
		if ( ! isset( $_GET['alert_id'] ) || ! isset( $_GET['page'] ) || 'vrts-runs' !== $_GET['page'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		if ( ! isset( $_GET['action'] ) || ( 'flag_false_positive' !== $_GET['action'] && 'remove_false_positive' !== $_GET['action'] ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'false_positive' ) ) {
			return;
		}

		$alert_id = intval( $_GET['alert_id'] );
		$run_id = intval( $_GET['run_id'] );
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		$run = Test_Run::get_item( $run_id );
		$alert = Alert::get_item( $alert_id );

		if ( ! $run ) {
			return;
		}

		if ( ! $alert ) {
			return;
		}

		$service = new Service();
		$should_flag_false_positive = 'flag_false_positive' === $action;

		Alert::set_false_positive( $alert_id, $should_flag_false_positive );

		if ( $should_flag_false_positive ) {
			$service->mark_alert_as_false_positive( $alert_id );
		} else {
			$service->unmark_alert_as_false_positive( $alert_id );
		}

		$redirect_url = Url_Helpers::get_test_run_page( $run_id ) . '&alert_id=' . $alert_id;
		wp_safe_redirect( $redirect_url );

		exit;
	}
}
