<?php

namespace Vrts\Services;

use Vrts\Tables\Alerts_Table;

class Alert_Service {

	/**
	 * Create alert from comparison.
	 *
	 * @param int   $post_id Post ID.
	 * @param int   $test_id Test ID.
	 * @param array $comparison Comparison.
	 */
	public function create_alert_from_comparison( $post_id, $test_id, $comparison ) {
		global $wpdb;
		$table_alert = Alerts_Table::get_table_name();

		$prepare_alert = [];
		$prepare_alert['post_id'] = $post_id;
		$prepare_alert['screenshot_test_id'] = $test_id;
		$prepare_alert['target_screenshot_url'] = $comparison['screenshot']['image_url'];
		$prepare_alert['target_screenshot_finish_date'] = $comparison['screenshot']['updated_at'];
		$prepare_alert['base_screenshot_url'] = $comparison['base_screenshot']['image_url'];
		$prepare_alert['base_screenshot_finish_date'] = $comparison['base_screenshot']['updated_at'];
		$prepare_alert['comparison_screenshot_url'] = $comparison['image_url'];
		$prepare_alert['differences'] = $comparison['pixels_diff'];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- It's ok.
		if ( $wpdb->insert( $table_alert, $prepare_alert ) ) {
			$alert_id = $wpdb->insert_id;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			$wpdb->update(
				$table_alert,
				[ 'title' => '#' . $alert_id ],
				[ 'id' => $alert_id ]
			);
		}

		return $alert_id;
	}
}
