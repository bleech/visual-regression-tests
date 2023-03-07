<?php

namespace Vrts\Services;

use Vrts\Features\Cron_Jobs;
use Vrts\Features\Email_Notifications;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Models\Test;
use Vrts\Tables\Tests_Table;
use WP_Error;

class Test_Service {

	/**
	 * Update tests from comparison.
	 *
	 * @param int   $alert_id Alert id.
	 * @param int   $test_id Test id.
	 * @param array $comparison Comparison.
	 *
	 * @return int|false
	 */
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

	/**
	 * Update test from schedule.
	 *
	 * @param int   $test_id Test id.
	 * @param array $screenshot Screenshot.
	 *
	 * @return void
	 */
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

	/**
	 * Create test from API data.
	 *
	 * @param array $data Data.
	 *
	 * @return int|false
	 */
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
				}//end if
			} elseif ( $data['schedule']['base_screenshot'] ) {
				$this->update_test_from_schedule( $test_id, $data['schedule']['base_screenshot'] );
			}//end if
			return true;
		}//end if
	}

	/**
	 * Fetch and update tests.
	 *
	 * @return void
	 */
	public function fetch_and_update_tests() {
		$service_request = Service::fetch_updates();
		if ( 200 === $service_request['status_code'] ) {
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

	/**
	 * Create test.
	 *
	 * @param array $args Arguments.
	 *
	 * @return int|WP_Error
	 */
	public function create_test( $args = [] ) {
		if ( Service::is_connected() ) {
			$defaults = [
				'id' => null,
				'status' => 0,
				'post_id' => null,
			];
			$service_project_id = get_option( 'vrts_project_id' );
			$args = wp_parse_args( $args, $defaults );
			$post_id = $args['post_id'];
			$request_url = 'tests';
			$parameters = [
				'project_id' => $service_project_id,
				'url' => get_permalink( $post_id ),
				'frequency' => 'daily',
			];
			$service_request = Service::rest_service_request( $request_url, $parameters, 'post' );
			if ( 201 === $service_request['status_code'] ) {
				$test_id = $service_request['response']['id'];
				$args['service_test_id'] = $test_id;
				// TODO: Add some validation.

				// Remove row and post id to determine if new or update.
				$row_id = (int) $args['id'];
				unset( $args['id'] );
				$new_row_id = Test::save( $args, $row_id );
				if ( $new_row_id ) {
					Subscription::decrease_tests_count();
					Cron_Jobs::schedule_initial_fetch_test_updates( $new_row_id );
					return $new_row_id;
				}
			}//end if
		}//end if

		// TODO: handle other errors as well.
		return new WP_Error(
			'no_credits',
			/* translators: %s: the id of the post. */
			sprintf( esc_html__( 'Oops, we ran out of testing pages. Page id %s couln’t be added as a test.' ), $post_id )
		);
	}
}
