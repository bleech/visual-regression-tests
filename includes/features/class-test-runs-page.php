<?php

namespace Vrts\Features;

use Vrts\List_Tables\Test_Runs_List_Table;
use Vrts\List_Tables\Test_Runs_Queue_List_Table;

class Test_Runs_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
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

		add_action( 'load-' . $submenu_page, [ $this, 'screen_option' ] );
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
			'option' => 'vrts_test_runs_per_page',
		];

		// screen_option are user meta.
		add_screen_option( $option, $args );
	}

	/**
	 * Render page
	 */
	public function render_page() {
		vrts()->component('test-runs-page', [
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
			'id' => intval( $_GET['id'] ?? 0 ),
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
			'action' => sanitize_text_field( wp_unslash( $_GET['action'] ?? 'list' ) ),
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
			'search_query' => sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) ),
			'list_table' => new Test_Runs_List_Table(),
			'list_queue_table' => new Test_Runs_Queue_List_Table(),
		]);
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
