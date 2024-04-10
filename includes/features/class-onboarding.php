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
				'id' => 'false-positives',
				'permission_callback' => [ $this, 'should_display_alerts_onboarding' ],
				'steps' => [
					[
						'side' => 'left',
						'element' => '#delete-action',
						'title' => wp_kses_post( __( 'Mark as false positive', 'visual-regression-tests' ) ),
						'description' => wp_kses_post( __( 'You may <strong>stop this alert from happening again.</strong> Mark it as a false positive and the plugin will filter out matching alerts for you.', 'visual-regression-tests' ) ),
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
