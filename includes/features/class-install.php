<?php

namespace Vrts\Features;

use Vrts\Features\Service;
use Vrts\Tables\Alerts_Table;
use Vrts\Tables\Tests_Table;

class Install {

	const ACTIVATION_TRANSIENT = 'vrts_activation';

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( vrts()->get_plugin_file(), [ $this, 'install' ] );
		register_activation_hook( vrts()->get_plugin_file(), [ $this, 'set_activation_admin_notice_transient' ] );
		add_action( 'admin_notices', [ $this, 'activation_admin_notice' ] );

		add_action( 'init', [ $this, 'install' ], 10, 2 );
		add_action( 'upgrader_process_complete', [ $this, 'on_upgrade' ], 10, 2 );
		add_action( 'vrts_plugin_on_upgrade', [ $this, 'install' ], 10, 2 );
	}

	/**
	 * Install plugin.
	 *
	 * @param bool $network_wide If the plugin has been activated network wide.
	 */
	public function install( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			global $wpdb;

			// Direct DB query and no caching are OK to use in this case.
			// @codingStandardsIgnoreStart.
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			// @codingStandardsIgnoreEnd.

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				// call here function to install tables, options etc.
				$this->install_tables();
				$this->connect_service();
				restore_current_blog();
			}
		} else {
			// call here function to install tables, options etc.
			$this->install_tables();
			$this->connect_service();
		}
	}

	/**
	 * Install plugin tables.
	 */
	private function install_tables() {
		Alerts_Table::install_table();
		Tests_Table::install_table();
	}

	/**
	 * Install plugin tables.
	 */
	private function connect_service() {
		Service::connect_service();
	}

	/**
	 * Set activation transient.
	 */
	public function set_activation_admin_notice_transient() {
		set_transient( self::ACTIVATION_TRANSIENT, true, 5 );
	}

	/**
	 * Display activation admin notice.
	 */
	public function activation_admin_notice() {
		if ( get_transient( self::ACTIVATION_TRANSIENT ) ) {
			Admin_Notices::render_notification( 'plugin_activated' );
			delete_transient( self::ACTIVATION_TRANSIENT );
		}
	}

	/**
	 * On upgrade.
	 *
	 * @param object $upgrader WP_Upgrader instance.
	 * @param array  $options  Array of bulk item update data.
	 */
	public function on_upgrade( $upgrader, $options ) {
		if ( 'update' !== $options['action'] ) {
			return;
		}

		if ( 'core' === $options['type'] ) {
			return;
		}

		if ( 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $plugin ) {
				if ( plugin_basename( vrts()->get_plugin_file() ) === $plugin ) {
					do_action( 'vrts_plugin_on_upgrade' );
				}
			}
		}
	}
}
