<?php

namespace Vrts\Services;

use Vrts\Core\Utilities\Image_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Features\Subscription;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;

/**
 * Posts the alerts of a finished test run as JSON to the webhook URL from the settings.
 */
class Webhook_Service {

	/**
	 * Send the webhook for a test run, once, like the notification email. Not for manual runs.
	 *
	 * @param int $test_run_id Test run id.
	 */
	public function send_test_run_webhook( $test_run_id ) {
		$url = vrts()->settings()->get_option( 'vrts_webhook_url' );
		$test_run = Test_Run::get_item( $test_run_id );

		// Schedule runs continue without Pro, so check the subscription here too.
		if ( ! $url || ! Subscription::get_subscription_status() || ! $test_run || 'manual' === $test_run->trigger ) {
			return;
		}

		$alerts = Alert::get_items_by_test_run( $test_run->id );

		if ( ! $alerts ) {
			return;
		}

		// Blocking, so slow receivers get up to five seconds: WordPress raises a
		// non-blocking request's timeout to one second and still waits within it.
		wp_remote_post( $url, [
			'timeout' => 5,
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body' => wp_json_encode( $this->get_payload( $test_run, $alerts ) ),
		] );
	}

	/**
	 * Get the webhook payload.
	 *
	 * @param object $test_run Test run.
	 * @param array  $alerts Alerts of the test run.
	 *
	 * @return array
	 */
	private function get_payload( $test_run, $alerts ) {
		$tests = maybe_unserialize( $test_run->tests );
		$tests_count = is_array( $tests ) ? count( $tests ) : 0;

		return [
			'message' => $this->get_message( $test_run, count( $alerts ), $tests_count ),
			'source' => 'VRTs',
			'version' => 1,
			'event_id' => 'vrts-run-' . $test_run->service_test_run_id,
			'site' => [
				'name' => html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES, 'UTF-8' ),
				'url' => home_url( '/' ),
				'alert_threshold' => Test::get_global_alert_threshold(),
				'click_selectors' => (string) vrts()->settings()->get_option( 'vrts_click_selectors' ),
			],
			'run' => [
				'id' => (int) $test_run->id,
				'trigger' => $test_run->trigger,
				'trigger_note' => Test_Run::get_trigger_note( $test_run ),
				'started_at' => $this->get_iso_date( $test_run->started_at ),
				'finished_at' => $this->get_iso_date( $test_run->finished_at ),
				'tests_count' => $tests_count,
				'admin_url' => Url_Helpers::get_test_run_page( $test_run ),
			],
			'alerts' => array_map( function ( $alert ) use ( $test_run ) {
				return $this->get_alert_data( $alert, $test_run );
			}, $alerts ),
		];
	}

	/**
	 * One-line summary, usable as an agent's task or as a chat message.
	 *
	 * Kept in English: it is read by agents and integrations, not shown in the UI.
	 *
	 * @param object $test_run Test run.
	 * @param int    $alerts_count Number of alerts.
	 * @param int    $tests_count Number of tests in the run.
	 *
	 * @return string
	 */
	private function get_message( $test_run, $alerts_count, $tests_count ) {
		$trigger_phrases = [
			'scheduled' => 'in a scheduled run',
			'update' => 'after updates',
			'api' => 'in a run started via the API',
		];

		return sprintf(
			'VRTs found visual changes on %1$d of %2$d %3$s of %4$s %5$s. Compare the before and after screenshots and report whether each change is intended, a regression or noise (like animations, sliders or cookie banners).',
			$alerts_count,
			$tests_count,
			1 === $tests_count ? 'page' : 'pages',
			wp_parse_url( home_url(), PHP_URL_HOST ),
			$trigger_phrases[ $test_run->trigger ] ?? 'in run #' . $test_run->id
		);
	}

	/**
	 * Get the payload data of an alert.
	 *
	 * @param object $alert Alert.
	 * @param object $test_run Test run.
	 *
	 * @return array
	 */
	private function get_alert_data( $alert, $test_run ) {
		$test = Test::get_item_by_post_id( $alert->post_id );
		$before_image_url = Image_Helpers::get_screenshot_url( $alert, 'base' );
		$after_image_url = Image_Helpers::get_screenshot_url( $alert, 'target' );
		// Skip this alert and any captured after it: runs can be processed out of order.
		$previous_alerts = array_filter( Alert::get_latest_items_by_post_id( $alert->post_id, 4 ), function ( $previous_alert ) use ( $alert ) {
			return $previous_alert->target_screenshot_finish_date < $alert->target_screenshot_finish_date;
		} );

		return [
			'id' => (int) $alert->id,
			'admin_url' => Url_Helpers::get_alert_page( $alert->id, $test_run->id ),
			'page' => [
				'title' => html_entity_decode( get_the_title( $alert->post_id ), ENT_QUOTES, 'UTF-8' ),
				'url' => get_permalink( $alert->post_id ) ?: null,
			],
			'pixels_diff' => (int) $alert->differences,
			'viewport_diff_percentage' => $this->get_viewport_diff_percentage( $alert ),
			'before' => [
				'taken_at' => $this->get_iso_date( $alert->base_screenshot_finish_date ),
				'image_url' => $before_image_url,
				'html_url' => Image_Helpers::get_html_url( $before_image_url ),
			],
			'after' => [
				'taken_at' => $this->get_iso_date( $alert->target_screenshot_finish_date ),
				'image_url' => $after_image_url,
				'html_url' => Image_Helpers::get_html_url( $after_image_url ),
			],
			'diff_image_url' => Image_Helpers::get_screenshot_url( $alert, 'comparison' ),
			'test_settings' => $test ? Test::get_custom_code( $test ) : null,
			'previous_alerts' => array_values( array_map( function ( $previous_alert ) {
				return [
					'taken_at' => $this->get_iso_date( $previous_alert->target_screenshot_finish_date ),
					'viewport_diff_percentage' => $this->get_viewport_diff_percentage( $previous_alert ),
					'diff_image_url' => Image_Helpers::get_screenshot_url( $previous_alert, 'comparison' ),
				];
			}, array_slice( $previous_alerts, 0, 3 ) ) ),
		];
	}

	/**
	 * Changed share of the most-changed screen, as measured by the screenshotter.
	 *
	 * Not rounded: an alert at 1.004% with a 1% threshold must not read as 1%.
	 *
	 * @param object $alert Alert.
	 *
	 * @return float|null
	 */
	private function get_viewport_diff_percentage( $alert ) {
		$meta = maybe_unserialize( $alert->meta );
		$percentage = is_array( $meta ) ? ( $meta['viewport_diff_percentage'] ?? null ) : null;

		return is_numeric( $percentage ) ? (float) $percentage : null;
	}

	/**
	 * Format a date stored in UTC as ISO 8601.
	 *
	 * @param string|null $date Date in UTC.
	 *
	 * @return string|null
	 */
	private function get_iso_date( $date ) {
		return $date ? gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $date . ' UTC' ) ) : null;
	}
}
