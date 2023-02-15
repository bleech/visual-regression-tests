<?php

namespace Vrts\Models;

use Vrts\Features\Metaboxes;
use Vrts\Tables\Tests_Table;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use WP_Error;

/**
 * Model Tests Page.
 */
class Test {

	/**
	 * Get all test items from database
	 *
	 * @param array $args Optional.
	 *
	 * @return object
	 */
	public static function get_items( $args = [] ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		$defaults = [
			'number' => 20,
			'offset' => 0,
			'orderby' => 'id',
			'order' => 'DESC',
		];

		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE 1=1';

		if ( null !== $args['s'] ) {
			$where .= $wpdb->prepare(
				' AND posts.post_title LIKE %s',
				'%' . $wpdb->esc_like( $args['s'] ) . '%'
			);
		}

		if ( null !== $args['filter_status'] ) {
			// current_alert_id IS NOT NULL = Pause.
			if ( 'paused' === $args['filter_status'] ) {
				$where .= ' AND tests.current_alert_id IS NOT NULL';
			}
			// current_alert_id IS NULL = Running.
			if ( 'running' === $args['filter_status'] ) {
				$where .= ' AND tests.current_alert_id IS NULL';
			}
		}

		$whitelist_orderby = [ 'id', 'post_title', 'status', 'snapshot_date' ];
		$whitelist_order = [ 'ASC', 'DESC' ];

		$orderby = in_array( $args['orderby'], $whitelist_orderby, true ) ? $args['orderby'] : 'id';
		$order = in_array( $args['order'], $whitelist_order, true ) ? $args['order'] : 'DESC';

		$orderby = "ORDER BY $orderby $order";

		$limit = $args['number'] > 100 ? 100 : $args['number'];

		$limits = $wpdb->prepare(
			'LIMIT %d, %d',
			$args['offset'],
			$limit
		);

		$query = "
			SELECT
				tests.id, tests.status, tests.snapshot_date, tests.post_id, tests.current_alert_id, tests.hide_css_selectors,
				posts.post_title
			FROM $tests_table as tests
			INNER JOIN $wpdb->posts as posts ON posts.id = tests.post_id
			$where
			$orderby
			$limits
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared above.
		$items = $wpdb->get_results( $query );

		return $items;
	}

	/**
	 * Get a single test from database
	 *
	 * @param int $id the id of the item.
	 *
	 * @return array
	 */
	public static function get_item( $id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $tests_table WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Get a single test from database
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return array
	 */
	public static function get_item_id( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT id FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);
	}

	/**
	 * Get the id of the alert
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return int
	 */
	public static function get_alert_id( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT current_alert_id FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);
	}

	/**
	 * Get post id by test id
	 *
	 * @param int $snapshot_test_id the id of the post.
	 *
	 * @return int
	 */
	public static function get_post_id( $snapshot_test_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT post_id FROM $tests_table WHERE snapshot_test_id = %d",
				$snapshot_test_id
			)
		);
	}

	/**
	 * Get post id by service test id
	 *
	 * @param int $service_test_id the id of the test.
	 *
	 * @return int
	 */
	public static function get_post_id_by_service_test_id( $service_test_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT post_id FROM $tests_table WHERE service_test_id = %s",
				$service_test_id
			)
		);
	}

	/**
	 * Get service test id by post id
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return int
	 */
	public static function get_service_test_id_by_post_id( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$service_test_id = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT service_test_id FROM $tests_table WHERE post_id = %s",
				$post_id
			)
		);

		return $service_test_id;
	}

	/**
	 * Does an alert exits?
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return boolean
	 */
	public static function has_post_alert( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$current_alert_id = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT current_alert_id FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);

		return null === $current_alert_id ? false : true;
	}

	/**
	 * Get the target screenshot url
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return string
	 */
	public static function get_target_screenshot_url( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT target_screenshot_url FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);
	}

	/**
	 * Get the target snapshot date
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return string
	 */
	public static function get_snapshot_date( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT snapshot_date FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);
	}

	/**
	 * Get total test items from database
	 *
	 * @param int $filter_status_query the id of status.
	 *
	 * @return array
	 */
	public static function get_total_items( $filter_status_query = null ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();
		$query = "SELECT COUNT(*) FROM $tests_table";

		if ( null !== $filter_status_query ) {
			// current_alert_id IS NOT NULL = Pause.
			if ( 'paused' === $filter_status_query ) {
				$query .= ' WHERE current_alert_id IS NOT NULL';
			}
			// current_alert_id IS NULL = Running.
			if ( 'running' === $filter_status_query ) {
				$query .= ' WHERE current_alert_id IS NULL';
			}
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- It's prepared above
		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Insert or update test data
	 *
	 * @param array $args The arguments to insert.
	 */
	public static function save( $args = [] ) {
		if ( Service::is_connected() ) {
			global $wpdb;

			$tests_table = Tests_Table::get_table_name();
			$defaults = [
				'id' => null,
				'status' => 0,
				'post_id' => null,
			];

			$service_project_id = get_option( 'vrts_project_id' );
			$click_selectors = vrts()->settings()->get_option( 'vrts_click_selectors' );
			$args = wp_parse_args( $args, $defaults );
			$post_id = $args['post_id'];
			$request_url = 'tests';
			$parameters = [
				'project_id' => $service_project_id,
				'url' => get_permalink( $post_id ),
				'frequency' => 'daily',
			];
			$response_data = Service::rest_service_request( $request_url, $parameters, 'post' );
			$response_body = json_decode( $response_data['response']['body'], true );
			$response_code = $response_data['status_code'];
			if ( 201 === $response_code ) {
				$test_id = $response_body['id'];
				$args['service_test_id'] = $test_id;
				// TODO: Add some validation.

				// Remove row and post id to determine if new or update.
				$row_id = (int) $args['id'];
				unset( $args['id'] );
				if ( ! $row_id ) {
					// Insert a new row.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- It's ok.
					if ( $wpdb->insert( $tests_table, $args ) ) {
						Subscription::decrease_tests_count();
						return $wpdb->insert_id;
					}
				} else {
					// Update existing row.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
					if ( $wpdb->update( $tests_table, $args, [ 'id' => $row_id ] ) ) {
						Subscription::decrease_tests_count();
						return $row_id;
					}
				}
			}//end if
		}//end if

		return new WP_Error(
			'no_credits',
			/* translators: %s: the id of the post. */
			sprintf( esc_html__( 'Oops, we ran out of testing pages. Page id %s couln’t be added as a test.' ), $post_id )
		);
	}

	/**
	 * Save hide CSS selectors for a test.
	 *
	 * @param int    $id Test ID.
	 * @param string $hide_css_selectors Hide CSS selectors.
	 * @return bool
	 */
	public static function save_hide_css_selectors( $id = 0, $hide_css_selectors = '' ) {
		if ( ! $id ) {
			return false;
		}

		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$result = $wpdb->update(
			$tests_table,
			[
				'hide_css_selectors' => '' === $hide_css_selectors ? null : $hide_css_selectors,
			],
			[
				'id' => $id,
			]
		);

		if ( false === $result ) {
			return false;
		}

		return true;
	}

	/**
	 * Get active test ids
	 *
	 * @return array
	 */
	public static function get_active_test_ids() {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();
		$query = "SELECT service_test_id FROM $tests_table";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- It's ok.
		return $wpdb->get_col( $query );
	}

	/**
	 * Set alert for a test.
	 *
	 * @param int $post_id The id of the post.
	 * @param int $alert_id The id of the alert.
	 */
	public static function set_alert( $post_id = 0, $alert_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();
		$data = [ 'current_alert_id' => $alert_id ];
		$where = [ 'post_id' => $post_id ];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update( $tests_table, $data, $where );
	}

	/**
	 * Get post status
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return boolean
	 */
	public static function get_status( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$post_status = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT status FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);

		return $post_status;
	}

	/**
	 * Pause test.
	 *
	 * @param int $service_test_id The service test id.
	 */
	public static function pause( $service_test_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();
		$data = [ 'status' => false ];
		$where = [ 'service_test_id' => $service_test_id ];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update( $tests_table, $data, $where );
	}

	/**
	 * Unpause test.
	 *
	 * @param int $service_test_id The service test id.
	 */
	public static function unpause( $service_test_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();
		$data = [ 'status' => 1 ];
		$where = [ 'service_test_id' => $service_test_id ];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update( $tests_table, $data, $where );
	}

	/**
	 * Delete a test from database and update its post meta.
	 *
	 * @param int $post_id the id of the item.
	 *
	 * @return array
	 */
	public static function delete( $post_id = 0 ) {
		if ( Service::is_connected() ) {
			global $wpdb;

			$tests_table = Tests_Table::get_table_name();

			// Field value must set to 0 to be sure that a default value is compatible with gutenberg.
			update_post_meta(
				$post_id,
				Metaboxes::get_post_meta_key_status(),
				0
			);

			delete_post_meta(
				$post_id,
				Metaboxes::get_post_meta_key_is_new_test()
			);

			Service::delete_test( $post_id );
			Subscription::increase_tests_count();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			return $wpdb->delete( $tests_table, [ 'post_id' => $post_id ] );
		}//end if
	}
}
