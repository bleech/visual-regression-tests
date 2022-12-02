<?php

switch ( $data['view'] ) {
	case 'get_started':
		$template = dirname( __FILE__ ) . '/views/admin-notification-get-started.php';
		break;

	case 'new_test_added':
		$template = dirname( __FILE__ ) . '/views/admin-notification-new-test-added.php';
		break;

	case 'new_test_failed':
		$template = dirname( __FILE__ ) . '/views/admin-notification-new-test-failed.php';
		break;

	case 'test_disabled':
		$template = dirname( __FILE__ ) . '/views/admin-notification-test-disabled.php';
		break;

	case 'plugin_activated':
		$template = dirname( __FILE__ ) . '/views/admin-notification-plugin-activated.php';
		break;

	case 'settings_saved':
			$template = dirname( __FILE__ ) . '/views/admin-notification-settings-saved.php';
		break;

	default:
		$template = '';
		break;
}//end switch

if ( file_exists( $template ) ) {
	include $template;
}
