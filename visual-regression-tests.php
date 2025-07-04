<?php
/**
 * Plugin Name:       VRTs – Visual Regression Tests
 * Plugin URI:        https://vrts.app/
 * Description:       Find issues before others do – every time. With automatic screenshots, daily comparisons, and instant tests after WordPress and plugin updates.
 * Version:           2.0.4
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            Bleech
 * Author URI:        https://bleech.de
 * Text Domain:       visual-regression-tests
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

use Vrts\Core\Plugin;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'VRTS_PLUGIN_FILE' ) ) {
	define( 'VRTS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'VRTS_SERVICE_ENDPOINT' ) ) {
	define( 'VRTS_SERVICE_ENDPOINT', getenv( 'VRTS_SERVICE_ENDPOINT' ) ?: 'https://bleech-vrts-app.blee.ch/api/v1/' );
}

// Autoloader via Composer if exists.
if ( file_exists( plugin_dir_path( VRTS_PLUGIN_FILE ) . 'vendor/autoload.php' ) ) {
	require plugin_dir_path( VRTS_PLUGIN_FILE ) . 'vendor/autoload.php';
}

// Custom autoloader.
require plugin_dir_path( VRTS_PLUGIN_FILE ) . 'includes/autoload.php';

if ( ! function_exists( 'vrts' ) ) {
	/**
	 * Main function responsible for accessing plugin functionalities.
	 *
	 * @return Plugin Class instance.
	 */
	function vrts() {
		return Plugin::get_instance();
	}
}

/**
 * Plugin Setup.
 *
 * Load and init theme features.
 */
vrts()->setup( 'vrts', [
	'Vrts\\Features\\' => 'includes/features',
	'Vrts\\Tables\\' => 'includes/tables',
	'Vrts\\Rest_Api\\' => 'includes/rest-api',
]);
