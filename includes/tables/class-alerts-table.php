<?php

namespace Vrts\Tables;

class Alerts_Table {

	const DB_VERSION = '1.4';
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
				PRIMARY KEY (id)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $sql );
			update_option( $option_name, self::DB_VERSION );

		}//end if
		if ($installed_version <= '1.2') {
			static::set_is_false_positive_from_alert_state();
		}
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

	protected static function set_is_false_positive_from_alert_state() {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query(
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching  -- It's OK.
			"UPDATE {$table_name} SET is_false_positive = 1, alert_state = 1 WHERE alert_state = 2"
		);
	}
}
