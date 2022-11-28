<?php

switch ( $data['action'] ) {
	case 'view':
		$template = dirname( __FILE__ ) . '/views/alerts-page-view.php';
		break;

	case 'edit':
		$template = dirname( __FILE__ ) . '/views/alerts-page-edit.php';
		break;

	default:
		$template = dirname( __FILE__ ) . '/views/alerts-page-list.php';
		break;
}

if ( file_exists( $template ) ) {
	include $template;
}
