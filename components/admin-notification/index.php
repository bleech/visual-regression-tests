<?php

switch ( $data['view'] ) {
	case 'connection_failed':
		$template = dirname( __FILE__ ) . '/views/admin-notification-connection-failed.php';
		break;

	case 'get_started':
		$template = dirname( __FILE__ ) . '/views/admin-notification-get-started.php';
		break;

	case 'new_test_added':
		$template = dirname( __FILE__ ) . '/views/admin-notification-new-test-added.php';
		break;

	case 'new_test_failed':
		$template = dirname( __FILE__ ) . '/views/admin-notification-new-test-failed.php';
		break;

	case 'new_tests_added':
		$template = dirname( __FILE__ ) . '/views/admin-notification-new-tests-added.php';
		break;

	case 'plugin_activated':
		$template = dirname( __FILE__ ) . '/views/admin-notification-plugin-activated.php';
		break;

	case 'settings_saved':
		$template = dirname( __FILE__ ) . '/views/admin-notification-settings-saved.php';
		break;

	case 'license_added':
		$template = dirname( __FILE__ ) . '/views/admin-notification-license-added.php';
		break;

	case 'license_not_added':
		$template = dirname( __FILE__ ) . '/views/admin-notification-license-not-added.php';
		break;

	case 'test_disabled':
		$template = dirname( __FILE__ ) . '/views/admin-notification-test-disabled.php';
		break;

	case 'test_started':
		$template = dirname( __FILE__ ) . '/views/admin-notification-test-started.php';
		break;

	case 'unlock_more_tests':
		$template = dirname( __FILE__ ) . '/views/admin-notification-unlock-more-tests.php';
		break;

	default:
		$template = '';
		break;
}//end switch

if ( file_exists( $template ) ) {
	include $template;
}
