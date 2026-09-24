<?php

namespace Vrts\Services;

use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Tables\Alerts_Table;

class Alert_Service {

	/**
	 * Whether a comparison differs enough to become an alert.
	 *
	 * Without a threshold any comparison with more than one changed pixel alerts.
	 * With a threshold the changed share of the most-changed screen of the page,
	 * as measured by the screenshotter, has to exceed it.
	 *
	 * @param int   $alert_threshold Threshold in percent, 0 for any change.
	 * @param array $comparison Comparison data from the service.
	 *
	 * @return bool
	 */
	public static function exceeds_alert_threshold( $alert_threshold, $comparison ) {
		$pixels_diff = (int) ( $comparison['pixels_diff'] ?? 0 );
		$viewport_diff_percentage = $comparison['meta']['viewport_diff_percentage'] ?? null;

		if ( ! $alert_threshold ) {
			return $pixels_diff > 1;
		}

		if ( ! is_numeric( $viewport_diff_percentage ) ) {
			return $pixels_diff > 1;
		}

		return (float) $viewport_diff_percentage > $alert_threshold;
	}

	/**
	 * Whether a comparison should become an alert for the given test.
	 *
	 * A comparison that already has an alert (created by the other delivery
	 * path, webhook or poll) always does, whatever the current threshold is.
	 *
	 * @param string $test_id Service test id.
	 * @param array  $comparison Comparison data from the service.
	 *
	 * @return bool
	 */
	public static function should_alert( $test_id, $comparison ) {
		if ( ! empty( $comparison['matches_false_positive'] ) ) {
			return false;
		}
		if ( ! empty( $comparison['id'] ) && Alert::get_item_by_comparison_id( $comparison['id'] ) ) {
			return true;
		}
		return self::exceeds_alert_threshold( Test::get_alert_threshold_by_service_test_id( $test_id ), $comparison );
	}

	/**
	 * Create alert from comparison.
	 *
	 * @param int    $post_id Post ID.
	 * @param int    $test_id Test ID.
	 * @param array  $comparison Comparison.
	 * @param object $test_run Test run.
	 */
	public function create_alert_from_comparison( $post_id, $test_id, $comparison, $test_run = null ) {
		global $wpdb;
		$table_alert = Alerts_Table::get_table_name();
		$comparison_id = empty( $comparison['id'] ) ? null : $comparison['id'];

		// The same comparison can arrive both via webhook (with a test run) and via the
		// hourly poll fallback (without one) — reuse the existing alert instead of
		// creating an orphaned duplicate.
		$existing_alert = $comparison_id ? Alert::get_item_by_comparison_id( $comparison_id ) : null;

		if ( $existing_alert ) {
			return $this->adopt_alert( $existing_alert, $test_run );
		}

		$prepare_alert = [];
		$prepare_alert['post_id'] = $post_id;
		$prepare_alert['screenshot_test_id'] = $test_id;
		$prepare_alert['target_screenshot_url'] = $comparison['screenshot']['image_url'];
		$prepare_alert['target_screenshot_finish_date'] = $comparison['screenshot']['updated_at'];
		$prepare_alert['base_screenshot_url'] = $comparison['base_screenshot']['image_url'];
		$prepare_alert['base_screenshot_finish_date'] = $comparison['base_screenshot']['updated_at'];
		$prepare_alert['comparison_screenshot_url'] = $comparison['image_url'];
		$prepare_alert['comparison_id'] = $comparison_id;
		$prepare_alert['differences'] = $comparison['pixels_diff'];
		$prepare_alert['test_run_id'] = $test_run ? $test_run->id : null;
		$prepare_alert['meta'] = maybe_serialize( $comparison['meta'] ?? [] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- It's ok.
		if ( $wpdb->insert( $table_alert, $prepare_alert ) ) {
			$alert_id = $wpdb->insert_id;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			$wpdb->update(
				$table_alert,
				[ 'title' => '#' . $alert_id ],
				[ 'id' => $alert_id ]
			);

			return $alert_id;
		}

		// The unique key on comparison_id rejected a concurrent duplicate —
		// adopt the row the other request just created.
		$existing_alert = $comparison_id ? Alert::get_item_by_comparison_id( $comparison_id ) : null;

		return $existing_alert ? $this->adopt_alert( $existing_alert, $test_run ) : null;
	}

	/**
	 * Link an existing alert to a test run when it has none.
	 *
	 * @param object      $alert Alert.
	 * @param object|null $test_run Test run.
	 *
	 * @return int
	 */
	protected function adopt_alert( $alert, $test_run ) {
		global $wpdb;

		if ( empty( $alert->test_run_id ) && $test_run ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			$wpdb->update(
				Alerts_Table::get_table_name(),
				[ 'test_run_id' => $test_run->id ],
				[ 'id' => $alert->id ]
			);
		}

		return $alert->id;
	}
}
