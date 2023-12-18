<?php

namespace Vrts\Models;

use Vrts\Tables\Tests_Table;

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

		if ( isset( $args['s'] ) && null !== $args['s'] ) {
			$where .= $wpdb->prepare(
				' AND posts.post_title LIKE %s',
				'%' . $wpdb->esc_like( $args['s'] ) . '%'
			);
		}

		if ( isset( $args['filter_status'] ) && null !== $args['filter_status'] ) {
			// current_alert_id IS NOT NULL = Pause.
			if ( 'paused' === $args['filter_status'] ) {
				$where .= ' AND tests.current_alert_id IS NOT NULL';
			}
			// current_alert_id IS NULL = Running.
			if ( 'running' === $args['filter_status'] ) {
				$where .= ' AND tests.current_alert_id IS NULL';
			}
		}

		$whitelist_orderby = [ 'id', 'post_title', 'status', 'base_screenshot_date' ];
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
				tests.id,
				tests.status,
				tests.base_screenshot_date,
				tests.post_id,
				tests.current_alert_id,
				tests.service_test_id,
				tests.hide_css_selectors,
				tests.next_run_date,
				tests.last_comparison_date,
				tests.is_running,
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
	 * Get all running test items from database
	 *
	 * @return array
	 */
	public static function get_all_running() {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
			"SELECT * FROM $tests_table WHERE status != 0 AND current_alert_id IS NULL"
		);
	}

	/**
	 * Get all inactive test items from database
	 *
	 * @return array
	 */
	public static function get_all_inactive() {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
			"SELECT * FROM $tests_table WHERE status = 0"
		);
	}

	/**
	 * Get a single test from database
	 *
	 * @param int $id the id of the item.
	 *
	 * @return object
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
	 * Get multiple tests from database by id
	 *
	 * @param array $ids the ids of the items.
	 *
	 * @return object
	 */
	public static function get_items_by_ids( $ids = [] ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $tests_table WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
				$ids
			)
		);
	}

	/**
	 * Get a single test from database
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return object
	 */
	public static function get_item_by_post_id( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $tests_table WHERE post_id = %d",
				$post_id
			)
		);
	}

	/**
	 * Get multiple tests from database by post ids
	 *
	 * @param array $post_ids Post ids.
	 *
	 * @return object
	 */
	public static function get_items_by_post_ids( $post_ids = [] ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $tests_table WHERE post_id IN (" . implode( ',', array_fill( 0, count( $post_ids ), '%d' ) ) . ')',
				$post_ids
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
	 * Checks if post has a test
	 *
	 * @param int $post_id the id of the post.
	 *
	 * @return bool
	 */
	public static function exists_for_post( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return ! ! $wpdb->get_var(
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
	public static function get_base_screenshot_url( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT base_screenshot_url FROM $tests_table WHERE post_id = %d",
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
	public static function get_base_screenshot_date( $post_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT base_screenshot_date FROM $tests_table WHERE post_id = %d",
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
	 * @param int   $row_id The row id to update.
	 *
	 * @return int|void
	 */
	public static function save( $args = [], $row_id = null ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		if ( ! $row_id ) {
			// Insert a new row.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- It's ok.
			if ( $wpdb->insert( $tests_table, $args ) ) {
				return $wpdb->insert_id;
			}
		} else {
			// Update existing row.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			if ( $wpdb->update( $tests_table, $args, [ 'id' => $row_id ] ) ) {
				return $row_id;
			}
		}
	}

	/**
	 * Insert multiple test data
	 *
	 * @param array $data Data to update (in multi array column => value pairs).
	 *
	 * @return int|false The number of rows affected, or false on error.
	 */
	public static function save_multiple( $data = [] ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// If there is no data, return false.
		if ( ! isset( $data[0] ) ) {
			return false;
		}

		$fields  = '`' . implode( '`, `', array_keys( $data[0] ) ) . '`';
		$formats = implode( ', ', array_map(function( $row ) {
			return '(' . implode( ', ', array_fill( 0, count( $row ), '%s' ) ) . ')';
		}, $data ) );
		$values  = [];

		foreach ( $data as $row ) {
			foreach ( $row as $value ) {
				$values[] = $value;
			}
		}

		// TODO: add support for update.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
				"INSERT INTO `$tests_table` ($fields) VALUES $formats",
				$values
			)
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
		$hide_css_selectors = sanitize_text_field( $hide_css_selectors );
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
	 * Get all service test ids
	 *
	 * @return array
	 */
	public static function get_all_service_test_ids() {
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
	 * Delete a test from database.
	 *
	 * @param int $test_id the id of the item.
	 *
	 * @return int
	 */
	public static function delete( $test_id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->delete( $tests_table, [ 'id' => $test_id ] );
	}

	/**
	 * Convert values to correct type.
	 *
	 * @param object $test The test object.
	 *
	 * @return object
	 */
	public static function cast_values( $test ) {
		$test->id = ! is_null( $test->id ) ? (int) $test->id : null;
		$test->post_id = ! is_null( $test->post_id ) ? (int) $test->post_id : null;
		$test->status = ! is_null( $test->status ) ? (int) $test->status : null;
		$test->current_alert_id = ! is_null( $test->current_alert_id ) ? (int) $test->current_alert_id : null;
		$test->base_screenshot_date = ! is_null( $test->base_screenshot_date ) ? mysql2date( 'c', $test->base_screenshot_date ) : null;
		$test->next_run_date = ! is_null( $test->next_run_date ) ? mysql2date( 'c', $test->next_run_date ) : null;
		$test->last_comparison_date = ! is_null( $test->last_comparison_date ) ? mysql2date( 'c', $test->last_comparison_date ) : null;
		$test->is_running = ! is_null( $test->is_running ) ? (bool) $test->is_running : null;

		return $test;
	}

	/**
	 * Get test by service test id.
	 *
	 * @param array $service_test_ids The local only service test ids.
	 *
	 * @return object
	 */
	public static function clear_remote_test_ids( $service_test_ids ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"UPDATE $tests_table
					SET
						service_test_id = NULL,
						status = 0
					WHERE service_test_id IN ( %s )",
				implode( ',', $service_test_ids )
			)
		);
	}

	/**
	 * Unset alert and screenshot for a test.
	 *
	 * @param int $test_id The test id.
	 */
	public static function reset_base_screenshot( $test_id ) {
		global $wpdb;

		if ( ! $test_id ) {
			return;
		}

		$table_test = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update(
			$table_test,
			[
				'current_alert_id' => null,
				'base_screenshot_url' => null,
				'base_screenshot_date' => null,
				'last_comparison_date' => null,
				'next_run_date' => null,
				'is_running' => null,
			],
			[ 'id' => $test_id ]
		);
	}

	/**
	 * Set tests running.
	 *
	 * @param array $test_ids The test ids.
	 */
	public static function set_tests_running( $test_ids ) {
		global $wpdb;

		if ( empty( $test_ids ) ) {
			return;
		}

		$table_test = Tests_Table::get_table_name();

		$placeholders = implode( ', ', array_fill( 0, count( $test_ids ), '%s' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"UPDATE $table_test
					SET
						is_running = 1 "
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
				. "WHERE service_test_id IN ( $placeholders )",
				$test_ids
			)
		);
	}
}
