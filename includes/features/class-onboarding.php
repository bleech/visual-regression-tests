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
						'title' => wp_kses_post( __( '👋 Hey there, Welcome aboard!', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "With our VRTs plugin, you can effortlessly maintain your website's visual consistency. <br><br><strong>Automatically detect</strong> and <strong>get notified about visual changes across</strong> your website  to achieve pixel-perfect precision.", 'visual-regression-tests' ) ),
					],
					[
						'title' => wp_kses_post( __( 'Daily Checks', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "The <strong>daily scheduled test run</strong> takes screenshots of your selected pages and conducts <strong>comparisons on a split screen</strong>. <br><br>Rest assured, as soon as a visual change is detected, we'll <strong>notify you via email</strong>.", 'visual-regression-tests' ) ),
					],
					[
						'side' => 'right',
						'align' => 'start',
						'padding' => 8,
						'element' => '#show-modal-add-new',
						'title' => wp_kses_post( __( "Let's get started!", 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Begin by adding your first test here to enable VRTs for the selected page.', 'visual-regression-tests' ) ),
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
						'title' => wp_kses_post( __( '🎉 Yay, you created your first VRT!', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "Starting from tomorrow, it will run as part of our <strong>daily test run</strong>, ensuring consistent monitoring of your selected page.", 'visual-regression-tests' ) ),
					],
					[
						'element' => '.vrts_navigation_item a[href$="admin.php?page=vrts-settings"]',
						'title' => wp_kses_post( __( 'Fine-tune your setup', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "Further customize your test configuration and plugin settings for an optimized experience.", 'visual-regression-tests' ) ),
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
						'title' => wp_kses_post( __( 'Run your first test now', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Want to see your test in action? Give it a go and run this test now!', 'visual-regression-tests' ) ),
					],
				],
			],
			[
				'id' => 'alerts',
				'permission_callback' => [ $this, 'should_display_alerts_onboarding' ],
				'steps' => [
					[
						'side' => 'top',
						'align' => 'center',
						'element' => '#post-body-content .postbox-header',
						'title' => wp_kses_post( __( 'Compare Changes', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'Explore detected changes between versions.', 'visual-regression-tests' ) ),
					],
					[
						'side' => 'right',
						'element' => '#post-body .navigation',
						'title' => wp_kses_post( __( 'Choose your view', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'To check the alert, utilize our <strong>difference, split</strong>, and <strong>side-by-side views</strong> to pinpoint the visual difference accurately.', 'visual-regression-tests' ) ),
					],
					[
						'side' => 'left',
						'padding' => 10,
						'element' => '.vrts-alert-settings-postbox',
						'title' => wp_kses_post( __( 'Fine-tune test', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( '<strong>If the alert is not accurate</strong>, you can <strong>adjust the test setup by excluding elements</strong> from the page when the snapshot is created.', 'visual-regression-tests' ) ),
					],
					[
						'side' => 'left',
						'padding' => 10,
						'element' => '#delete-action',
						'title' => wp_kses_post( __( 'Mark as false positive', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'If this alert was <strong>triggered by a harmless visual change.</strong> <br><br><strong>Once flagged, this alert will not reappear.</strong>', 'visual-regression-tests' ) ),
					],
					[
						'side' => 'left',
						'padding' => 10,
						'element' => '#publishing-action',
						'title' => wp_kses_post( __( 'Archive your Alerts', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "Organize your alerts by <strong>archiving</strong> them once you've reviewed them, for easy access and later review.", 'visual-regression-tests' ) ),
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

			if ( $next_id === 1 || ( $is_front_page_added && $next_id === 2 ) ) {
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
		$is_new_test_added = isset( $_GET['new-test-added'] );

		if ( 'vrts' === $page && $is_new_test_added ) {
			$frontpage_id = get_option( 'page_on_front' );
			$is_front_page_added = ! is_null( Test::get_item_id( $frontpage_id ) );
			$next_id = Test::get_autoincrement_value();

			if ( $next_id === 2 || ( $is_front_page_added && $next_id === 3 ) ) {
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
	 * Should display alerts onboarding.
	 *
	 * @return bool
	 */
	public function should_display_alerts_onboarding() {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		$alert_id = sanitize_text_field( wp_unslash( $_GET['alert_id'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.

		if ( 'vrts-alerts' === $page && 'edit' === $action && '' !== $alert_id ) {
			$archived_alerts = Alert::get_items( [
				'filter_status' => 'archived',
			] );

			if ( ! $archived_alerts ) {
				return true;
			}
		}

		return false;
	}
}
