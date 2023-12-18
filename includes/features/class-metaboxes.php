<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Services\Test_Service;
use WP_Error;

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
		add_action( 'rest_api_init', [ $this, 'add_rest_actions' ] );
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
	 * Add rest actions.
	 */
	public function add_rest_actions() {
		if ( current_user_can( 'manage_options' ) ) {
			$custom_post_types = get_post_types([
				'public' => true,
				'_builtin' => false,
			]);
			foreach ( array_merge( [ 'post', 'page' ], $custom_post_types ) as $custom_post_type ) {
				add_action( 'rest_after_insert_' . $custom_post_type, [ $this, 'update_rest_data' ], 10, 2 );
			}
		}
	}

	/**
	 * Render meta box.
	 */
	public function render_metabox() {
		global $post;
		$post_id = $post->ID ? $post->ID : 0;
		$run_tests_checked = ! empty( Test::get_item_id( $post_id ) );
		$base_screenshot_date = Test::get_base_screenshot_date( $post_id );
		if ( $base_screenshot_date ) {
			$base_screenshot_date = Date_Time_Helpers::get_formatted_date_time( $base_screenshot_date );
		}

		$alert_id = Test::get_alert_id( $post_id );
		$testing_status_instructions = '';
		if ( $alert_id ) {
			$base_link = admin_url( 'admin.php?page=vrts-alerts&action=edit&alert_id=' );
			$testing_status_instructions .= sprintf(
				/* translators: %1$s and %2$s: link wrapper. */
				esc_html__( 'Resolve %1$salert%2$s to resume testing', 'visual-regression-tests' ),
				'<a href="' . esc_url( $base_link . $alert_id ) . '">',
				'</a>'
			);
		}

		$test_id = Test::get_item_id( $post_id );
		$test = (object) Test::get_item( $test_id );

		vrts()->component('metabox-classic-editor', [
			'post_id' => $post_id,
			'nonce' => $this->nonce,
			'run_tests_checked' => $run_tests_checked,
			'field_test_status_key' => self::$field_test_status_key,
			'has_post_alert' => Test::has_post_alert( $post_id ),
			'test_status' => (bool) Test::get_status( $post_id ),
			'base_screenshot_url' => Test::get_base_screenshot_url( $post_id ),
			'base_screenshot_date' => $base_screenshot_date,
			'testing_status_instructions' => $testing_status_instructions,
			'placeholder_image_data_url' => vrts()->get_snapshot_placeholder_image(),
			'is_new_test' => self::is_new_test( $post_id ),
			'remaining_tests' => Subscription::get_remaining_tests(),
			'total_tests' => Subscription::get_total_tests(),
			'is_connected' => Service::is_connected(),
			'test_settings' => [
				'test_id' => isset( $test->id ) ? $test->id : null,
				'hide_css_selectors' => isset( $test->hide_css_selectors ) ? $test->hide_css_selectors : null,
			],
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

		// Save Settings of the test.
		$test = (object) Test::get_item_by_post_id( $post_id );
		$test_id = isset( $_POST['test_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['test_id'] ) ) : 0;

		if ( ! empty( $test ) && ! empty( $test->id ) && (int) $test->id === (int) $test_id ) {
			$hide_css_selectors = isset( $_POST['hide_css_selectors'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_css_selectors'] ) ) : '';
			$test_service = new Test_Service();
			$test_service->update_css_hide_selectors( $test_id, $hide_css_selectors );
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

	/**
	 * Update rest data.
	 *
	 * @param WP_Post         $post Post object.
	 * @param WP_REST_Request $request Request object.
	 */
	public function update_rest_data( $post, $request ) {
		$vrts_params = $request->get_param( 'vrts' );
		if ( array_key_exists( 'hide_css_selectors', $vrts_params ?? [] ) ) {
			$hide_css_selectors = $vrts_params['hide_css_selectors'] ? sanitize_text_field( $vrts_params['hide_css_selectors'] ) : '';
			$test_id = Test::get_item_id( $post->ID );
			$test_service = new Test_Service();
			$test_service->update_css_hide_selectors( $test_id, $hide_css_selectors );
		}
	}
}
