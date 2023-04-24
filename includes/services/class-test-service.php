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
		if ( $comparison['screenshot']['image_url'] ?? null ) {
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
		if ( $screenshot['image_url'] ?? null ) {
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
	 * @param int $post_id Post id.
	 *
	 * @return int|WP_Error
	 */
	public function create_test( $post_id ) {
		if ( Service::is_connected() ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return new WP_Error( 'vrts_post_error', __( 'Post not found.', 'visual-regression-testing-for-wp' ) );
			}
			$test = Test::get_item_by_post_id( $post_id );
			if ( $test ) {
				return $test;
			}
			if ( 'publish' === $post->post_status ) {
				return $this->create_remote_test( $post );
			} elseif ( 'revision' !== $post->post_type && 'auto-draft' !== $post->post_status ) {
				$args = [
					'post_id' => $post_id,
					'status' => 0,
				];
				$new_row_id = Test::save( $args );
				return Test::get_item( $new_row_id );
			}
		} else {
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-testing-for-wp' ) );
		}//end if
		return new WP_Error( 'vrts_service_error', __( 'Error creating test.', 'visual-regression-testing-for-wp' ) );
	}

	/**
	 * Create test remotely via service.
	 *
	 * @param WP_Post $post Post.
	 * @param array   $test Test.
	 *
	 * @return object|WP_Error
	 */
	public function create_remote_test( $post, $test = [] ) {
		if ( Service::is_connected() ) {
			$service_project_id = get_option( 'vrts_project_id' );
			$request_url = 'tests';
			$parameters = [
				'project_id' => $service_project_id,
				'url' => get_permalink( $post ),
				'frequency' => 'daily',
			];
			$service_request = Service::rest_service_request( $request_url, $parameters, 'post' );
			if ( 201 === $service_request['status_code'] ) {
				$test_id = $service_request['response']['id'];
				$args = array_merge($test, [
					'post_id' => $post->ID,
					'service_test_id' => $test_id,
					'status' => 1,
				]);
				unset( $args['id'] );
				// TODO: Add some validation.

				$new_row_id = Test::save( $args, $test['id'] ?? null );
				if ( $new_row_id ) {
					Subscription::decrease_tests_count();
					Cron_Jobs::schedule_initial_fetch_test_updates( $new_row_id );
					return Test::get_item( $new_row_id );
				}
			} else {
				return new WP_Error( 'vrts_service_error', __( 'Service could not create test.', 'visual-regression-testing-for-wp' ) );
			}
		} else {
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-testing-for-wp' ) );
		}//end if
	}

	/**
	 * Delete test.
	 *
	 * @param int|object $test_id Test id or test object.
	 *
	 * @return bool
	 */
	public function delete_test( $test_id ) {
		$delete_locally = true;
		$test = ( is_string( $test_id ) || is_int( $test_id ) )
				? Test::get_item( (int) $test_id )
				: $test_id;
		if ( ! empty( $test->service_test_id ) ) {
			$delete_locally = ! ! $this->delete_remote_test( $test_id );
		}
		if ( ! $delete_locally ) {
			Subscription::get_latest_status();
		}
		return $delete_locally && Test::delete( $test->id );
	}

	/**
	 * Delete test remotely via service.
	 *
	 * @param int|object $test_id Test id or test object.
	 *
	 * @return bool
	 */
	public function delete_remote_test( $test_id ) {
		if ( Service::is_connected() ) {
			$test = ( is_string( $test_id ) || is_int( $test_id ) )
				? Test::get_item( (int) $test_id )
				: $test_id;
			if (
				Service::delete_test( $test->service_test_id )
				&& Subscription::increase_tests_count()
			) {
				$args = (array) $test;
				$args['status'] = 0;
				$args['service_test_id'] = null;
				unset( $args['id'] );
				Test::save( $args, $test->id );
				return $test;
			} else {
				return false;
			}
		}
	}

	/**
	 * Resume stale tests.
	 */
	public function resume_stale_tests() {
		$stale_tests = Test::get_all_inactive();
		foreach ( $stale_tests as $stale_test ) {
			if ( Subscription::get_remaining_tests() > 0 ) {
				$this->resume_test( $stale_test );
			}
		}
	}

	/**
	 * Resume test.
	 *
	 * @param object $test Test.
	 */
	private function resume_test( $test ) {
		if ( empty( $test->service_test_id ) ) {
			$post = get_post( $test->post_id );
			if ( 'publish' === $post->post_status ) {
				$this->create_remote_test( $post, (array) $test );
			}
		}
	}
}
