<?php

namespace Vrts\Features;

use Vrts\Models\Alert;
use Vrts\Models\Test;

class Onboarding {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_field' ] );
		add_filter( 'vrts_onboarding', [ $this, 'get_onboarding' ] );
	}

	/**
	 * Add user meta setting.
	 */
	public function register_rest_field() {
		register_rest_field( 'user', 'vrts_onboarding', [
			'get_callback' => function( $user ) {
				return get_user_meta( $user['id'], 'vrts_onboarding', true ) ?: [];
			},
			'update_callback' => function( $value, $user ) {
				return update_user_meta( $user->ID, 'vrts_onboarding', $value );
			},
			'schema' => [
				'type' => 'object',
				'properties' => [
					'completed' => [
						'type' => 'array',
						'default' => [],
					],
				],
			],
		] );
	}

	/**
	 * Get onboarding.
	 *
	 * @return array
	 */
	public function get_onboarding() {
		$onboardins = $this->get_onboardings();

		foreach ( $onboardins as $onboarding ) {
			if ( $this->has_user_completed_onboarding( $onboarding['id'] ) ) {
				continue;
			}

			if ( call_user_func( $onboarding['permission_callback'] ) ) {
				unset( $onboarding['permission_callback'] );
				return $onboarding;
			}
		}

		return false;
	}

	/**
	 * Has user completed onboarding.
	 *
	 * @param string $onboarding_id the onboarding id.
	 * @return bool
	 */
	public function has_user_completed_onboarding( $onboarding_id ) {
		$onboarding = get_user_meta( get_current_user_id(), 'vrts_onboarding', true ) ?: [];
		return in_array( $onboarding_id, $onboarding['completed'] ?? [], true );
	}

	/**
	 * Get onboardings.
	 *
	 * @return array
	 */
	public function get_onboardings() {
		return [
			[
				'id' => 'tests-welcome',
				'permission_callback' => [ $this, 'should_display_tests_welcome_onboarding' ],
				'steps' => [
					[
						'title' => wp_kses_post( __( '👋 Howdy, welcome aboard!', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "With our VRTs plugin, you can effortlessly maintain your website's visual consistency. <br><br><strong>Automatically detect visual changes</strong> and <strong>receive Alerts</strong> to achieve pixel-perfect precision.", 'visual-regression-tests' ) ),
					],
					[
						'title' => wp_kses_post( __( '⏰ Daily Checks, automatic Alerts', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "Our <strong>daily Test Run</strong> takes screenshots of your <strong>Test pages</strong> and performs <strong>split-screen comparisons</strong>. <br><br>Rest assured, you'll be <strong>notified via email</strong>, as soon as a visual change is detected.", 'visual-regression-tests' ) ),
					],
					[
						'side' => 'right',
						'align' => 'start',
						'padding' => 8,
						'element' => '#show-modal-add-new',
						'title' => wp_kses_post( __( "🚀 Let's get started!", 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Add your first Test here to enable VRTs for the selected page.', 'visual-regression-tests' ) ),
					],
				],
			],
			[
				'id' => 'first-test',
				'permission_callback' => [ $this, 'should_display_first_test_onboarding' ],
				'steps' => [
					[
						'side' => 'bottom',
						'align' => 'center',
						'element' => '.wp-list-table tbody tr:first-child',
						'title' => wp_kses_post( __( '🥳 Yay, you created your first VRT!', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Starting from tomorrow, your Test will <strong>run daily</strong>, ensuring consistent monitoring of your page.', 'visual-regression-tests' ) ),
					],
					[
						'element' => '.vrts_navigation_item a[href$="admin.php?page=vrts-settings"]',
						'title' => wp_kses_post( __( '🛠️ Fine-tune your setup', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Further customize your Test configuration and plugin settings for an optimized experience.', 'visual-regression-tests' ) ),
					],
				],
			],
			[
				'id' => 'run-test',
				'permission_callback' => [ $this, 'should_display_run_test_onboarding' ],
				'steps' => [
					[
						'padding' => 2,
						'element' => '.wp-list-table .vrts-run-test',
						'title' => wp_kses_post( __( '🔬 Run your Test now', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Want to see your Test in action? <br>Give it a go and run this Test now!', 'visual-regression-tests' ) ),
					],
				],
			],
			[
				'id' => 'runs-introduction',
				'permission_callback' => [ $this, 'should_display_runs_introduction_onboarding' ],
				'steps' => [
					[
						'side' => 'bottom',
						'align' => 'center',
						'element' => '.vrts_navigation_item a[href$="admin.php?page=vrts-runs"]',
						'title' => wp_kses_post( __( '🚀 Meet the new Runs!', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Alerts are now bundled into Runs. Get a single report for each daily test, manual test, API trigger, or new: <strong>WordPress & plugin update</strong>!', 'visual-regression-tests' ) ),
					],
				],
			],
		];
	}

	/**
	 * Should display tests welcome onboarding.
	 *
	 * @return bool
	 */
	public function should_display_tests_welcome_onboarding() {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		if ( 'vrts' === $page ) {
			$frontpage_id = get_option( 'page_on_front' );
			$is_front_page_added = ! is_null( Test::get_item_id( $frontpage_id ) );
			$next_id = Test::get_autoincrement_value();

			if ( 1 === $next_id || ( $is_front_page_added && 2 === $next_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Should display first test onboarding.
	 *
	 * @return bool
	 */
	public function should_display_first_test_onboarding() {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		$is_new_test_added = isset( $_GET['new-test-added'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.

		if ( 'vrts' === $page && $is_new_test_added ) {
			$frontpage_id = get_option( 'page_on_front' );
			$is_front_page_added = ! is_null( Test::get_item_id( $frontpage_id ) );
			$next_id = Test::get_autoincrement_value();

			if ( 2 === $next_id || ( $is_front_page_added && 3 === $next_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Should display run test onboarding.
	 *
	 * @return bool
	 */
	public function should_display_run_test_onboarding() {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		$has_subscription = (bool) Subscription::get_subscription_status();

		if ( 'vrts' === $page && $has_subscription ) {
			$tests = Test::get_items();

			foreach ( $tests as $test ) {
				$status = Test::get_calculated_status( $test );
				if ( 'scheduled' === $status ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Should display run introduction onboarding.
	 *
	 * @return bool
	 */
	public function should_display_runs_introduction_onboarding() {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.

		if ( in_array( $page, [ 'vrts', 'vrts-runs', 'vrts-settings' ], true ) ) {
			$has_migrated_alerts = get_option( 'vrts_test_runs_has_migrated_alerts' );

			if ( $has_migrated_alerts ) {
				return true;
			}
		}

		return false;
	}
}
