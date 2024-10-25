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
			$alerts = Alert::get_items_by_test_run( $run_id );
			list($alert_id, $alert) = $this->get_alert( $alerts );

			$service = new Test_Run_Service();
			$service->update_latest_alert_for_all_tests( $run );

			$test = $alert ? Test::get_item_by_post_id( $alert->post_id ) : null;
			$tests = maybe_unserialize( $run->tests );
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
				return $a['post_id'] > $b['post_id'] ? -1 : 1;
			} );

			$is_receipt = 'receipt' === $alert_id;

			if ( $is_receipt ) {
				$current_pagination = count( $alerts );
				$prev_alert_id = isset( $alerts[ count( $alerts ) - 1 ] ) ? $alerts[ count( $alerts ) - 1 ]->id : 0;
				$next_alert_id = 0;
			} else {
				$current_pagination = Alert::get_pagination_current_position( $alert_id, $run_id );
				$prev_alert_id = Alert::get_pagination_prev_alert_id( $alert_id, $run_id );
				$next_alert_id = Alert::get_pagination_next_alert_id( $alert_id, $run_id );

				if ( ! $next_alert_id ) {
					$next_alert_id = 'receipt';
				}
			}

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
