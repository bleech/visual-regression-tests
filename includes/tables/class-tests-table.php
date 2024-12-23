<?php

namespace Vrts\Tables;

class Tests_Table {

	const DB_VERSION = '1.5';
	const TABLE_NAME = 'vrts_tests';

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

			if ( $installed_version && version_compare( $installed_version, '1.3', '<' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching  -- It's OK.
				$wpdb->query(
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's OK.
					"ALTER TABLE {$table_name} RENAME COLUMN target_screenshot_url TO base_screenshot_url;"
				);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching  -- It's OK.
				$wpdb->query(
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's OK.
					"ALTER TABLE {$table_name} RENAME COLUMN snapshot_date TO base_screenshot_date;"
				);
			}

			if ( $installed_version && version_compare( $installed_version, '1.5', '<' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching  -- It's OK.
				$wpdb->query(
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- It's OK.
					"ALTER TABLE {$table_name} DROP COLUMN current_alert_id;"
				);
			}

			$sql = "CREATE TABLE {$table_name} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				status boolean NOT NULL,
				post_id bigint(20),
				service_test_id varchar(40),
				base_screenshot_url varchar(2048),
				base_screenshot_date datetime,
				last_comparison_date datetime,
				next_run_date datetime,
				is_running boolean,
				hide_css_selectors longtext,
				PRIMARY KEY (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( $option_name, self::DB_VERSION );
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
}
