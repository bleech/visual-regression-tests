<?php

namespace Vrts\Features;

use Vrts\Models\Alert;

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
				'id' => 'alerts',
				'permission_callback' => [ $this, 'should_display_alerts_onboarding' ],
				'steps' => [
					[
						'side' => 'top',
						'align' => 'center',
						'element' => '#post-body-content',
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
						'element' => '.vrts-alert-settings-postbox',
						'title' => wp_kses_post( __( 'Fine-tune test', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( '<strong>If the alert is not accurate</strong>, you can <strong>adjust the test setup by excluding elements</strong> from the page when the snapshot is created.', 'visual-regression-tests' ) ),
					],
					[
						'side' => 'left',
						'element' => '#delete-action',
						'title' => wp_kses_post( __( 'Mark as false positive', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'If this alert was <strong>triggered by a harmless visual change.</strong> <br><br><strong>Once flagged, this alert will not reappear.</strong>', 'visual-regression-tests' ) ),
					],
					[
						'side' => 'left',
						'element' => '#publishing-action',
						'title' => wp_kses_post( __( 'Archive your Alerts', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( "Organize your alerts by <strong>archiving</strong> them once you've reviewed them, for easy access and later review.", 'visual-regression-tests' ) ),
					],
				],
			],
		];
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
			$alert = Alert::get_item( $alert_id );

			// only if the alert has option to mark as false positive.
			if ( $alert && 0 === (int) $alert->alert_state && null !== $alert->comparison_id ) {
				return true;
			}
		}

		return false;
	}
}
