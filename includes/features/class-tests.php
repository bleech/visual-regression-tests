<?php

namespace Vrts\Features;

use Vrts\Models\Test;

class Tests {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Allows developers to run tests by calling `do_action( 'vrts_run_tests' )`.
		add_action( 'vrts_run_tests', [ $this, 'run_api_tests' ] );
		add_action( 'upgrader_process_complete', [ $this, 'run_upgrader_tests' ], 10, 99 );
	}

	/**
	 * Run api tests.
	 *
	 * @param string $notes Notes.
	 */
	public static function run_api_tests( $notes = '' ) {
		self::run_tests( 'api', $notes );
	}

	/**
	 * Run upgrader tests.
	 *
	 * @param \WP_Upgrader $upgrader Upgrader.
	 * @param array        $options Options.
	 */
	public static function run_upgrader_tests( $upgrader, $options ) {
		$updates = [];
		if ($options['action'] == 'update') {
			switch ($options['type']) {
				case 'plugin':
					foreach($options['plugins'] as $plugin) {
						$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
						$new_version = $plugin_data['Version'];
						$name = $plugin_data['Name'];
						$slug = dirname(plugin_basename($plugin));
						$updates[] = [
							'type' => 'plugin',
							'name' => $name,
							'slug' => $slug,
							'version' => $new_version,
						];
					}
					break;
				case 'theme':
					foreach($options['themes'] as $theme) {
						$theme_data = wp_get_theme($theme);
						$new_version = $theme_data->get('Version');
						$name = $theme_data->get('Name');
						$slug = $theme;
						$updates[] = [
							'type' => 'theme',
							'name' => $name,
							'slug' => $slug,
							'version' => $new_version,
						];
					}
					break;
				case 'core':
					$new_version = static::get_wp_version();
					$updates[] = [
						'type' => 'core',
						'version' => $new_version,
					];
					break;
				case 'translation':
					$translations = $options['translations'] ?? [];
					foreach ($translations as $translation) {
						$type = $translation['type'];
						$slug = $translation['slug'];
						$language = $translation['language'];
						if ($type === 'plugin') {
							$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php');
							$name = $plugin_data['Name'];
						} elseif ($type === 'theme') {
							$theme_data = wp_get_theme($slug);
							$name = $theme_data->get('Name');
						} else {
							$name = 'WordPress';
						}
						$updates[] = [
							'type' => $type,
							'name' => $name,
							'slug' => $slug,
							'language' => $language,
						];
					}
					break;
			}
		}
		if ( ! empty( $updates ) ) {
			self::run_tests( 'update', null, $updates );
		}
	}

	/**
	 * Run tests.
	 *
	 * @param string $trigger Trigger.
	 * @param string $trigger_notes Trigger notes.
	 * @param array  $trigger_meta Trigger meta.
	 */
	private static function run_tests( $trigger, $trigger_notes, $trigger_meta = null ) {
		$has_subscription = Subscription::get_subscription_status();

		if ( ! $has_subscription ) {
			return false;
		}

		$tests = Test::get_all_running();
		$service_test_ids = wp_list_pluck( $tests, 'service_test_id' );
		Service::run_manual_tests( $service_test_ids, [
			'trigger' => $trigger,
			'trigger_notes' => $trigger_notes,
			'trigger_meta' => $trigger_meta,
		] );
	}

	static function get_wp_version() {
		return (function() {
			require ABSPATH . WPINC . '/version.php';
			return $wp_version;
		})();
	}
}
