<?php

namespace Vrts\Services;

use Vrts\Features\Email_Notifications;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Models\Test;
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

	public function update_test_from_api_data( $data ) {
		$test_id = $data['test_id'];
		$post_id = Test::get_post_id_by_service_test_id( $test_id );

		if ( $post_id ) {
			if ( array_key_exists( 'is_paused', $data ) && $data['is_paused'] ) {
				if ( $data['comparison']['pixels_diff'] > 0 ) {
					$comparison = $data['comparison'];
					$alert_service = new Alert_Service();
					$alert_id = $alert_service->create_alert_from_comparison( $post_id, $test_id, $comparison );

					if ( $alert_id ) {
						$test_service = new Test_Service();
						$test_service->update_test_from_comparison( $alert_id, $test_id, $comparison );
						// Send e-mail notification.
						$email_notifications = new Email_Notifications();
						$email_notifications->send_email( $comparison['pixels_diff'], $post_id, $alert_id );
					}
				} //end if
			} elseif ( $data['schedule']['base_screenshot'] ) {
				$this->update_test_from_schedule( $post_id, $test_id, $data['schedule']['base_screenshot'] );
			} //end if
			return true;
		} //end if
	}

	public function fetch_and_update_tests() {
		$service_request = Service::fetch_updates();
		if ( $service_request['status_code'] === 200 ) {
			$response = $service_request['response'];
			if ( array_key_exists( 'updates', $response ) ) {
				$updates = $response['updates'];
				foreach ( $updates as $update ) {
					$this->update_test_from_api_data( $update );
				}
			}
			if (
				array_key_exists( 'remaining_credits', $response )
				&& array_key_exists( 'total_credits', $response )
				&& array_key_exists( 'has_subscription', $response )
			) {
				Subscription::update_available_tests( $response['remaining_credits'], $response['total_credits'], $response['has_subscription'] );
			}
		}
	}
}
