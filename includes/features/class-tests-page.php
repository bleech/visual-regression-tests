<?php

namespace Vrts\Features;

use Vrts\List_Tables\Tests_List_Table;
use Vrts\Models\Test;
use Vrts\Features\Subscription;

class Tests_Page {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
		add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 3 );
		add_action( 'wp_link_query', [ $this, 'wp_link_query' ], 10, 2 );
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		$submenu_page = add_submenu_page(
			'vrts',
			__( 'Tests', 'visual-regression-tests' ),
			__( 'Tests', 'visual-regression-tests' ),
			'manage_options',
			'vrts',
			[ $this, 'render_page' ],
			1
		);
		remove_submenu_page( 'vrts', 'vrts' );

		add_action( 'load-' . $submenu_page, [ $this, 'screen_option' ] );
		add_action( 'load-' . $submenu_page, [ $this, 'add_assets' ] );
		add_action( 'load-' . $submenu_page, [ $this, 'submit_add_new_test' ] );
		add_action( 'load-' . $submenu_page, [ $this, 'submit_retry_connection' ] );
		add_action( 'load-' . $submenu_page, [ $this, 'process_column_actions' ] );
		add_action( 'load-' . $submenu_page, [ $this, 'init_notifications' ] );
	}

	/**
	 * Add screen options.
	 */
	public function screen_option() {
		// Set Screen Option.
		$option = 'per_page';
		$args   = [
			'default' => 20,
			'option' => 'actions_per_page',
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
	 * Render page
	 */
	public function render_page() {
		vrts()->component('tests-page', [
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
			'id' => intval( $_GET['id'] ?? 0 ),
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
			'action' => sanitize_text_field( wp_unslash( $_GET['action'] ?? 'list' ) ),
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
			'search_query' => sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) ),
			'list_table' => new Tests_List_Table(),
			'remaining_tests' => Subscription::get_remaining_tests(),
			'is_connected' => Service::is_connected(),
		]);
	}

	/**
	 * Handle the submit of the Add New button.
	 */
	public function submit_add_new_test() {
		if ( ! isset( $_POST['submit_add_new_test'] ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vrts_page_tests_nonce' ) ) {
			die( esc_html__( 'Are you cheating?', 'visual-regression-tests' ) );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Permission Denied!', 'visual-regression-tests' ) );
		}

		$errors   = [];
		$page_url = admin_url( 'admin.php?page=vrts' );

		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;

		// some basic validation.
		if ( ! $post_id ) {
			$errors[] = esc_html__( 'Error: Post ID is required.', 'visual-regression-tests' );
		}

		// bail out if error found.
		if ( $errors ) {
			$first_error = reset( $errors );
			$redirect_to = add_query_arg( [ 'error' => $first_error ], $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		$fields = [
			'id' => Test::get_item_id( $post_id ),
			'status' => 1,
			'post_id' => $post_id,
		];

		// New or edit?
		if ( $post_id ) {
			$insert_test = Test::save( $fields );
		}

		if ( is_wp_error( $insert_test ) ) {
			$redirect_to = add_query_arg([
				'new-test-failed' => true,
				'post_id' => $post_id,
			], $page_url);
			// Update post meta.
			// Field value must set to 0 to be sure that a default value is compatible with gutenberg.
			update_post_meta(
				$post_id,
				Metaboxes::get_post_meta_key_status(),
				0
			);
		} else {
			// Update post meta.
			update_post_meta(
				$post_id,
				Metaboxes::get_post_meta_key_status(),
				1
			);
			$redirect_to = add_query_arg([
				'message' => 'success',
				'new-test-added' => true,
				'post_id' => $post_id,
			], $page_url);
		}//end if

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Handle the submit of the Retry connection button.
	 */
	public function submit_retry_connection() {
		if ( ! isset( $_POST['submit_retry_connection'] ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vrts_retry_connection_nonce' ) ) {
			die( esc_html__( 'Are you cheating?', 'visual-regression-tests' ) );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Permission Denied!', 'visual-regression-tests' ) );
		}

		$response = Service::retry_connection();

		$page_url = admin_url( 'admin.php?page=vrts' );
		wp_safe_redirect( $page_url );
		exit;
	}

	/**
	 * Handle the submit of process_column_actions.
	 */
	public function process_column_actions() {
		if ( ! isset( $_GET['action'] ) && ! isset( $_GET['test_id'] ) ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vrts_page' ) ) {
			die( esc_html__( 'Are you cheating?', 'visual-regression-tests' ) );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'Permission Denied!', 'visual-regression-tests' ) );
		}

		$errors   = [];
		$page_url = admin_url( 'admin.php?page=vrts' );

		$test_id = isset( $_GET['test_id'] ) ? sanitize_text_field( wp_unslash( $_GET['test_id'] ) ) : 0;
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 0;

		// some basic validation.
		if ( ! $test_id ) {
			$errors[] = esc_html__( 'Error: Test ID is required.', 'visual-regression-tests' );
		}

		// bail out if error found.
		if ( $errors ) {
			$first_error = reset( $errors );
			$redirect_to = add_query_arg( [ 'error' => $first_error ], $page_url );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		// Disable Testing.
		if ( $test_id && 'disable-testing' === $action ) {
			$item = (array) Test::get_item( $test_id );
			if ( $item ) {
				$set_alert = Test::delete( $item['post_id'] );
			}

			$redirect_to = add_query_arg([
				'message' => 'success',
				'testing-disabled' => ( Service::is_connected() ? true : false ),
				'post_id' => $item['post_id'],
			], $page_url);
		}

		if ( is_wp_error( $set_alert ) ) {
			$redirect_to = add_query_arg( [ 'message' => 'error' ], $page_url );
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Add required assets.
	 */
	public function add_assets() {
		// Remove may previously enqueued wplink script.
		wp_deregister_script( 'wplink' );

		// Register custom wplink for the Add New functionality.
		wp_register_script( 'vrts-wplink', vrts()->get_plugin_url( 'assets/scripts/wplink.js' ), [ 'jquery', 'wp-a11y' ], vrts()->get_plugin_info( 'version' ), false );

		// Enqueue custom wplink.
		wp_enqueue_script( 'vrts-wplink' );

		// Localize custom wplink.
		wp_localize_script(
			'vrts-wplink',
			'wpLinkL10n',
			[
				'noTitle'        => esc_html__( '(no title)', 'visual-regression-tests' ),
				'noMatchesFound' => esc_html__( 'No results to enable visual regression testing found.', 'visual-regression-tests' ),
				/* translators: Minimum input length in characters to start searching posts in the "Insert/edit link" modal. */
				'minInputLength' => (int) esc_html_x( '3', 'minimum input length for searching post links', 'visual-regression-tests' ),
			]
		);
		wp_enqueue_style( 'editor-buttons' );
	}

	/**
	 * Modify wp_link_query results.
	 *
	 * @param array $results the results.
	 * @param array $query the query.
	 *
	 * @return array the modified results.
	 */
	public function wp_link_query( $results, $query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It should be ok here.
		$is_vrts_filter_query = isset( $_POST['vrts_filter_query'] ) ? filter_var( wp_unslash( $_POST['vrts_filter_query'] ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) : false;

		if ( true === $is_vrts_filter_query ) {
			$meta_key = Metaboxes::get_post_meta_key_status();

			foreach ( $results as &$result ) {
				$run_tests_status = intval( get_post_meta( $result['ID'], $meta_key, true ) );
				$result['run_tests_status'] = $run_tests_status;
			}
		}

		return $results;
	}

	/**
	 * Init notifications.
	 */
	public function init_notifications() {
		$total_test_items = Test::get_total_items();
		$frontpage_id = get_option( 'page_on_front' );
		$is_front_page_added = ! is_null( Test::get_item_id( $frontpage_id ) );
		$is_connected = Service::is_connected();

		if ( ! Service::is_connected() ) {
			add_action( 'admin_notices', [ $this, 'render_notification_connection_failed' ] );
		} else {
			if ( 0 === $total_test_items || ( 1 === $total_test_items && true === $is_front_page_added ) ) {
				add_action( 'admin_notices', [ $this, 'render_notification_get_started' ] );
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		$is_new_test_added = isset( $_GET['new-test-added'] ) ? sanitize_text_field( wp_unslash( $_GET['new-test-added'] ) ) : false;
		if ( $is_new_test_added ) {
			add_action( 'admin_notices', [ $this, 'render_notification_new_test_added' ] );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		$is_testing_disabled = isset( $_GET['testing-disabled'] ) ? sanitize_text_field( wp_unslash( $_GET['testing-disabled'] ) ) : false;
		if ( $is_testing_disabled ) {
			add_action( 'admin_notices', [ $this, 'render_notification_test_disabled' ] );
		}

		$remaining_tests = Subscription::get_remaining_tests();
		if ( '1' === $remaining_tests ) {
			add_action( 'admin_notices', [ $this, 'render_notification_unlock_more_tests' ] );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		$is_new_test_failed = isset( $_GET['new-test-failed'] ) ? sanitize_text_field( wp_unslash( $_GET['new-test-failed'] ) ) : false;
		if ( ( $is_new_test_failed || '0' === $remaining_tests ) && $is_connected ) {
			add_action( 'admin_notices', [ $this, 'render_notification_new_test_failed' ] );
		}
	}

	/**
	 * Render connection_failed Notification.
	 */
	public function render_notification_connection_failed() {
		Admin_Notices::render_notification( 'connection_failed' );
	}

	/**
	 * Render get_started Notification.
	 */
	public function render_notification_get_started() {
		Admin_Notices::render_notification( 'get_started', true );
	}

	/**
	 * Render new_test_added Notification.
	 */
	public function render_notification_new_test_added() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : false;
		Admin_Notices::render_notification('new_test_added', false, [
			'page_title' => get_the_title( $post_id ),
		]);
	}

	/**
	 * Render new_test_failed Notification.
	 */
	public function render_notification_new_test_failed() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : false;
		Admin_Notices::render_notification('new_test_failed', false, [
			'page_title' => get_the_title( $post_id ),
		]);
	}

	/**
	 * Render test_disabled Notification.
	 */
	public function render_notification_test_disabled() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : false;
		Admin_Notices::render_notification('test_disabled', false, [
			'page_title' => get_the_title( $post_id ),
			'post_id' => intval( $post_id ),
		]);
	}

	/**
	 * Render unlock_more_tests Notification.
	 */
	public function render_notification_unlock_more_tests() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It should be ok here.
		Admin_Notices::render_notification('unlock_more_tests', false, [
			'total_tests' => Subscription::get_total_tests(),
			'remaining_tests' => Subscription::get_remaining_tests(),
		]);
	}
}
