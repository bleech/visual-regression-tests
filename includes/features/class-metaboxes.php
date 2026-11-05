<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Image_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Test;
use Vrts\Services\Test_Service;

class Metaboxes {

	/**
	 * Field key for the "Run Tests" checkbox post meta.
	 * _ (underscore prefix) represents a protected meta key.
	 *
	 * @var string
	 */
	public static $field_test_status_key = '_vrts_testing_status';

	/**
	 * Field key for the "is new test" post meta.
	 * _ (underscore prefix) represents a protected meta key.
	 *
	 * @var string
	 */
	public static $field_is_new_test_key = '_vrts_is_new_test';

	/**
	 * Nonce.
	 *
	 * @var string
	 */
	protected $nonce = 'vrts_metabox_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_test_settings_assets' ] );
		add_action( 'admin_footer-post.php', [ $this, 'render_test_settings' ] );
		add_action( 'admin_footer-post-new.php', [ $this, 'render_test_settings' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes_data' ], 10, 2 );
	}

	/**
	 * Get the value of the static key.
	 */
	public static function get_post_meta_key_status() {
		return self::$field_test_status_key;
	}

	/**
	 * Get the value of the static key.
	 */
	public static function get_post_meta_key_is_new_test() {
		return self::$field_is_new_test_key;
	}

	/**
	 * Is new test.
	 *
	 * @param int $post_id WP Post id.
	 */
	public static function is_new_test( $post_id ) {
		$test = Test::get_item_by_post_id( $post_id );

		return empty( $test ) ? false : ! $test->service_test_id;
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		if ( current_user_can( 'manage_options' ) ) {
			$custom_post_types = get_post_types([
				'public' => true,
				'_builtin' => false,
			]);

			add_meta_box(
				'vrts_post_options_metabox',
				vrts()->get_plugin_info( 'name' ),
				[ $this, 'render_metabox' ],
				array_merge( [ 'post', 'page' ], $custom_post_types ),
				'side',
				'default',
				[ '__back_compat_meta_box' => true ]
			);
		}
	}

	/**
	 * Enqueue the code editor for the test settings modal. In the block editor, scripts enqueued
	 * while the modal renders in the footer would not be printed.
	 *
	 * @param string $hook_suffix Admin page.
	 */
	public function enqueue_test_settings_assets( $hook_suffix ) {
		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && $this->has_test_settings() ) {
			wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
			wp_enqueue_code_editor( [ 'type' => 'application/javascript' ] );
		}
	}

	/**
	 * Render the test settings modal opened from the meta box and the block editor sidebar.
	 */
	public function render_test_settings() {
		if ( $this->has_test_settings() ) {
			$test = Test::get_item_by_post_id( get_the_ID() );
			vrts()->component( 'test-settings', [
				'preloaded' => $test ? [ $test->id => \Vrts\Services\Test_Settings::get_settings( $test ) ] : [],
			] );
		}
	}

	/**
	 * Whether the current post edit screen offers test settings.
	 *
	 * @return bool
	 */
	private function has_test_settings() {
		return current_user_can( 'manage_options' ) && in_array( get_current_screen()->post_type, vrts()->get_public_post_types(), true );
	}

	/**
	 * Render meta box.
	 */
	public function render_metabox() {
		global $post;

		$post_id = $post->ID ? $post->ID : 0;
		$test_id = Test::get_item_id( $post_id );
		$test = (object) Test::get_item( $test_id );

		$run_tests_checked = ! is_null( $test_id );
		$alert_id = $test->current_alert_id ?? null;
		$testing_status_instructions = '';

		if ( $alert_id ) {
			$alert_link = Url_Helpers::get_alert_page( $alert_id );
			$testing_status_instructions .= sprintf(
				/* translators: %1$s and %2$s: link wrapper. */
				esc_html__( '%1$sView Alert%2$s', 'visual-regression-tests' ),
				'<a href="' . esc_url( $alert_link ) . '">',
				'</a>'
			);
		}

		vrts()->component('metabox-classic-editor', [
			'post_id' => $post_id,
			'nonce' => $this->nonce,
			'plugin_url' => Url_Helpers::get_page_url( 'tests' ),
			'run_tests_checked' => $run_tests_checked,
			'field_test_status_key' => self::$field_test_status_key,
			'has_post_alert' => isset( $test->current_alert_id ) ? ! is_null( $test->current_alert_id ) : false,
			'base_screenshot_url' => Image_Helpers::get_screenshot_url( $test, 'base' ),
			'base_screenshot_date' => Date_Time_Helpers::get_formatted_date_time( $test->base_screenshot_date ?? null ),
			'testing_status_instructions' => $testing_status_instructions,
			'is_new_test' => self::is_new_test( $post_id ),
			'remaining_tests' => Subscription::get_remaining_tests(),
			'total_tests' => Subscription::get_total_tests(),
			'is_connected' => Service::is_connected(),
			'test_status' => Test::get_status_data( $test ),
			'screenshot' => Test::get_screenshot_data( $test ),
			'test_id' => isset( $test->id ) ? $test->id : null,
		]);
	}

	/**
	 * Add meta boxes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function save_meta_boxes_data( $post_id, $post ) {
		$edit_cap = get_post_type_object( $post->post_type )->cap->edit_post;

		if ( ! current_user_can( $edit_cap, $post_id ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ $this->nonce ] ?? '' ) );

		// Verify nonce. Only valid when using classic editor.
		if ( ! wp_verify_nonce( $nonce, $this->nonce ) ) {
			return;
		}

		// Save "Run Tests" checkbox value to post meta.
		if ( array_key_exists( self::$field_test_status_key, $_POST ) && '1' === $_POST[ self::$field_test_status_key ] ) {
			$service = new Test_Service();
			$service->create_test( $post_id );
		} else {
			// Delete data from tests database table if "Run Tests" checkbox is not checked.
			$test_id = Test::get_item_id( $post_id );
			if ( $test_id ) {
				$service = new Test_Service();
				$service->delete_test( (int) $test_id );
			}
		}
	}

	/**
	 * Delete post meta keys.
	 */
	public static function delete_meta_keys() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->delete(
			$wpdb->postmeta,
			[
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- TODO: Check later
				'meta_key' => self::get_post_meta_key_status(),
			]
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->delete(
			$wpdb->postmeta,
			[
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- TODO: Check later
				'meta_key' => self::get_post_meta_key_is_new_test(),
			]
		);
	}
}
