<?php
/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'includes/features/class-admin-notices.php';
require_once 'includes/features/class-metaboxes.php';
require_once 'includes/features/class-service.php';
require_once 'includes/features/class-subscription.php';

require_once 'includes/tables/class-alerts-table.php';
require_once 'includes/tables/class-tests-table.php';

Vrts\Features\Admin_Notices::delete_dismissed_options();
Vrts\Features\Metaboxes::delete_meta_keys();
Vrts\Features\Service::disconnect_service();
Vrts\Features\Service::delete_option();
Vrts\Features\Subscription::delete_options();

Vrts\Tables\Alerts_Table::uninstall_table();
Vrts\Tables\Tests_Table::uninstall_table();
