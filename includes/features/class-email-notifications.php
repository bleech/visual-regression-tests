<?php

namespace Vrts\Features;

use Vrts\Models\Alert;

class Email_Notifications {

	/**
	 * Send email.
	 *
	 *  @param int $differences the number of differences.
	 *  @param int $post_id the id of the post.
	 *  @param int $alert_id the id of the alert.
	 */
	public function send_email( $differences, $post_id, $alert_id ) {
		$notification_emails = $this->sanitize_multiple_emails( vrts()->settings()->get_option( 'vrts_email_notification_address' ) );
		$home_url = get_site_url();
		$admin_url = get_admin_url();

		// Check if notification email already exists.
		$subject = 'VRTs: Alert #' . $alert_id . ' (' . esc_url( $home_url ) . ')';
		$message = "Howdy,\n\n" .
		"New visual differences have been detected on a page.\n\n" .
		"Review and resolve the alert to resume testing:\n" .
		esc_url( $admin_url ) . 'admin.php?page=vrts-alerts&action=edit&alert_id=' . $alert_id . "\n\n" .
		'This alert was sent by the Visual Regression Tests plugin on ' . esc_url( $home_url );

		if ( $notification_emails ) {
			$sent = wp_mail( $notification_emails, $subject, $message );
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
