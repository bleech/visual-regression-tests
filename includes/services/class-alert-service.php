<?php

namespace Vrts\Services;

use Vrts\Models\Alert;
use Vrts\Tables\Alerts_Table;

class Alert_Service {

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
