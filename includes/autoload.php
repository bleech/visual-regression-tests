<?php

namespace Vrts;

/**
 * Autoload Core Classes.
 *
 * Custom autoloader for WordPress file and class names standards.
 *
 * Few examples
 *     Vrts\Core\Plugin - includes/core/class-plugin-php
 *     Vrts\Core\Utilities\Assets - includes/core/utilities/class-assets-php
 */
spl_autoload_register( function ( $class_name ) {
	$namespace = 'Vrts\\';

	if ( strpos( $class_name, $namespace ) !== 0 ) {
		return false;
	}

	$path = plugin_dir_path( VRTS_PLUGIN_FILE ) . 'includes';
	$parts = explode( '\\', substr( $class_name, strlen( $namespace ) ) );
	$count_parts = count( $parts );

	foreach ( $parts as $key => $part ) {
		$part = str_replace( '_', '-', strtolower( $part ) );
		$prefix = ( $key + 1 === $count_parts ) ? '/class-' : '/';
		$path .= $prefix . $part;
	}

	$path .= '.php';

	if ( ! file_exists( $path ) ) {
		return false;
	}

	require_once $path;

	return true;
});
