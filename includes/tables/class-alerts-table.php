<?php

namespace Vrts\Tables;

class Alerts_Table {

	const DB_VERSION = '1.0';
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

			$sql = "CREATE TABLE {$table_name} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				title text,
				post_id bigint(20),
				screenshot_test_id varchar(40),
				target_screenshot_url varchar(2048),
				target_screenshot_finish_date datetime,
				base_screenshot_url varchar(2048),
				base_screenshot_finish_date datetime,
				comparison_screenshot_url varchar(2048),
				differences int(4),
				alert_state boolean NOT NULL DEFAULT 0,
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
