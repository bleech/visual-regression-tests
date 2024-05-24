<?php
namespace Vrts\Models;

use Vrts\Tables\Alerts_Table;
use Vrts\Services\Test_Service;

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
		$alert_states = ( null !== $args['filter_status'] && 'archived' === $args['filter_status'] ) ? [ 1, 2 ] : [ 0 ];
		$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

		$where = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
			"WHERE alert_state IN ($alert_states_placeholders)",
			$alert_states
		);

		if ( '' !== $args['s'] ) {
			$where .= $wpdb->prepare(
				' AND title LIKE %s',
				'%' . $wpdb->esc_like( $args['s'] ) . '%'
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

		$query = "
			SELECT * FROM $alerts_table
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
	 *
	 * @return array
	 */
	public static function get_total_items( $filter_status_query = null ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();

		// 0 = Open
		// 1 = Archived.
		// 2 = False Positive.
		$alert_states = ( 'archived' === $filter_status_query ) ? [ 1, 2 ] : [ 0 ];
		$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

		$where = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- It's ok.
			"WHERE alert_state IN ($alert_states_placeholders)",
			$alert_states
		);

		$query = "
			SELECT COUNT(*) FROM $alerts_table
			$where
		";

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
		// 0 = Open / 1 = Archived / 2 = False Positive.
		if ( in_array( $new_alert_state, [ 0, 1, 2 ], true ) ) {
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
		// 0 = Open / 1 = Archived / 2 = False Positive.
		if ( in_array( $new_alert_state, [ 0, 1, 2 ], true ) ) {
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
	 * @param array $alert_states the state of the item.
	 */
	public static function get_pagination_next_alert_id( $id = 0, $alert_states = [ 0 ] ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();
		$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT id FROM $alerts_table WHERE alert_state IN ($alert_states_placeholders)
				AND id > %d
				ORDER BY id ASC
				LIMIT 1",
				array_merge( $alert_states, [ $id ] )
			)
		);
	}

	/**
	 * Get the prev alert id for the pagination.
	 *
	 * @param int   $id the id of the item.
	 * @param array $alert_states the state of the item.
	 */
	public static function get_pagination_prev_alert_id( $id = 0, $alert_states = [ 0 ] ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();
		$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
				"SELECT id FROM $alerts_table WHERE alert_state IN ($alert_states_placeholders)
				AND id < %d
				ORDER BY id DESC
				LIMIT 1",
				array_merge( $alert_states, [ $id ] )
			)
		);
	}

	/**
	 * Get the current position inside the pagination.
	 *
	 * @param int   $id the id of the item.
	 * @param array $alert_states the state of the item.
	 */
	public static function get_pagination_current_position( $id = 0, $alert_states = [ 0 ] ) {
		global $wpdb;

		$alerts_table = Alerts_Table::get_table_name();
		$alert_states_placeholders = implode( ', ', array_fill( 0, count( $alert_states ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's ok.
		$query = $wpdb->prepare( "SELECT `row_number` FROM (SELECT RANK() OVER(ORDER BY id ASC) as `row_number`, `id` FROM `$alerts_table` WHERE `alert_state` IN ($alert_states_placeholders)) AS openAlertsWithRowNumber WHERE id = %d", array_merge( $alert_states, [ $id ] ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- It's ok.
		return (int) $wpdb->get_var( $query );
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
}
