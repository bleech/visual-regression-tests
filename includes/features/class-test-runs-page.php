<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\List_Tables\Test_Runs_List_Table;
use Vrts\List_Tables\Test_Runs_Queue_List_Table;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;
use Vrts\Services\Test_Run_Service;

class Test_Runs_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'remove_admin_notices' ] );
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
		add_action( 'admin_body_class', [ $this, 'add_body_class' ] );

	}

	/**
	 * Remove admin notices.
	 */
	public function remove_admin_notices() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		if ( isset( $_GET['page'] ) && 'vrts-runs' === $_GET['page'] && isset( $_GET['run_id'] ) ) {
			remove_all_actions( 'admin_notices' );
		}
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

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
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
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
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
			$service = new Test_Run_Service();
			$service->update_latest_alert_for_all_tests( $run );

			$tests = $this->prepare_tests( maybe_unserialize( $run->tests ) );
			$alerts = $this->prepare_alerts( $run_id, $tests );

			list($alert_id, $alert) = $this->get_alert( $alerts );
			$test = $alert ? Test::get_item_by_post_id( $alert->post_id ) : null;

			$is_receipt = 'receipt' === $alert_id;

			list( $current_pagination, $prev_alert_id, $next_alert_id ) = $this->get_pagination( $alerts, $alert_id );

			vrts()->component('test-run-page', [
				'run' => $run,
				'alerts' => $alerts,
				'alert' => $alert,
				'is_receipt' => $is_receipt,
				'pagination' => [
					'prev_alert_id' => $prev_alert_id,
					'next_alert_id' => $next_alert_id,
					'current' => $current_pagination,
					'total' => count( $alerts ),
				],
				'tests' => $tests,
				'test_settings' => [
					'test_id' => isset( $test->id ) ? $test->id : null,
					'hide_css_selectors' => isset( $test->hide_css_selectors ) ? $test->hide_css_selectors : null,
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
	 * Prepare alerts.
	 *
	 * @param array $tests Tests.
	 */
	private function prepare_tests( $tests ) {
		if ( is_array( $tests ) && count( $tests ) > 0 && ! is_array( $tests[0] ) ) {
			$tests = array_map( function( $test ) {
				$test = (int) $test;
				$post_id = Test::get_post_id( $test );
				return [
					'id' => $test,
					'post_id' => $post_id,
					'post_title' => get_the_title( $post_id ),
					'permalink' => get_permalink( $post_id ),
				];
			}, $tests );
		}
		usort( $tests, function( $a, $b ) {
			return $a['id'] > $b['id'] ? -1 : 1;
		} );
		return $tests;
	}

	/**
	 * Prepare alerts.
	 *
	 * @param int   $run_id Run ID.
	 * @param array $tests Tests.
	 */
	private function prepare_alerts( $run_id, $tests ) {
		$alerts = Alert::get_items_by_test_run( $run_id );
		$alerts_by_post_id = [];
		foreach ( $alerts as $alert ) {
			$alerts_by_post_id[ $alert->post_id ][] = $alert;
		}
		$sorted_alerts = [];
		foreach ( $tests as $test ) {
			if ( isset( $alerts_by_post_id[ $test['post_id'] ] ) ) {
				$sorted_alerts = array_merge( $sorted_alerts, $alerts_by_post_id[ $test['post_id'] ] );
				unset( $alerts_by_post_id[ $test['post_id'] ] );
			}
		}
		$remaining_alerts = array_values( $alerts_by_post_id );
		usort( $remaining_alerts, function( $a, $b ) {
			return $a[0]->post_id > $b[0]->post_id ? -1 : 1;
		} );
		foreach ( $remaining_alerts as $remaining_alert ) {
			$sorted_alerts = array_merge( $sorted_alerts, $remaining_alert );
		}
		return $sorted_alerts;
	}

	/**
	 * Get pagination.
	 *
	 * @param array $alerts   Alerts.
	 * @param int   $alert_id Alert ID.
	 */
	private function get_pagination( $alerts, $alert_id ) {
		if ( 'receipt' === $alert_id ) {
			$current_pagination = count( $alerts );
			$prev_alert_id = $alerts[ count( $alerts ) - 1 ]->id ?? 0;
			$next_alert_id = 0;
		} else {
			$current_index = ( array_keys( array_filter( $alerts, function ( $alert ) use ( $alert_id ) {
				return $alert->id === $alert_id;
			} ) )[0] ?? 0 );
			$prev_alert_id = $alerts[ $current_index - 1 ]->id ?? 0;
			$next_alert_id = $alerts[ $current_index + 1 ]->id ?? 0;
			$current_pagination = $current_index + 1;

			if ( ! $next_alert_id ) {
				$next_alert_id = 'receipt';
			}
		}
		return [ $current_pagination, $prev_alert_id, $next_alert_id ];
	}

	/**
	 * Get alert.
	 *
	 * @param array $alerts Alerts.
	 */
	private function get_alert( $alerts ) {
		$first_alert_id = isset( $alerts[0] ) ? $alerts[0]->id : 0;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		$alert_id = sanitize_text_field( wp_unslash( $_GET['alert_id'] ?? $first_alert_id ) );

		if ( 'receipt' === $alert_id ) {
			return [ 'receipt', null ];
		}

		$alert = Alert::get_item( $alert_id );

		if ( ! $alert ) {
			$alert_id = $first_alert_id;
			$alert = Alert::get_item( $alert_id );
		}

		return [ $alert_id, $alert ];
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
}
