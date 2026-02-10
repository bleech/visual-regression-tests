<?php

switch ( $data['action'] ) {
	default:
		$template = __DIR__ . '/views/tests-page-list.php';
		break;
}

if ( file_exists( $template ) ) {
	include $template;
}
