<?php

namespace Vrts\Tables;

class Test_Runs_Table {

	const DB_VERSION = '1.0';
	const TABLE_NAME = 'vrts_test_runs';

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
				service_test_run_id varchar(40),
				tests text,
				alerts text default NULL,
				`trigger` varchar(20),
				trigger_notes text,
				trigger_meta text default NULL,
				started_at datetime,
				scheduled_at datetime,
				finished_at datetime,
				PRIMARY KEY (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			if ( ! $installed_version ) {
				if ( static::create_runs_from_alerts() ) {
					update_option( 'vrts_test_runs_has_migrated_alerts', true );
				}
			}

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
		delete_option( 'vrts_test_runs_has_migrated_alerts' );
	}

	/**
	 * Migrate alerts from the old table.
	 */
	public static function create_runs_from_alerts() {
		global $wpdb;
		$alerts_table = Alerts_Table::get_table_name();
		$tests_table = Tests_Table::get_table_name();
		$runs_table = self::get_table_name();

		$sql = "SELECT
				a.id as id,
				a.target_screenshot_finish_date as finished_at,
				t.id as test_id
			FROM {$alerts_table} a
			JOIN {$tests_table} t
			ON t.post_id = a.post_id
			WHERE a.test_run_id IS NULL;
		";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$alerts = $wpdb->get_results( $sql );

		if ( null === $alerts ) {
			return false;
		}

		$test_runs = array_map(function ( $alert ) {
			return [
				'tests' => maybe_serialize( [ $alert->test_id ] ),
				'alerts' => maybe_serialize( [ $alert->id ] ),
				'trigger' => 'legacy',
				'started_at' => $alert->finished_at,
				'finished_at' => $alert->finished_at,
			];
		}, $alerts);

		$test_runs_values = implode( ',', array_map(function ( $run ) {
			return "('" . implode( "','", array_map( 'esc_sql', $run ) ) . "')";
		}, $test_runs));

		// insert all test runs with single query.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "INSERT INTO {$runs_table} (tests, alerts, `trigger`, started_at, finished_at) VALUES " . $test_runs_values . ';' );

		// update test_run_id in alerts table from newly created test runs based on alerts column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "UPDATE {$alerts_table} a JOIN {$runs_table} r ON r.alerts LIKE CONCAT('%\"', a.id, '\"%') SET a.test_run_id = r.id;" );

		return true;
	}
}
