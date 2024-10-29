<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Image_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Test;
use Vrts\Features\Subscription;

class Enqueue_Scripts {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
	}

	/**
	 * Register and Enqueue CSS and JS.
	 */
	public function enqueue_scripts() {
		if ( current_user_can( 'manage_options' ) ) {
			$admin_assets_path = vrts()->get_plugin_path( 'build/admin.asset.php' );

			if ( ! file_exists( $admin_assets_path ) ) {
				// add admin notice.
				// You need to run `npm start` or `npm run build`.'.

				return;
			}

			$admin_assets_data = include $admin_assets_path;

			// Register CSS.
			wp_register_style( 'vrts-admin', vrts()->get_plugin_url( 'build/admin.css' ), [], $admin_assets_data['version'] );

			// Register JS.
			wp_register_script( 'vrts-admin', vrts()->get_plugin_url( 'build/admin.js' ), $admin_assets_data['dependencies'], $admin_assets_data['version'], true );

			// Enqueue CSS.
			wp_enqueue_style( 'vrts-admin' );

			// Enqueue JS.
			wp_enqueue_script( 'vrts-admin' );

			// Localize scripts.
			wp_localize_script(
				'vrts-admin',
				'vrts_admin_vars',
				[
					'rest_url' => esc_url_raw( rest_url( 'vrts/v1' ) ),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'pluginUrl' => vrts()->get_plugin_url(),
					'currentUserId' => get_current_user_id(),
					'onboarding' => apply_filters( 'vrts_onboarding', null ),
				]
			);
		}//end if
	}

	/**
	 * Register and Enqueue Editor CSS and JS.
	 */
	public function enqueue_block_editor_assets() {
		if ( current_user_can( 'manage_options' ) ) {
			global $post;
			if ( ! $post ) {
				return;
			}
			$custom_post_types = get_post_types([
				'public' => true,
				'_builtin' => false,
			]);
			$post_types = array_merge( [ 'post', 'page' ], $custom_post_types );
			if ( ! in_array( $post->post_type, $post_types, true ) ) {
				return;
			}

			$editor_assets_path = vrts()->get_plugin_path( 'build/editor.asset.php' );
			if ( ! file_exists( $editor_assets_path ) ) {
				// add editor notice.
				// You need to run `npm start` or `npm run build`.'.
				return;
			}

			$editor_assets_data = include $editor_assets_path;

			// Register CSS.
			wp_register_style( 'vrts-editor', vrts()->get_plugin_url( 'build/editor.css' ), [], $editor_assets_data['version'] );

			// Register JS.
			wp_register_script( 'vrts-editor', vrts()->get_plugin_url( 'build/editor.js' ), $editor_assets_data['dependencies'], $editor_assets_data['version'], true );

			// Enqueue CSS.
			wp_enqueue_style( 'vrts-editor' );

			// Enqueue JS.
			wp_enqueue_script( 'vrts-editor' );

			// Localize scripts.
			$test = (object) Test::get_item_by_post_id( $post->ID );

			wp_localize_script(
				'vrts-editor',
				'vrts_editor_vars',
				[
					'plugin_name' => vrts()->get_plugin_info( 'name' ),
					'rest_url' => esc_url_raw( rest_url() ),
					'has_post_alert' => Test::has_post_alert( $post->ID ),
					'base_screenshot_url' => Image_Helpers::get_screenshot_url( $test, 'base' ),
					'base_screenshot_date' => Date_Time_Helpers::get_formatted_date_time( $test->base_screenshot_date ?? null ),
					'remaining_tests' => Subscription::get_remaining_tests(),
					'total_tests' => Subscription::get_total_tests(),
					'upgrade_url' => Url_Helpers::get_page_url( 'upgrade' ),
					'plugin_url' => Url_Helpers::get_page_url( 'tests' ),
					'is_connected' => Service::is_connected(),
					'test_status' => Test::get_status_data( $test ),
					'screenshot' => Test::get_screenshot_data( $test ),
					'test_settings' => [
						'test_id' => isset( $test->id ) ? $test->id : null,
						'hide_css_selectors' => isset( $test->hide_css_selectors ) ? $test->hide_css_selectors : null,
					],
				]
			);
		}//end if
	}
}
