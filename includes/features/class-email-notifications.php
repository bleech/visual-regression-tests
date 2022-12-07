<?php

namespace Vrts\Features;

use Vrts\Features\Subscription;
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
		$admin_url = get_admin_url();

		// Check if notification email already exists.
		$subject = sprintf(
			/* translators: %1$s: the id of the alert, %2$s: the home url */
			esc_html__( 'VRTs: Alert %1$s (%2$s)', 'visual-regression-tests' ),
			$alert_id,
			esc_url( $site_url )
		);

		$message = esc_html__( 'Howdy,', 'visual-regression-tests' ) . "\n\n" .
			esc_html__( 'New visual differences have been detected on a page.', 'visual-regression-tests' ) . "\n\n" .
			esc_html__( 'Review and resolve the alert to resume testing:', 'visual-regression-tests' ) . "\n" .
			esc_url( $admin_url ) . 'admin.php?page=vrts-alerts&action=edit&alert_id=' . $alert_id . "\n\n" .
			sprintf(
				/* translators: %1$s: the home url */
				esc_html__( 'This alert was sent by the Visual Regression Tests plugin on %1$s', 'visual-regression-tests' ), esc_url( $site_url )
			);

		$has_subscription = Subscription::get_subscription_status();
		$headers = [];
		if ( '1' === $has_subscription ) {
			$notification_email_cc = $this->sanitize_multiple_emails( vrts()->settings()->get_option( 'vrts_email_notification_cc_address' ) );
			$headers[] = 'Cc: ' . $notification_email_cc;
		}

		if ( $notification_email ) {
			$sent = wp_mail( $notification_email, $subject, $message, $headers );
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
}
