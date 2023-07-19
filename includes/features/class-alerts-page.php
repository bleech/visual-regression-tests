<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\List_Tables\Alerts_List_Table;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Features\Service;
use Vrts\Services\Test_Service;

class Alerts_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
		add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 3 );
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		$count = Alert::get_total_items();

		$submenu_page = add_submenu_page(
			'vrts',
			esc_html__( 'Alerts', 'visual-regression-tests' ),
			$count ? esc_html__( 'Alerts', 'visual-regression-tests' ) . '&nbsp;<span class="update-plugins" title="' . esc_attr( $count ) . '">' . esc_html( $count ) . '</span>' : esc_html__( 'Alerts', 'visual-regression-tests' ),
			'manage_options',
			'vrts-alerts',
			[ $this, 'render_page' ],
			2
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? 'list' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$alert_id = sanitize_text_field( wp_unslash( $_GET['alert_id'] ?? '0' ) );

		if ( 'vrts-alerts' === $page ) {
			// Dynamic redirect by alert_state.
			if ( 'edit' === $action || 'view' === $action ) {
				$alert = (object) Alert::get_item( $alert_id );
				$new_action = false;

				if ( isset( $alert->alert_state ) && intval( $alert->alert_state ) === 1 && 'edit' === $action ) {
					$new_action = 'view';
				} elseif ( isset( $alert->alert_state ) && intval( $alert->alert_state ) === 0 && 'view' === $action ) {
					$new_action = 'edit';
				}

				if ( $new_action && $action !== $new_action ) {
					wp_safe_redirect( add_query_arg( [
						'page' => $page,
						'action' => $new_action,
						'alert_id' => $alert_id,
					], admin_url( 'admin.php' ) ) );
					exit;
				}
			}//end if

			if ( 'list' === $action ) {
				add_action( 'load-' . $submenu_page, [ $this, 'screen_option' ] );
			}

			add_action( 'load-' . $submenu_page, [ $this, 'process_column_actions' ] );
			add_action( 'load-' . $submenu_page, [ $this, 'submit_edit_alert' ] );
			add_action( 'load-' . $submenu_page, [ $this, 'submit_edit_alert_settings' ] );
		}//end if
	}

	/**
	 * Add screen options.
	 */
	public function screen_option() {

		// Set Screen Option.
		$option = 'per_page';
		$args   = [
			'default' => 20,
			'option' => 'vrts_alerts_per_page',
		];

		// screen_option are user meta.
		add_screen_option( $option, $args );
	}

	/**
	 * Screen Option
	 *
	 * @param mixed  $status The value to save instead of the option value. Default false (to skip saving the current option).
	 * @param string $option The option name.
	 * @param int    $value The option value.
	 * @return mixed
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Render page.
	 */
	public function render_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? 'list' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$alert_id = sanitize_text_field( wp_unslash( $_GET['alert_id'] ?? '0' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
		$search_query = sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) );

		// Render Edit Page or View page.
		if ( 'edit' === $action || 'view' === $action ) {
			$alert = (object) Alert::get_item( $alert_id );
			$permalink = esc_url( get_permalink( $alert->post_id ) );
			$base_link = admin_url( 'admin.php?page=vrts-alerts' );
			$is_connected = Service::is_connected();

			$test_id = Test::get_item_id( $alert->post_id );
			$test = (object) Test::get_item( $test_id );

			vrts()->component('alerts-page', [
				'action' => $action,
				'alert_id' => $alert_id,
				'alert' => $alert,
				'permalink' => $permalink,
				'base_screenshot_url' => $alert->base_screenshot_url,
				'target_screenshot_url' => $alert->target_screenshot_url,
				'target_screenshot_finish_date' => Date_Time_Helpers::get_formatted_date_time( $alert->target_screenshot_finish_date ),
				'comparison_screenshot_url' => $alert->comparison_screenshot_url,
				'pagination' => [
					'next_alert_id' => Alert::get_pagination_next_alert_id( $alert_id, 'edit' === $action ? 0 : 1 ),
					'prev_next_alert_id' => Alert::get_pagination_prev_alert_id( $alert_id, 'edit' === $action ? 0 : 1 ),
					'current' => Alert::get_pagination_current_position( $alert_id, 'edit' === $action ? 0 : 1 ),
					'total' => Alert::get_total_items( 'edit' === $action ? null : 'resolved' ),
					'prev_link' => $base_link . '&action=' . $action . '&alert_id=' . Alert::get_pagination_prev_alert_id( $alert_id, 'edit' === $action ? 0 : 1 ),
					'next_link' => $base_link . '&action=' . $action . '&alert_id=' . Alert::get_pagination_next_alert_id( $alert_id, 'edit' === $action ? 0 : 1 ),
				],
				'is_connected'  => $is_connected,
				'test_settings' => [
					'hide_css_selectors' => isset( $test->hide_css_selectors ) ? $test->hide_css_selectors : null,
				],
			]);
		} else {
			// Render Lists page.
			vrts()->component('alerts-page', [
				'action' => $action,
				'search_query' => $search_query,
				'list_table' => new Alerts_List_Table(),
			]);
		}//end if
	}

	/**
	 * Handle the submit from details on the edit alert page.
	 */
	public function submit_edit_alert() {
		if ( ! isset( $_POST['submit_edit_alert'] ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vrts_page_alerts_nonce' ) ) {
			die( esc_html__( 'Are you cheating?', 'visual-regression-tests' ) );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Permission Denied!', 'visual-regression-tests' ) );
		}

		$errors   = [];
		$page_url = admin_url( 'admin.php?page=vrts-alerts' );

		$alert_id = isset( $_POST['alert_id'] ) ? sanitize_text_field( wp_unslash( $_POST['alert_id'] ) ) : 0;

		// Some basic validation.
		if ( ! $alert_id ) {
			$errors[] = esc_html__( 'Error: Alert ID is required.', 'visual-regression-tests' );
		}

		// Bail out if error found.
		if ( $errors ) {
			$first_error = reset( $errors );
			$redirect_to = add_query_arg( [ 'error' => $first_error ], $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		// Do the stuff.
		if ( $alert_id ) {
			$insert_alert = static::resolve_alert( $alert_id );
		}//end if

		if ( is_wp_error( $insert_alert ) ) {
			$redirect_to = add_query_arg( [ 'message' => 'error' ], $page_url );
		} else {
			$next_open_alert_id = Alert::get_next_open_alert_id();
			$redirect_to = add_query_arg( [
				'action' => 'edit',
				'alert_id' => $next_open_alert_id,
			], $page_url );
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Handle the submit from the settings on edit alert page.
	 */
	public function submit_edit_alert_settings() {
		if ( ! isset( $_POST['submit_edit_alert_settings'] ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vrts_page_alerts_nonce' ) ) {
			die( esc_html__( 'Are you cheating?', 'visual-regression-tests' ) );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Permission Denied!', 'visual-regression-tests' ) );
		}

		$errors   = [];
		$page_url = admin_url( 'admin.php?page=vrts-alerts' );

		$alert_id = isset( $_POST['alert_id'] ) ? sanitize_text_field( wp_unslash( $_POST['alert_id'] ) ) : 0;

		// Some basic validation.
		if ( ! $alert_id ) {
			$errors[] = esc_html__( 'Error: Alert ID is required.', 'visual-regression-tests' );
		}

		// Bail out if error found.
		if ( $errors ) {
			$first_error = reset( $errors );
			$redirect_to = add_query_arg( [ 'error' => $first_error ], $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		// Do the stuff.
		if ( $alert_id ) {
			$alert = (object) Alert::get_item( $alert_id );
			$test_id = Test::get_item_id( $alert->post_id );
			$hide_css_selectors = isset( $_POST['hide_css_selectors'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_css_selectors'] ) ) : null;
			$test_service = new Test_Service();
			$test_settings_saved = $test_service->update_css_hide_selectors( $test_id, $hide_css_selectors );
		}//end if

		if ( is_wp_error( $test_settings_saved ) ) {
			$redirect_to = add_query_arg( [ 'message' => 'error' ], $page_url );
		} else {
			$redirect_to = add_query_arg( [
				'action' => 'edit',
				'alert_id' => $alert_id,
			], $page_url );
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Handle column actions (restore, resolve delete).
	 */
	public function process_column_actions() {
		if ( ! isset( $_GET['action'] ) && ! isset( $_GET['alert_id'] ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vrts_page_alerts_nonce' ) ) {
			die( esc_html__( 'Are you cheating?', 'visual-regression-tests' ) );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Permission Denied!', 'visual-regression-tests' ) );
		}

		$errors   = [];
		$page_url = admin_url( 'admin.php?page=vrts-alerts' );

		$alert_id = isset( $_GET['alert_id'] ) ? sanitize_text_field( wp_unslash( $_GET['alert_id'] ) ) : 0;
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 0;

		// some basic validation.
		if ( ! $alert_id ) {
			$errors[] = esc_html__( 'Error: Alert ID is required.', 'visual-regression-tests' );
		}

		// bail out if error found.
		if ( $errors ) {
			$first_error = reset( $errors );
			$redirect_to = add_query_arg( [ 'error' => $first_error ], $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		// Edit has a separate view, so that we can bail it here.
		if ( $alert_id && 'edit' === $action || $alert_id && 'view' === $action ) {
			return;
		}

		// Restore Alert.
		if ( $alert_id && 'restore' === $action ) {
			$set_alert = $this->restore_alert( $alert_id );

			// Keep reloading resolved filtered page after restored alert, because this action can only be triggered from this page.
			$page_url = "{$page_url}&status=resolved";
		}

		// Resolve Alert.
		if ( $alert_id && 'resolve' === $action ) {
			$set_alert = $this->resolve_alert( $alert_id );
		}

		// Delete Alert.
		if ( $alert_id && 'delete' === $action ) {
			$set_alert = $this->delete_alert( $alert_id );

			// Keep reloading resolved filtered page after deleted alert, because this action can only be triggered from this page.
			$page_url = "{$page_url}&status=resolved";
		}

		if ( is_wp_error( $set_alert ) ) {
			$redirect_to = add_query_arg( [ 'message' => 'error' ], $page_url );
		} else {
			$redirect_to = add_query_arg( [ 'message' => 'success' ], $page_url );
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Resolve alert.
	 *
	 * @param int $alert_id the id of the alert.
	 */
	public static function resolve_alert( $alert_id = null ) {
		// Set the alert state.
		$new_alert_state = 1;
		$alert_result = Alert::set_alert_state( $alert_id, $new_alert_state );

		// Add the alert from tests table -> this should stop testing.
		$alert = (object) Alert::get_item( $alert_id );
		Test::set_alert( $alert->post_id, null );
		return $alert_result;
	}

	/**
	 * Restore alert.
	 *
	 * @param int $alert_id the id of the alert.
	 */
	public static function restore_alert( $alert_id = null ) {
		// Set the alert state.
		$new_alert_state = 0;
		Alert::set_alert_state( $alert_id, $new_alert_state );

		// Remove the alert from tests table -> this should continue testing.
		$alert = (object) Alert::get_item( $alert_id );
		Test::set_alert( $alert->post_id, $alert_id );
	}


	/**
	 * Delete alert.
	 *
	 * @param int $alert_id the id of the alert.
	 */
	public static function delete_alert( $alert_id = null ) {
		// Remove the alert from tests table, only to be sure that.
		$alert = (object) Alert::get_item( $alert_id );
		Test::set_alert( $alert->post_id, null );

		// Remove the alert from the database.
		Alert::delete( $alert_id );
	}
}
