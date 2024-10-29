<?php

namespace Vrts\Services;

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
			/* translators: %1$s: the id of the test run, %2$s: home url */
			esc_html_x( 'VRTs: Run #%1$s (%2$s)', 'test run notification email subject', 'visual-regression-tests' ),
			$test_run_id,
			esc_url( home_url() )
		);

		$data = $this->get_test_run_email_data( $test_run_id );
		$message = vrts()->get_component( 'emails/test-run', $data );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
		];

		$emails = $this->get_test_run_emails( $data['run'] );

		if ( $emails ) {
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
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
		$tests = maybe_unserialize( $run->tests );
		$alerts = Alert::get_items_by_test_run( $run_id );

		if ( is_array( $tests ) && count( $tests ) > 0 && ! is_array( $tests[0] ) ) {
			$tests = array_map( function( $test ) {
				$test = (int) $test;
				$post_id = Test::get_post_id( $test );
				return [
					'id' => $test,
					'post_id' => $post_id,
					'post_title' => get_the_title( $post_id ),
					'permalink' => get_permalink( $post_id ),
				];
			}, $tests );
		}
		usort( $tests, function( $a, $b ) {
			return $a['post_id'] > $b['post_id'] ? -1 : 1;
		} );

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
