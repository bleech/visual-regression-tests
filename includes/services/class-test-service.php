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
	 * @param array $data Comparison data.
	 *
	 * @return int|false
	 */
	public function update_test_from_comparison( $alert_id, $test_id, $data ) {
		global $wpdb;
		$table_test = Tests_Table::get_table_name();
		$comparison = $data['comparison'];
		if ( $comparison['screenshot']['image_url'] ?? null ) {
			// Update test row with new id foreign key and add latest screenshot.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			return $wpdb->update(
				$table_test,
				[
					'current_alert_id' => $alert_id,
					'next_run_date' => $data['next_run_at'] ?? '',
					'last_comparison_date' => $comparison['updated_at'],
					'is_running' => false,
				],
				[ 'service_test_id' => $test_id ]
			);
		}
	}

	/**
	 * Update test from schedule.
	 *
	 * @param int   $test_id Test id.
	 * @param array $data Screenshot data.
	 *
	 * @return void
	 */
	public function update_test_from_schedule( $test_id, $data ) {
		global $wpdb;
		$table_test = Tests_Table::get_table_name();
		$screenshot = $data['schedule']['base_screenshot'];
		if ( $screenshot['image_url'] ?? null ) {
			// Update test row with new id foreign key and add latest screenshot.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			$wpdb->update(
				$table_test,
				[
					'base_screenshot_url' => $screenshot['image_url'],
					'base_screenshot_date' => $screenshot['updated_at'],
					'next_run_date' => $data['next_run_at'] ?? '',
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
			if ( $data['schedule']['base_screenshot'] ?? null ) {
				$this->update_test_from_schedule( $test_id, $data );
			} elseif ( $data['comparison'] ?? null ) {
				$alert_id = null;
				if ( $data['is_paused'] ?? null && $data['comparison']['pixels_diff'] > 1 ) {
					$comparison = $data['comparison'];
					$alert_service = new Alert_Service();
					$alert_id = $alert_service->create_alert_from_comparison( $post_id, $test_id, $comparison );
				}//end if
				$test_service = new Test_Service();
				$test_service->update_test_from_comparison( $alert_id, $test_id, $data );
				if ( $alert_id ) {
					// Send e-mail notification.
					$email_notifications = new Email_Notifications();
					$email_notifications->send_email( $comparison['pixels_diff'], $post_id, $alert_id );
				}//end if
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
				&& array_key_exists( 'tier_id', $response )
			) {
				Subscription::update_available_tests( $response['remaining_credits'], $response['total_credits'], $response['has_subscription'], $response['tier_id'] );
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
				return new WP_Error( 'vrts_post_error', __( 'Post not found.', 'visual-regression-tests' ) );
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
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-tests' ) );
		}//end if
		return new WP_Error( 'vrts_service_error', __( 'Error creating test.', 'visual-regression-tests' ) );
	}

	/**
	 * Create tests.
	 *
	 * @param array $post_ids Post ids.
	 *
	 * @return array|WP_Error
	 */
	public function create_tests( $post_ids ) {
		if ( Service::is_connected() ) {
			$created_tests = [];
			$post_types = vrts()->get_public_post_types();
			$posts = get_posts( [
				'post__in' => $post_ids,
				'post_type' => $post_types,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			] );

			if ( ! $posts ) {
				return new WP_Error( 'vrts_posts_error', __( 'Posts not found.', 'visual-regression-tests' ) );
			}

			$post_ids = wp_list_pluck( $posts, 'ID' );
			$remaining_tests = get_option( 'vrts_remaining_tests' );
			$existing_tests = Test::get_items_by_post_ids( $post_ids );
			$existing_tests_posts_ids = array_map( 'intval', wp_list_pluck( $existing_tests, 'post_id' ) );

			// Remove posts that already have tests.
			$posts = array_filter( $posts, function( $post ) use ( $existing_tests_posts_ids ) {
				return ! in_array( $post->ID, $existing_tests_posts_ids, true );
			} );

			if ( ! $posts ) {
				return $existing_tests;
			}

			// Only try to create as many remote tests as we have remaining.
			$published_posts = array_slice( array_filter( $posts, function( $post ) {
				return 'publish' === $post->post_status;
			} ), 0, $remaining_tests );

			$not_published_posts = array_filter( $posts, function( $post ) {
				return 'publish' !== $post->post_status && 'revision' !== $post->post_type && 'auto-draft' !== $post->post_status;
			} );

			if ( $published_posts ) {
				$published_posts_ids = wp_list_pluck( $published_posts, 'ID' );
				$remote_tests = $this->create_remote_tests( $published_posts_ids );
				if ( ! is_wp_error( $remote_tests ) ) {
					$created_tests = $remote_tests;
				}
			}

			if ( $not_published_posts ) {
				$args = array_values( array_map(function( $post ) {
					return [
						'post_id' => $post->ID,
						'status' => 0,
					];
				}, $not_published_posts) );

				$is_saved = Test::save_multiple( $args );

				if ( $is_saved ) {
					$not_published_posts_ids = wp_list_pluck( $not_published_posts, 'ID' );
					$created_tests = array_merge( $created_tests, Test::get_items_by_post_ids( $not_published_posts_ids ) );
				}
			}

			return $created_tests;
		} else {
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-tests' ) );
		}//end if
		return new WP_Error( 'vrts_service_error', __( 'Error creating test.', 'visual-regression-tests' ) );
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
			$existing_test = Test::get_item_by_post_id( $post->ID );
			if ( $existing_test ) {
				return $existing_test;
			}
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
				return new WP_Error( 'vrts_service_error', __( 'Service could not create test.', 'visual-regression-tests' ) );
			}
		} else {
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-tests' ) );
		}//end if
	}

	/**
	 * Create tests remotely via service.
	 *
	 * @param array $post_ids Post IDs.
	 *
	 * @return array|WP_Error
	 */
	public function create_remote_tests( $post_ids ) {
		if ( Service::is_connected() ) {
			$service_project_id = get_option( 'vrts_project_id' );
			$request_url = 'tests';

			$urls = array_combine( $post_ids, array_map( function( $post_id ) {
				return get_permalink( $post_id );
			}, $post_ids ) );

			$parameters = [
				'project_id' => $service_project_id,
				'urls' => array_values( $urls ),
				'frequency' => 'daily',
			];

			$service_request = Service::rest_service_request( $request_url, $parameters, 'post' );

			if ( 201 === $service_request['status_code'] ) {
				$args = [];

				foreach ( $service_request['response'] as $test ) {
					$args[] = [
						'post_id' => array_search( $test['url'], $urls, true ),
						'service_test_id' => $test['id'],
						'status' => 1,
					];
				}

				$saved_tests_number = Test::save_multiple( $args );

				if ( $saved_tests_number ) {
					Subscription::decrease_tests_count( $saved_tests_number );

					$post_ids = wp_list_pluck( $args, 'post_id' );
					$created_tests = Test::get_items_by_post_ids( $post_ids );

					foreach ( $created_tests as $test ) {
						Cron_Jobs::schedule_initial_fetch_test_updates( $test->id );
					}

					return $created_tests;
				} else {
					return new WP_Error( 'vrts_test_error', __( 'Plugin could not save tests.', 'visual-regression-tests' ) );
				}
			} else {
				return new WP_Error( 'vrts_service_error', __( 'Service could not create tests.', 'visual-regression-tests' ) );
			}//end if
		} else {
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-tests' ) );
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
				$this->resume_stale_test( $stale_test );
			}
		}
	}

	/**
	 * Resume stale test.
	 *
	 * @param object $test Test.
	 */
	private function resume_stale_test( $test ) {
		if ( empty( $test->service_test_id ) ) {
			$post = get_post( $test->post_id );
			if ( 'publish' === $post->post_status ) {
				$this->create_remote_test( $post, (array) $test );
			}
		}
	}

	/**
	 * Update test.
	 *
	 * @param int    $test_id Test id.
	 * @param string $css_hide_selector CSS selector to hide.
	 *
	 * @return int|WP_Error
	 */
	public function update_css_hide_selectors( $test_id, $css_hide_selector ) {
		if ( Service::is_connected() ) {
			$test = Test::get_item( $test_id );
			if ( ! $test ) {
				return new WP_Error( 'vrts_test_error', __( 'Test not found.', 'visual-regression-tests' ) );
			}

			$updated = Service::update_test(
				$test->service_test_id,
				[ 'options' => [ 'hideSelectors' => $css_hide_selector ] ]
			);
			if ( $updated ) {
				return Test::save_hide_css_selectors( $test_id, $css_hide_selector );
			} else {
				return new WP_Error( 'vrts_service_error', __( 'Service could not update test.', 'visual-regression-tests' ) );
			}
		} else {
			return new WP_Error( 'vrts_service_error', __( 'Service is not connected.', 'visual-regression-tests' ) );
		}
	}

	/**
	 * Resume test.
	 *
	 * @param int $post_id Post id.
	 */
	public function resume_test( $post_id ) {
		$test = Test::get_item_by_post_id( $post_id );
		if ( ! $test ) {
			return false;
		} else {
			Test::reset_base_screenshot( $test->id );
			Service::resume_test( $post_id );
		}
	}
}
