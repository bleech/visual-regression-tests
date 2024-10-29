<?php

namespace Vrts\Models;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Image_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Tables\Alerts_Table;
use Vrts\Tables\Tests_Table;

/**
 * Model Tests Page.
 */
class Test {

	/**
	 * Get all test items from database
	 *
	 * @param array $args Optional.
	 * @param bool  $return_count Optional.
	 *
	 * @return object
	 */
	public static function get_items( $args = [], $return_count = false ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();
		$alerts_table = Alerts_Table::get_table_name();

		$defaults = [
			'number' => 20,
			'offset' => 0,
			'orderby' => 'id',
			'order' => 'DESC',
		];

		$args = wp_parse_args( $args, $defaults );

		$select = $return_count ? 'SELECT COUNT(*)' : 'SELECT *';
		$where = 'WHERE 1=1';

		if ( isset( $args['s'] ) && null !== $args['s'] ) {
			$where .= $wpdb->prepare(
				' AND tests.post_title LIKE %s',
				'%' . $wpdb->esc_like( $args['s'] ) . '%'
			);
		}

		if ( isset( $args['filter_status'] ) && null !== $args['filter_status'] ) {
			if ( 'changes-detected' === $args['filter_status'] ) {
				$where .= " AND calculated_status = '6-has-alert'";
			}
			if ( 'passed' === $args['filter_status'] ) {
				$where .= " AND calculated_status = '5-passed'";
			}
			if ( 'scheduled' === $args['filter_status'] ) {
				$where .= " AND calculated_status = '4-scheduled'";
			}
		}

		if ( isset( $args['ids'] ) ) {
			$where .= $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				' AND id IN (' . implode( ',', array_fill( 0, count( $args['ids'] ), '%d' ) ) . ')',
				$args['ids']
			);
		}

		$whitelist_orderby = [ 'id', 'post_title', 'status', 'base_screenshot_date' ];
		$whitelist_order = [ 'ASC', 'DESC' ];

		$orderby = in_array( $args['orderby'], $whitelist_orderby, true ) ? $args['orderby'] : 'id';
		$order = in_array( $args['order'], $whitelist_order, true ) ? $args['order'] : 'DESC';

		if ( 'status' === $orderby ) {
			$orderby = "ORDER BY calculated_status $order, calculated_date $order";
		} else {
			$orderby = "ORDER BY $orderby $order";
		}

		$limit = $args['number'] > 100 ? 100 : $args['number'];

		if ( $args['number'] < 1 ) {
			$limits = '';
		} else {
			$limits = $wpdb->prepare(
				'LIMIT %d, %d',
				$args['offset'],
				$limit
			);
		}

		$query = "
			$select
				FROM (
					SELECT
						tests.id,
						tests.status,
						tests.post_id,
						tests.current_alert_id,
						tests.service_test_id,
						tests.base_screenshot_url,
						tests.base_screenshot_date,
						tests.last_comparison_date,
						tests.next_run_date,
						tests.is_running,
						tests.hide_css_selectors,
						posts.post_title,
						CASE
							WHEN tests.current_alert_id is not null THEN '6-has-alert'
							WHEN tests.service_test_id is null THEN '1-post-not-published'
							WHEN tests.base_screenshot_date is null THEN '2-waiting'
							WHEN tests.is_running > 0 THEN '3-running'
							WHEN tests.last_comparison_date is null THEN '4-scheduled'
							else '5-passed'
						END as calculated_status,
						CASE
							WHEN tests.current_alert_id is not null THEN alerts.target_screenshot_finish_date
							WHEN tests.service_test_id is null THEN tests.base_screenshot_date
							WHEN tests.base_screenshot_date is null THEN tests.base_screenshot_date
							WHEN tests.is_running > 0 THEN tests.base_screenshot_date
							WHEN tests.last_comparison_date is null THEN tests.next_run_date
							else tests.last_comparison_date
						END as calculated_date
					FROM $tests_table as tests
					INNER JOIN $wpdb->posts as posts ON posts.id = tests.post_id
					LEFT JOIN $alerts_table as alerts ON alerts.id = tests.current_alert_id
					GROUP BY tests.id
				) tests
			$where
			$orderby
			$limits
		";

		if ( $return_count ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared above.
			$items = $wpdb->get_var( $query );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared above.
			$items = $wpdb->get_results( $query );
		}

		return $items;
	}

	/**
	 * Get all running test items from database
	 *
	 * @param bool $return_count Optional.
	 *
	 * @return array
	 */
	public static function get_all_running( $return_count = false ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		if ( $return_count ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			return $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT COUNT(*) FROM $tests_table WHERE status != 0"
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			return $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $tests_table WHERE status != 0"
			);
		}
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
				"SELECT * FROM $tests_table WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ') ORDER BY id DESC',
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
	 * Get autoincrement value
	 *
	 * @return int
	 */
	public static function get_autoincrement_value() {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				'SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s',
				DB_NAME,
				$tests_table
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
	 * @param int $id the id of the post.
	 *
	 * @return int
	 */
	public static function get_post_id( $id = 0 ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT post_id FROM $tests_table WHERE id = %d",
				$id
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
	 * Get total test items from database
	 *
	 * @param int $filter_status_query the id of status.
	 *
	 * @return array
	 */
	public static function get_total_items( $filter_status_query = null ) {
		return (int) self::get_items( [
			'number' => -1,
			'filter_status' => $filter_status_query,
		], true );
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

		$alert_id = 0 === $alert_id ? null : $alert_id;
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
	 * Get test calculated status
	 *
	 * @param int|object $test test id or test object.
	 *
	 * @return string
	 */
	public static function get_calculated_status( $test ) {
		if ( is_int( $test ) ) {
			$test = self::get_item( $test );
		}

		if ( ! Service::is_connected() ) {
			return 'disconnected';
		}

		// If test doesn't exist set initial status to 'waiting'.
		if ( ! isset( $test->id ) ) {
			return 'waiting';
		}

		$no_tests_left = intval( Subscription::get_remaining_tests() ) === 0;
		$has_remote_test = ! empty( $test->service_test_id );
		$has_base_screenshot = ! empty( $test->base_screenshot_date );
		$has_comparison = ! empty( $test->last_comparison_date );
		$is_running = (bool) $test->is_running;

		if ( false === (bool) $test->status && ( $no_tests_left || $has_remote_test ) ) {
			return 'no-credit-left';
		}

		if ( ! $has_remote_test ) {
			return 'post-not-published';
		}

		if ( $test->current_alert_id ) {
			return 'has-alert';
		}

		if ( ! $has_base_screenshot ) {
			return 'waiting';
		}

		if ( $is_running ) {
			return 'running';
		}

		if ( ! $has_comparison ) {
			return 'scheduled';
		}

		return 'passed';
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

	/**
	 * Get test status data
	 *
	 * @param int|object $test test id or test object.
	 *
	 * @return array
	 */
	public static function get_status_data( $test ) {
		if ( is_int( $test ) ) {
			$test = self::get_item( $test );
		}

		$test_status = self::get_calculated_status( $test );
		$has_subscription = Subscription::get_subscription_status();
		$instructions = '';

		switch ( $test_status ) {
			case 'disconnected':
				$class = 'paused';
				$text = esc_html__( 'Disconnected', 'visual-regression-tests' );
				break;
			case 'has-alert':
				$alert = Alert::get_item( $test->current_alert_id );
				$class = 'paused';
				$text = esc_html__( 'Changes detected', 'visual-regression-tests' );
				$alert_link = Url_Helpers::get_alert_page( $test->current_alert_id );
				$instructions = Date_Time_Helpers::get_formatted_relative_date_time( $alert->target_screenshot_finish_date );
				$instructions .= sprintf(
					/* translators: %1$s and %2$s: link wrapper. */
					esc_html__( '%1$s%2$s View Alert%3$s', 'visual-regression-tests' ),
					'<a href="' . esc_url( $alert_link ) . '" title="' . esc_attr__( 'View Alert', 'visual-regression-tests' ) . '">',
					'<i class="dashicons dashicons-image-flip-horizontal"></i>',
					'</a>'
				);
				break;
			case 'no-credit-left':
				$class = 'paused';
				$text = esc_html__( 'Disabled', 'visual-regression-tests' );
				$base_link = Url_Helpers::get_page_url( 'upgrade' );
				$instructions = sprintf(
					/* translators: %1$s and %2$s: link wrapper. */
					esc_html__( '%1$sUpgrade plugin%2$s to resume testing', 'visual-regression-tests' ),
					'<a href="' . $base_link . '" title="' . esc_attr__( 'Upgrade plugin', 'visual-regression-tests' ) . '">',
					'</a>'
				);
				break;
			case 'post-not-published':
				$class = 'paused';
				$text = esc_html__( 'Disabled', 'visual-regression-tests' );
				$instructions = esc_html__( 'Publish post to resume testing', 'visual-regression-tests' );
				break;
			case 'waiting':
				$class = 'waiting';
				$text = esc_html__( 'Waiting', 'visual-regression-tests' );
				break;
			case 'running':
				$class = 'waiting';
				$text = esc_html__( 'In Progress', 'visual-regression-tests' );
				$instructions = esc_html__( 'Refresh page to see result', 'visual-regression-tests' );
				break;
			case 'scheduled':
				$class = 'waiting';
				$text = esc_html__( 'Scheduled', 'visual-regression-tests' );
				$next_run = Test_Run::get_next_scheduled_run();
				if ( $next_run ) {
					$instructions = Date_Time_Helpers::get_formatted_relative_date_time( $next_run->scheduled_at );
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not required here.
				if ( $has_subscription && isset( $_GET['page'] ) && 'vrts' === $_GET['page'] ) {
					$instructions .= sprintf(
						'<a class="vrts-run-test" href="%s" data-id="%d" title="%s">%s</a>',
						Url_Helpers::get_run_manual_test_url( $test->id ),
						$test->id,
						esc_html__( 'Run Test', 'visual-regression-tests' ),
						'<i class="dashicons dashicons-update"></i> ' . esc_html__( 'Run Test', 'visual-regression-tests' )
					);
				}
				break;
			case 'passed':
			default:
				$class = 'running';
				$text = esc_html__( 'Passed', 'visual-regression-tests' );
				if ( $test->last_comparison_date ) {
					$instructions .= Date_Time_Helpers::get_formatted_relative_date_time( $test->last_comparison_date );
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not required here.
				if ( $has_subscription && isset( $_GET['page'] ) && 'vrts' === $_GET['page'] ) {
					$instructions .= sprintf(
						'<a class="vrts-run-test" href="%s" data-id="%d" title="%s">%s</a>',
						Url_Helpers::get_run_manual_test_url( $test->id ),
						$test->id,
						esc_html__( 'Run Test', 'visual-regression-tests' ),
						'<i class="dashicons dashicons-update"></i> ' . esc_html__( 'Run Test', 'visual-regression-tests' )
					);
				}
				break;
		}//end switch

		return [
			'status' => $test_status,
			'class' => $class,
			'text' => $text,
			'instructions' => $instructions,
		];
	}

	/**
	 * Get test screenshot data
	 *
	 * @param int|object $test test id or object.
	 *
	 * @return array
	 */
	public static function get_screenshot_data( $test ) {
		if ( is_int( $test ) ) {
			$test = self::get_item( $test );
		}

		$screenshot_status = 'taken';

		if ( ! Service::is_connected() ) {
			$screenshot_status = 'paused';
		} elseif ( ! isset( $test->id ) ) {
			$screenshot_status = 'waiting';
		} elseif ( false === (bool) $test->status ) {
			$screenshot_status = 'paused';
		} elseif ( ! $test->base_screenshot_date ) {
			$screenshot_status = 'waiting';
		}//end if

		$instructions = '';
		$screenshot = sprintf(
			'<img class="figure-image" src="%s" alt="%s" />',
			esc_url( vrts()->get_plugin_url( 'assets/images/vrts-snapshot-placeholder.svg' ) ),
			esc_html__( 'Snapshot', 'visual-regression-tests' )
		);

		switch ( $screenshot_status ) {
			case 'paused':
				$text = esc_html__( 'On hold', 'visual-regression-tests' );
				break;
			case 'waiting':
				$text = esc_html__( 'In progress', 'visual-regression-tests' );
				$instructions = sprintf(
					'<span class="vrts-testing-status--waiting">%s</span>',
					esc_html__( 'Refresh page to see snapshot', 'visual-regression-tests' )
				);
				break;
			case 'taken':
			default:
				$text = sprintf(
					'<a href="%s" target="_blank" data-id="%d" title="%s">%s</a>',
					esc_url( Image_Helpers::get_screenshot_url( $test, 'base' ) ),
					esc_attr( $test->id ),
					esc_html__( 'View this snapshot', 'visual-regression-tests' ),
					esc_html__( 'View Snapshot', 'visual-regression-tests' )
				);
				$instructions = Date_Time_Helpers::get_formatted_relative_date_time( $test->base_screenshot_date );
				$screenshot = sprintf(
					'<a href="%s" target="_blank" data-id="%d" title="%s"><img class="figure-image" src="%s" alt="%s"></a>',
					esc_url( Image_Helpers::get_screenshot_url( $test, 'base' ) ),
					esc_attr( $test->id ),
					esc_html__( 'View this snapshot', 'visual-regression-tests' ),
					esc_url( Image_Helpers::get_screenshot_url( $test, 'base' ) ),
					esc_html__( 'View Snapshot', 'visual-regression-tests' )
				);
				break;
		}//end switch

		return [
			'status' => $screenshot_status,
			'text' => $text,
			'instructions' => $instructions,
			'screenshot' => $screenshot,
		];
	}

	/**
	 * Get tests by service test ids.
	 *
	 * @param array $service_test_ids Service test ids.
	 *
	 * @return array
	 */
	public static function get_by_service_test_ids( $service_test_ids ) {
		global $wpdb;

		$tests_table = Tests_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $tests_table WHERE service_test_id IN (" . implode( ',', array_fill( 0, count( $service_test_ids ), '%s' ) ) . ')',
				$service_test_ids
			)
		);
	}
}
