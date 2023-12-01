<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test;
use Vrts\Features\Metaboxes;
use Vrts\Features\Subscription;
use Vrts\Models\Alert;

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
					'rest_url' => esc_url_raw( rest_url() ),
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
			global $post;
			$alert_id = Test::get_alert_id( $post->ID );
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

			$test_id = Test::get_item_id( $post->ID );
			$test = (object) Test::get_item( $test_id );

			wp_localize_script(
				'vrts-editor',
				'vrts_editor_vars',
				[
					'plugin_name' => vrts()->get_plugin_info( 'name' ),
					'rest_url' => esc_url_raw( rest_url() ),
					'has_post_alert' => Test::has_post_alert( $post->ID ),
					'test_status' => (bool) Test::get_status( $post->ID ),
					'base_screenshot_url' => Test::get_base_screenshot_url( $post->ID ),
					'base_screenshot_date' => Date_Time_Helpers::get_formatted_date_time( Test::get_base_screenshot_date( $post->ID ) ),
					'testing_status_instructions' => $testing_status_instructions,
					'placeholder_image_data_url' => vrts()->get_snapshot_placeholder_image(),
					'remaining_tests' => Subscription::get_remaining_tests(),
					'total_tests' => Subscription::get_total_tests(),
					'upgrade_url' => admin_url( 'admin.php?page=vrts-upgrade' ),
					'plugin_url' => admin_url( 'admin.php?page=vrts' ),
					'is_connected' => Service::is_connected(),
					'test_settings' => [
						'test_id' => isset( $test->id ) ? $test->id : null,
						'hide_css_selectors' => isset( $test->hide_css_selectors ) ? $test->hide_css_selectors : null,
					],
				]
			);
		}//end if
	}
}
