<?php

namespace Vrts\Features;

use Vrts\Features\Service;
use Vrts\Tables\Alerts_Table;
use Vrts\Tables\Tests_Table;

class Install {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( vrts()->get_plugin_file(), [ $this, 'install' ] );
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
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
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
}
