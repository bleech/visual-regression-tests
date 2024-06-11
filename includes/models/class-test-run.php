<?php

namespace Vrts\Models;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Tables\Alerts_Table;
use Vrts\Tables\Test_Runs_Table;

/**
 * Model Tests Page.
 */
class Test_Run {

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

		$test_runs_table = Test_Runs_Table::get_table_name();
		$alerts_table = Alerts_Table::get_table_name();

		$defaults = [
			'number' => 20,
			'offset' => 0,
			'orderby' => 'id',
			'order' => 'DESC',
		];

		$args = wp_parse_args( $args, $defaults );

		$select = $return_count ? 'SELECT COUNT(*)' : 'SELECT *';
		$where = 'WHERE started_at IS NOT NULL AND is_running = 0';

		// if ( isset( $args['filter_status'] ) && null !== $args['filter_status'] ) {
		// 	if ( 'changes-detected' === $args['filter_status'] ) {
		// 		$where .= " AND calculated_status = '6-has-alert'";
		// 	}
		// 	if ( 'passed' === $args['filter_status'] ) {
		// 		$where .= " AND calculated_status = '5-passed'";
		// 	}
		// 	if ( 'scheduled' === $args['filter_status'] ) {
		// 		$where .= " AND calculated_status = '4-scheduled'";
		// 	}
		// }

		$whitelist_orderby = [ 'id', 'title' ];
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
						runs.id,
						runs.tests,
						runs.alerts,
						runs.trigger,
						runs.trigger_notes,
						runs.started_at,
						runs.scheduled_at,
						runs.finished_at,
						runs.is_running
					FROM $test_runs_table as runs
				) runs
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

	public static function get_queued_items() {
		global $wpdb;

		$test_runs_table = Test_Runs_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			"SELECT * FROM $test_runs_table WHERE finished_at is NULL ORDER BY scheduled_at ASC"
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

		$test_runs_table = Test_Runs_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $test_runs_table WHERE id = %d",
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

		$test_runs_table = Test_Runs_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $test_runs_table WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
				$ids
			)
		);
	}

	public static function get_by_service_test_run_id( $test_run_id ) {
		global $wpdb;

		$test_runs_table = Test_Runs_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT * FROM $test_runs_table WHERE service_test_run_id = %s",
				$test_run_id
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

		$test_runs_table = Test_Runs_Table::get_table_name();

		if ( ! $row_id ) {
			// Insert a new row.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- It's ok.
			if ( $wpdb->insert( $test_runs_table, $args ) ) {
				return $wpdb->insert_id;
			}
		} else {
			// Update existing row.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
			if ( $wpdb->update( $test_runs_table, $args, [ 'id' => $row_id ] ) ) {
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

		$test_runs_table = Test_Runs_Table::get_table_name();

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
				"INSERT INTO `$test_runs_table` ($fields) VALUES $formats",
				$values
			)
		);
	}

	/**
	 * Get test run calculated status
	 *
	 * @param int|object $test_run test run id or test object.
	 *
	 * @return string
	 */
	public static function get_calculated_status( $test_run ) {
		if ( is_int( $test_run ) ) {
			$test_run = self::get_item( $test_run );
		}

		$has_alerts = ! empty( $test_run->alerts );

		if ( $has_alerts ) {
			return 'has-alerts';
		}

		return 'passed';
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

		$test_runs_table = Test_Runs_Table::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return $wpdb->delete( $test_runs_table, [ 'id' => $test_id ] );
	}

	/**
	 * Convert values to correct type.
	 *
	 * @param object $test_run The test run object.
	 *
	 * @return object
	 */
	public static function cast_values( $test_run ) {
		$test_run->id = ! is_null( $test_run->id ) ? (int) $test_run->id : null;
		$test_run->tests = ! is_null( $test_run->tests ) ?  maybe_unserialize( $test_run->tests ) : [];
		$test_run->alerts = ! is_null( $test_run->alerts ) ?  maybe_unserialize( $test_run->alerts ) : [];
		$test_run->started_at = ! is_null( $test_run->started_at ) ? mysql2date( 'c', $test_run->started_at ) : null;
		$test_run->scheduled_at = ! is_null( $test_run->scheduled_at ) ? mysql2date( 'c', $test_run->scheduled_at ) : null;
		$test_run->finished_at = ! is_null( $test_run->finished_at ) ? mysql2date( 'c', $test_run->finished_at ) : null;

		return $test_run;
	}

	/**
	 * Get test run status data
	 *
	 * @param int|object $test_run test run id or test run object.
	 *
	 * @return array
	 */
	public static function get_status_data( $test_run ) {
		if ( is_int( $test_run ) ) {
			$test_run = self::get_item( $test_run );
		}

		$test_run_status = self::get_calculated_status( $test_run );
		$instructions = '';

		switch ( $test_run_status ) {
			case 'has-alerts':
				// $alert = Alert::get_item( $test->current_alert_id );
				$alerts_count = count( maybe_unserialize( $test_run->alerts ) );
				$class = 'paused';
				$text = esc_html__( 'Changes detected', 'visual-regression-tests' );
				$base_link = admin_url( 'admin.php?page=vrts-alerts&action=edit&alert_id=' );
				$instructions = Date_Time_Helpers::get_formatted_relative_date_time( $test_run->finished_at );
				$instructions .= sprintf(
					/* translators: %1$s and %2$s: link wrapper. */
					esc_html( _n( '%1$s%2$s View Alert (%4$s)%3$s', '%1$s%2$s View Alerts (%4$s)%3$s', $alerts_count, 'visual-regression-tests' ) ),
					'<a href="' . $base_link . '" title="' . esc_attr__( 'View Alert', 'visual-regression-tests' ) . '">',
					'<i class="dashicons dashicons-image-flip-horizontal"></i>',
					'</a>',
					$alerts_count
				);
				break;
			case 'running':
				$class = 'waiting';
				$text = esc_html__( 'In Progress', 'visual-regression-tests' );
				$instructions = esc_html__( 'Refresh page to see result', 'visual-regression-tests' );
				break;
			case 'scheduled':
				$class = 'waiting';
				$text = esc_html__( 'Scheduled', 'visual-regression-tests' );
				break;
			case 'passed':
			default:
				$class = 'running';
				$text = esc_html__( 'Passed', 'visual-regression-tests' );
				if ( $test_run->finished_at ) {
					$instructions .= Date_Time_Helpers::get_formatted_relative_date_time( $test_run->finished_at );
				}
				break;
		}//end switch

		return [
			'status' => $test_run_status,
			'class' => $class,
			'text' => $text,
			'instructions' => $instructions,
		];
	}
}
