<?php

namespace Vrts\Services;

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;

class Email_Service {

	/**
	 * Send Test Run email.
	 *
	 * @param int $test_run_id the id of the test run.
	 *
	 * @return bool
	 */
	public function send_test_run_email( $test_run_id ) {
		$subject = sprintf(
			/* translators: %1$s: the id of the alert, %2$s: the test url */
			esc_html_x( 'VRTs: Alert for Test Run #%1$s', 'test run notification email subject', 'visual-regression-tests' ),
			$test_run_id
		);

		$data = $this->get_test_run_email_data( $test_run_id );
		$message = vrts()->get_component( 'emails/test-run', $data );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
		];

		$emails = $this->get_test_run_emails( $data['run'] );

		if ( $emails ) {
			return wp_mail( $emails, wp_specialchars_decode( $subject ), $message, $headers );
		}

		return false;
	}

	/**
	 * Get the emails for the test run.
	 *
	 * @param object $run the test run.
	 *
	 * @return array Email addresses.
	 */
	private function get_test_run_emails( $run ) {
		$emails = [];
		$trigger = $run->trigger;

		switch ( $trigger ) {
			case 'manual':
				$meta = maybe_unserialize( $run->trigger_meta );
				$user = get_user_by( 'ID', $meta['user_id'] ?? 0 );
				if ( $user ) {
					$emails = [ $user->user_email ];
				}
				break;
			case 'api':
				$emails = vrts()->settings()->get_option( 'vrts_email_api_notification_address' );
				break;
			case 'update':
				$emails = vrts()->settings()->get_option( 'vrts_email_update_notification_address' );
				break;
			default:
				$emails = vrts()->settings()->get_option( 'vrts_email_notification_address' );
				break;
		}

		return $emails;
	}

	/**
	 * Get the data for the test run email.
	 *
	 * @param int $run_id the id of the test run.
	 */
	private function get_test_run_email_data( $run_id ) {
		$run = Test_Run::get_item( $run_id );
		$tests = Test::get_items_by_ids( maybe_unserialize( $run->tests ) );
		$alerts = Alert::get_items_by_test_run( $run_id );

		$data = [
			'run' => $run,
			'tests' => $tests,
			'alerts' => $alerts,
		];

		return $data;
	}

	/**
	 * Preview test run email.
	 *
	 * @param int $run_id the id of the test run.
	 */
	public function preview_test_run_email( $run_id ) {
		$data = $this->get_test_run_email_data( $run_id );
		vrts()->component( 'emails/test-run', $data );
	}
}
