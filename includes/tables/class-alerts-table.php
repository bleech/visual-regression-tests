<?php

namespace Vrts\Tables;

class Alerts_Table {

	const DB_VERSION = '1.3';
	const TABLE_NAME = 'vrts_alerts';

	/**
	 * Get the name of the table.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Create or update the database table for tests.
	 */
	public static function install_table() {
		$option_name = self::TABLE_NAME . '_db_version';
		$installed_version = get_option( $option_name );

		if ( self::DB_VERSION !== $installed_version ) {
			global $wpdb;

			$table_name = self::get_table_name();
			$charset_collate = $wpdb->get_charset_collate();

			if ( $installed_version && version_compare( $installed_version, '1.1', '<' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching  -- It's OK.
				$wpdb->query(
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's OK.
					"ALTER TABLE {$table_name} MODIFY alert_state tinyint NOT NULL DEFAULT 0"
				);
			}

			if ( $installed_version && version_compare( $installed_version, '1.3', '<' ) ) {
				// Existing duplicates would prevent dbDelta from adding the
				// unique key on comparison_id.
				static::dedupe_comparison_ids();
			}

			$sql = "CREATE TABLE {$table_name} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				title text,
				post_id bigint(20),
				test_run_id bigint(20),
				screenshot_test_id varchar(40),
				target_screenshot_url varchar(2048),
				target_screenshot_finish_date datetime,
				base_screenshot_url varchar(2048),
				base_screenshot_finish_date datetime,
				comparison_screenshot_url varchar(2048),
				comparison_id varchar(40),
				differences int(4),
				alert_state tinyint NOT NULL DEFAULT 0,
				is_false_positive tinyint NOT NULL DEFAULT 0,
				meta text,
				PRIMARY KEY (id),
				UNIQUE KEY comparison_id (comparison_id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			if ( $installed_version && version_compare( $installed_version, '1.2', '<' ) ) {
				static::set_is_false_positive_from_alert_state();
			}

			// Don't record 1.3 unless the unique key exists, so a failed
			// index creation is retried instead of skipped forever.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$has_unique_key = (bool) $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SHOW INDEX FROM {$table_name} WHERE Key_name = 'comparison_id' AND Non_unique = 0"
			);

			if ( $has_unique_key ) {
				update_option( $option_name, self::DB_VERSION );
			}
		}//end if
	}

	/**
	 * Drop the database table for tests.
	 */
	public static function uninstall_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$sql = "DROP TABLE IF EXISTS {$table_name};";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->query( $sql );

		delete_option( self::TABLE_NAME . '_db_version' );
	}

	/**
	 * Merge duplicate alerts per comparison_id, keeping the oldest row and
	 * carrying over a test run link from its duplicates.
	 */
	protected static function dedupe_comparison_ids() {
		global $wpdb;
		$table_name = self::get_table_name();

		// Empty strings would collide under the unique key; only real ids take part.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"UPDATE {$table_name} SET comparison_id = NULL WHERE comparison_id = ''"
		);

		// A pairwise self-join only applies one match per row, so aggregate
		// over all duplicates and merge onto the oldest row.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			"UPDATE {$table_name} t
				INNER JOIN (
					SELECT comparison_id,
						MIN( id ) AS keep_id,
						MAX( test_run_id ) AS test_run_id,
						MAX( alert_state ) AS alert_state,
						MAX( is_false_positive ) AS is_false_positive
					FROM {$table_name}
					WHERE comparison_id IS NOT NULL
					GROUP BY comparison_id
					HAVING COUNT(*) > 1
				) dupes ON t.id = dupes.keep_id
				SET t.test_run_id = COALESCE( t.test_run_id, dupes.test_run_id ),
					t.alert_state = GREATEST( t.alert_state, dupes.alert_state ),
					t.is_false_positive = GREATEST( t.is_false_positive, dupes.is_false_positive )"
		);

		$wpdb->query(
			"DELETE t FROM {$table_name} t
				INNER JOIN (
					SELECT comparison_id, MIN( id ) AS keep_id
					FROM {$table_name}
					WHERE comparison_id IS NOT NULL
					GROUP BY comparison_id
					HAVING COUNT(*) > 1
				) dupes ON t.comparison_id = dupes.comparison_id AND t.id != dupes.keep_id"
		);
		// phpcs:enable
	}

	/**
	 * Set is_false_positive to 1 for all alerts that have alert_state set to 2.
	 */
	protected static function set_is_false_positive_from_alert_state() {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"UPDATE {$table_name} SET is_false_positive = 1, alert_state = 1 WHERE alert_state = 2"
		);
	}
}
