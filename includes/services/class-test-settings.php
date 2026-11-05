<?php

namespace Vrts\Services;

use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Core\Utilities\Image_Helpers;
use Vrts\Core\Utilities\Url_Helpers;

/**
 * Shared settings payload for server-rendered controls and REST responses.
 */
class Test_Settings {

	/**
	 * Get the settings shown in the test settings modal.
	 *
	 * @param object $test Test.
	 *
	 * @return array
	 */
	public static function get_settings( $test ) {
		$title = html_entity_decode( get_the_title( $test->post_id ), ENT_QUOTES, 'UTF-8' );

		return array_merge( Test::get_custom_code( $test ), [
			'title' => '' !== $title ? $title : __( '(no title)', 'visual-regression-tests' ),
			'path' => Url_Helpers::get_relative_permalink( $test->post_id ),
			'agent' => self::get_agent_context( $test ),
		] );
	}

	/**
	 * Page context for the prompt users can copy to an AI agent.
	 *
	 * @param object $test Test.
	 *
	 * @return array
	 */
	private static function get_agent_context( $test ) {
		$alerts = array_map( function ( $alert ) {
			$meta = maybe_unserialize( $alert->meta );
			$percentage = is_array( $meta ) ? ( $meta['viewport_diff_percentage'] ?? null ) : null;
			$snapshot = Image_Helpers::get_screenshot_url( $alert, 'base' );
			$screenshot = Image_Helpers::get_screenshot_url( $alert, 'target' );

			return [
				'date' => $alert->target_screenshot_finish_date,
				'pixels' => (int) $alert->differences,
				'percentage' => is_numeric( $percentage ) ? round( (float) $percentage, 2 ) : null,
				'false_positive' => (bool) $alert->is_false_positive,
				'snapshot' => $snapshot,
				'screenshot' => $screenshot,
				'diff' => Image_Helpers::get_screenshot_url( $alert, 'comparison' ),
				// The screenshotter stores the captured DOM next to each screenshot.
				'snapshot_html' => self::get_html_url( $snapshot ),
				'screenshot_html' => self::get_html_url( $screenshot ),
			];
		}, Alert::get_latest_items_by_post_id( $test->post_id ) );

		return [
			'url' => get_permalink( $test->post_id ),
			'alert_threshold' => Test::get_global_alert_threshold(),
			'click_selectors' => (string) vrts()->settings()->get_option( 'vrts_click_selectors' ),
			'alerts' => $alerts,
		];
	}

	/**
	 * URL of the HTML captured with a screenshot.
	 *
	 * @param string $screenshot_url Screenshot URL.
	 *
	 * @return string
	 */
	private static function get_html_url( $screenshot_url ) {
		return preg_replace( '/\.png$/i', '.html', (string) $screenshot_url );
	}
}
