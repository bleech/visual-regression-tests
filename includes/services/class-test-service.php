<?php

namespace Vrts\Services;

use Vrts\Tables\Tests_Table;

class Test_Service {

	public function update_test_from_comparison( $alert_id, $test_id, $comparison ) {
		global $wpdb;
		$table_test = Tests_Table::get_table_name();
		// Update test row with new id foreign key and add latest screenshot.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update(
			$table_test,
			[
				'current_alert_id' => $alert_id,
				'target_screenshot_url' => $comparison['screenshot']['image_url'],
				'snapshot_date' => $comparison['updated_at'],
			],
			[ 'service_test_id' => $test_id ]
		);
	}

	public function update_test_from_schedule( $test_id, $screenshot ) {
		global $wpdb;
		$table_test = Tests_Table::get_table_name();
		// Update test row with new id foreign key and add latest screenshot.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->update(
			$table_test,
			[
				'target_screenshot_url' => $screenshot['image_url'],
				'snapshot_date' => $screenshot['updated_at'],
			],
			[ 'service_test_id' => $test_id ]
		);
	}
}
