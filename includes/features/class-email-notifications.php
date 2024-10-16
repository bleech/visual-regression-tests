<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Features\Subscription;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;
use Vrts\Services\Render_Template_Service;
class Email_Notifications {

	/**
	 * Send email.
	 *
	 *  @param int $differences the number of differences.
	 *  @param int $post_id the id of the post.
	 *  @param int $alert_id the id of the alert.
	 */
	public function send_email( $differences, $post_id, $alert_id ) {
		$notification_email = sanitize_email( vrts()->settings()->get_option( 'vrts_email_notification_address' ) );
		$site_url = get_site_url();
		$parse_url = wp_parse_url( $site_url );
		$base_url  = $parse_url['scheme'] . '://' . $parse_url['host'];
		$admin_url = get_admin_url();

		// Check if notification email already exists.
		$subject = sprintf(
			/* translators: %1$s: the id of the alert, %2$s: the test url */
			esc_html_x( 'VRTs: Alert %1$s (%2$s)', 'notification email subject', 'visual-regression-tests' ),
			$alert_id,
			esc_url( get_the_permalink( $post_id ) )
		);

		$message = esc_html_x( 'Howdy,', 'notification email', 'visual-regression-tests' ) . "\n\n" .
			esc_html_x( 'New visual differences have been detected on the following page:', 'notification email', 'visual-regression-tests' ) . "\n\n" .
			html_entity_decode( wp_specialchars_decode( get_the_title( $post_id ) ) ) . "\n\n" .
			esc_html_x( 'View the alert:', 'notification email', 'visual-regression-tests' ) . "\n" .
			esc_url( Url_Helpers::get_alert_page( $alert_id ) ) . "\n\n" .
			sprintf(
				/* translators: %1$s: the home url */
				esc_html_x( 'This alert was sent by the Visual Regression Tests plugin on %1$s', 'notification email', 'visual-regression-tests' ), esc_url( $base_url )
			);

		$has_subscription = Subscription::get_subscription_status();
		$headers = [];
		if ( '1' === $has_subscription ) {
			$notification_email_cc = $this->sanitize_multiple_emails( vrts()->settings()->get_option( 'vrts_email_notification_cc_address' ) );
			$headers[] = 'Cc: ' . $notification_email_cc;
		}

		if ( $notification_email ) {
			$sent = wp_mail( $notification_email, wp_specialchars_decode( $subject ), $message, $headers );
			if ( $sent ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Send email.
	 *
	 *  @param int $test_run_id the id of the test run.
	 */
	public function send_test_run_email( $test_run_id ) {
		$notification_email = sanitize_email( vrts()->settings()->get_option( 'vrts_email_notification_address' ) );
		$site_url = get_site_url();
		$parse_url = wp_parse_url( $site_url );
		$base_url  = $parse_url['scheme'] . '://' . $parse_url['host'];
		$admin_url = get_admin_url();

		// Check if notification email already exists.
		$subject = sprintf(
			/* translators: %1$s: the id of the alert, %2$s: the test url */
			esc_html_x( 'VRTs: Alert for Test Run #%1$s', 'test run notification email subject', 'visual-regression-tests' ),
			$test_run_id
		);

		$service = new Render_Template_Service();
		$context = $this->get_test_run_email_context( $test_run_id );
		$message = $service->render_template( 'emails/test-run', $context );

		$has_subscription = Subscription::get_subscription_status();
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
		];
		if ( '1' === $has_subscription ) {
			$notification_email_cc = $this->sanitize_multiple_emails( vrts()->settings()->get_option( 'vrts_email_notification_cc_address' ) );
			$headers[] = 'Cc: ' . $notification_email_cc;
		}

		if ( $notification_email ) {
			$sent = wp_mail( $notification_email, wp_specialchars_decode( $subject ), $message, $headers );
			if ( $sent ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Sanitize multiple emails at once
	 *
	 * @param string $multiple_emails_string the email addresses to sanitize.
	 */
	private function sanitize_multiple_emails( $multiple_emails_string ) {
		$sanitized_email_array = [];
		if ( '' !== $multiple_emails_string ) {
			$emails = array_map( 'trim', explode( ',', $multiple_emails_string ) );
			// Build the TO array from valid addresses in the list.
			foreach ( $emails as $email ) {
				$clean = sanitize_email( $email );
				if ( is_email( $clean ) ) {
					$sanitized_email_array[] = $clean;
				}
			}
		}
		return implode( ',', $sanitized_email_array );
	}

	/**
	 * Get the context for the test run email.
	 *
	 * @param int $test_run_id the id of the test run.
	 */
	private function get_test_run_email_context( $test_run_id ) {
		$test_run = Test_Run::get_item( $test_run_id );
		$test_ids = maybe_unserialize( $test_run->tests ) ?? [];
		$alert_ids = maybe_unserialize( $test_run->alerts ) ?? [];
		$tests = Test::get_items_by_ids( $test_ids );
		$alerts = Alert::get_items_by_ids( $alert_ids );

		$tests_with_alerts = array_map( function( $alert ) use ( $tests ) {
			foreach ( $tests as $test ) {
				if ( $test->post_id === $alert->post_id ) {
					return $test->id;
				}
			}
		}, $alerts );
		$tests_without_alerts = array_diff( $test_ids, $tests_with_alerts );

		$tests_by_id = [];
		foreach ( $tests as $test ) {
			$tests_by_id[ $test->id ] = $test;
		}
		$alerts_by_post_id = [];
		foreach ( $alerts as $alert ) {
			$alerts_by_post_id[ $alert->post_id ] = $alert;
		}

		$context = [
			'test_run' => $test_run,
			'tests' => $tests_by_id,
			'alerts' => $alerts_by_post_id,
			'tests_with_alerts' => $tests_with_alerts,
			'tests_without_alerts' => $tests_without_alerts,
		];

		return $context;
	}

	/**
	 * Preview test run email.
	 *
	 * @param int $test_run_id the id of the test run.
	 */
	public function preview_test_run_email( $test_run_id ) {
		$service = new Render_Template_Service();
		$context = $this->get_test_run_email_context( $test_run_id );
		$message = $service->render_template( 'emails/test-run', $context );
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $message;
	}
}
