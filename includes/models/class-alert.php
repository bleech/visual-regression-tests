<?php
namespace Vrts\Models;

use Vrts\Tables\Alerts_Table;
use Vrts\Services\Test_Service;
use Vrts\Tables\Test_Runs_Table;

/**
 * Model Alert Page.
 */
class Alert {

	/**
	 * Get all test items from database
	 *
	 * @param array $args Optional.
	 *
	 * @return object
	 */
	public static function get_items( $args = [] ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();
		$test_runs_table = Test_Runs_Table::get_table_name();

		$defaults = [
			's' => '',
			'number' => 20,
			'offset' => 0,
			'orderby' => 'id',
			'order' => 'DESC',
			'filter_status' => 0,
		];

		$args = wp_parse_args( $args, $defaults );

		// 0 = Open
		// 1 = Archived.
		// 2 = False Positive.
		switch ( $args['filter_status'] ?? null ) {
			case 'archived':
				$alert_states = [ 1, 2 ];
				break;
			case 'all':
				$alert_states = [];
				break;
			default:
				$alert_states = [ 0 ];
				break;
		}
		if ( ! empty( $alert_states ) ) {
			$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

			$where = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
				"WHERE alert_state IN ($alert_states_placeholders)",
				$alert_states
			);
		} else {
			$where = 'WHERE 1=1';
		}

		if ( ! empty( $args['s'] ) ) {
			$where .= $wpdb->prepare(
				' AND ( title LIKE %s OR test_run_title LIKE %s )',
				'%' . $wpdb->esc_like( $args['s'] ) . '%',
				'%' . $wpdb->esc_like( $args['s'] ) . '%'
			);
		}

		if ( isset( $args['ids'] ) ) {
			$where .= $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				' AND id IN (' . implode( ',', array_fill( 0, count( $args['ids'] ), '%d' ) ) . ')',
				$args['ids']
			);
		}

		if ( isset( $args['test_run_id'] ) ) {
			$where .= $wpdb->prepare(
				' AND test_run_id = %d',
				$args['test_run_id']
			);
		}

		$whitelist_orderby = [ 'id', 'title', 'differences', 'target_screenshot_finish_date' ];
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

		$alert_title = sprintf(
			"CONCAT( '%s', alert.id ) as title",
			esc_html__( 'Alert #', 'visual-regression-tests' )
		);

		$run_title = sprintf(
			"CONCAT( '%s', run.id ) as test_run_title",
			esc_html__( 'Run #', 'visual-regression-tests' )
		);

		$query = "
			SELECT
				*
				FROM (
					SELECT
						alert.id,
						$alert_title,
						alert.post_id,
						alert.screenshot_test_id,
						alert.target_screenshot_url,
						alert.target_screenshot_finish_date,
						alert.base_screenshot_url,
						alert.base_screenshot_finish_date,
						alert.comparison_screenshot_url,
						alert.comparison_id,
						alert.differences,
						alert.alert_state,
						alert.test_run_id,
						$run_title
					FROM $alerts_table as alert
					LEFT JOIN $test_runs_table as run ON run.id = alert.test_run_id
				) alerts
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
	 * @return object
	 */
	public static function get_item( $id = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_row(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok
			$wpdb->prepare( "SELECT * FROM $alerts_table WHERE id = %d LIMIT 1", $id )
		);
	}

	/**
	 * Get multiple alerts from database by id
	 *
	 * @param array $ids the ids of the items.
	 *
	 * @return array
	 */
	public static function get_items_by_ids( $ids = [] ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $alerts_table WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
				$ids
			)
		);
	}

	/**
	 * Get multiple alerts from database by test run id
	 *
	 * @param array $run_id the id of the test run.
	 *
	 * @return array
	 */
	public static function get_items_by_test_run( $id = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $alerts_table WHERE test_run_id = %d",
				$id
			)
		);
	}

	/**
	 * Get latest alert id by post id
	 *
	 * @param int $post_id the id of the post.
	 * @param int $alert_state the state of the item.
	 *
	 * @return int
	 */
	public static function get_latest_alert_id_by_post_id( $post_id = 0, $alert_state = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT id FROM $alerts_table
				WHERE alert_state = %d
				AND post_id = %d
				ORDER BY id DESC
				LIMIT 1",
				$alert_state,
				$post_id
			)
		);
	}

	/**
	 * Get total test items from database
	 *
	 * @param int $filter_status_query the id of status.
	 * @param int $test_run_id the id of the test run.
	 *
	 * @return array
	 */
	public static function get_total_items( $filter_status_query = null, $test_run_id = null ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// 0 = Open
		// 1 = Archived.
		// 2 = False Positive.
		switch ( $filter_status_query ?? null ) {
			case 'archived':
				$alert_states = [ 1, 2 ];
				break;
			case 'all':
				$alert_states = [];
				break;
			default:
				$alert_states = [ 0 ];
				break;
		}
		if ( ! empty( $alert_states ) ) {
			$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

			$status_where = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
				"WHERE alert_state IN ($alert_states_placeholders)",
				$alert_states
			);
		} else {
			$status_where = 'WHERE 1=1';
		}

		$test_run_where = '';
		if ( null !== $test_run_id ) {
			$test_run_where = $wpdb->prepare(
				' AND test_run_id = %d',
				$test_run_id
			);
		}

		$where = "{$status_where}{$test_run_where}";

		$query = "
			SELECT COUNT(*) FROM $alerts_table
			$where
		";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- It's prepared above
		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get total test items from database
	 *
	 * @param int $filter_status_query the id of status.
	 * @param int $test_run_id the id of the test run.
	 *
	 * @return array
	 */
	public static function get_total_items_grouped_by_test_run( $filter_status_query = null ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// 0 = Open
		// 1 = Archived.
		// 2 = False Positive.
		switch ( $filter_status_query ?? null ) {
			case 'archived':
				$alert_states = [ 1, 2 ];
				break;
			case 'all':
				$alert_states = [];
				break;
			default:
				$alert_states = [ 0 ];
				break;
		}
		if ( ! empty( $alert_states ) ) {
			$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

			$status_where = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
				"WHERE alert_state IN ($alert_states_placeholders)",
				$alert_states
			);
		} else {
			$status_where = 'WHERE 1=1';
		}

		$where = "{$status_where} AND test_run_id IS NOT NULL";


		$query = "
			SELECT COUNT(DISTINCT test_run_id) FROM $alerts_table
			$where
		";

		// var_dump($query);die();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- It's prepared above
		return (int) $wpdb->get_var( $query );
	}


	/**
	 * Update alert status.
	 *
	 * @param int $id the id of the item.
	 * @param int $new_alert_state the new state of the item.
	 */
	public static function set_alert_state( $id = 0, $new_alert_state = null ) {
		// 0 = Unread / 1 = Read
		if ( in_array( $new_alert_state, [ 0, 1 ], true ) ) {
			global $wpdb;

			$alerts_table = Alerts_Table::get_table_name();
			$data = [ 'alert_state' => $new_alert_state ];

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			return $wpdb->update( $alerts_table, $data, [ 'id' => $id ] );
		} else {
			return false;
		}
	}

	/**
	 * Update all alert statuses for post id.
	 *
	 * @param int $post_id the id of the item.
	 * @param int $new_alert_state the new state of the item.
	 */
	public static function set_alert_state_for_post_id( $post_id = 0, $new_alert_state = null ) {
		// 0 = Unread / 1 = Read
		if ( in_array( $new_alert_state, [ 0, 1 ], true ) ) {
			global $wpdb;

			$alerts_table = Alerts_Table::get_table_name();
			$data = [ 'alert_state' => $new_alert_state ];

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			return $wpdb->update( $alerts_table, $data, [ 'post_id' => $post_id ] );
		} else {
			return false;
		}
	}

	/**
	 * Get the next open alert id.
	 */
	public static function get_next_open_alert_id() {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
			$wpdb->prepare( "SELECT id FROM $alerts_table WHERE alert_state = %d LIMIT 1", 0 )
		);
	}

	/**
	 * Get the next alert id for the pagination.
	 *
	 * @param int   $id the id of the item.
	 * @param array $test_run_id the id of the test run.
	 */
	public static function get_pagination_next_alert_id( $id = 0, $test_run_id = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT id FROM $alerts_table
				WHERE id > %d
				AND test_run_id = %d
				ORDER BY id ASC
				LIMIT 1",
				$id,
				$test_run_id
			)
		);
	}

	/**
	 * Get the prev alert id for the pagination.
	 *
	 * @param int   $id the id of the item.
	 * @param array $test_run_id the id of the test run.
	 */
	public static function get_pagination_prev_alert_id( $id = 0, $test_run_id = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT id FROM $alerts_table
				WHERE id < %d
				AND test_run_id = %d
				ORDER BY id DESC
				LIMIT 1",
				$id,
				$test_run_id
			)
		);
	}

	/**
	 * Get the current position inside the pagination.
	 *
	 * @param int   $id the id of the item.
	 * @param array $test_run_id the id of the test run.
	 */
	public static function get_pagination_current_position( $id = 0, $test_run_id = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// Let's get the count of the alerts that are before the current one.
		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
			"SELECT count(*) FROM `$alerts_table`
			WHERE id < %d
			AND test_run_id = %d
			ORDER BY id DESC",
			$id,
			$test_run_id
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- It's ok.
		$count = (int) $wpdb->get_var( $query );

		// We need to add 1. E.g. if the count is 0, the position is 1.
		return $count + 1;
	}

	/**
	 * Delete a test from database and update its post meta.
	 *
	 * @param int $id the id of the item.
	 *
	 * @return array
	 */
	public static function delete( $id = 0 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->delete( $alerts_table, [ 'id' => $id ] );
	}

	/**
	 * Get the alert state.
	 *
	 * @param object $alert the alert object.
	 */
	public static function is_archived( $alert ) {
		// 0 = Open
		// 1 = Archived.
		// 2 = False Positive.
		return in_array( $alert->alert_state, [ 1, 2 ], false );
	}

	/**
	 * Get unread alerts count by test run ids.
	 *
	 * @param array|int $test_run_ids the ids of the test runs.
	 */
	public static function get_unread_count_by_test_run_ids( $test_run_ids ) {
		global $wpdb;

		if ( empty( $test_run_ids ) ) {
			return [];
		}

		if ( is_int( $test_run_ids ) || is_string( $test_run_ids ) ) {
			$test_run_ids = [ $test_run_ids ];
		}

		$alerts_table = Alerts_Table::get_table_name();

		$placeholders = implode( ', ', array_fill( 0, count( $test_run_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT test_run_id, COUNT(*) as count FROM $alerts_table
				WHERE test_run_id IN ($placeholders)
				AND alert_state = 0
				GROUP BY test_run_id",
				$test_run_ids
			)
		);
	}

	public static function set_read_status_by_test_run( $test_run_id, $read_status = 1 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update(
			$alerts_table,
			[ 'alert_state' => $read_status ],
			[ 'test_run_id' => intval($test_run_id) ]
		);
	}

	/**
	 * Set or remove alert as false positive.
	 *
	 * @param int $alert_id the id of the item.
	 * @param int $is_false_positive the state of the item.
	 */
	public static function set_false_positive( $alert_id, $is_false_positive = 1 ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->update(
			$alerts_table,
			[ 'is_false_positive' => $is_false_positive ],
			[ 'id' => intval( $alert_id ) ]
		);
	}
}
