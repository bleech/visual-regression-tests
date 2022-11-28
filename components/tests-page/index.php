<?php

switch ( $data['action'] ) {
	default:
		$template = dirname( __FILE__ ) . '/views/tests-page-list.php';
		break;
}

if ( file_exists( $template ) ) {
	include $template;
}
