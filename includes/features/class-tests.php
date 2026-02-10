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
		if ( 'update' === $options['action'] ) {
			switch ( $options['type'] ) {
				case 'plugin':
					if ( isset( $options['plugins'] ) ) {
						if ( is_array( $options['plugins'] ) ) {
							foreach ( $options['plugins'] as $plugin ) {
								$updates[] = static::add_plugin( $plugin );
							}
						} else {
							$updates[] = static::add_plugin( $options['plugins'] );
						}
					}
					if ( isset( $options['plugin'] ) ) {
						$updates[] = static::add_plugin( $options['plugin'] );
					}
					break;
				case 'theme':
					if ( isset( $options['themes'] ) ) {
						if ( is_array( $options['themes'] ) ) {
							foreach ( $options['themes'] as $theme ) {
								$updates[] = static::add_theme( $theme );
							}
						} else {
							$updates[] = static::add_theme( $options['themes'] );
						}
					}
					if ( isset( $options['theme'] ) ) {
						$updates[] = static::add_theme( $options['theme'] );
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
					if ( isset( $options['translations'] ) ) {
						if ( is_array( $options['translations'] ) ) {
							foreach ( $options['translations'] as $translation ) {
								$updates[] = static::add_translation( $translation );
							}
						} else {
							$updates[] = static::add_translation( $options['translations'] );
						}
					}
					if ( isset( $options['translation'] ) ) {
						$updates[] = static::add_translation( $options['translation'] );
					}
					break;
			}//end switch
		}//end if
		if ( ! empty( $updates ) ) {
			self::run_tests( 'update', null, $updates );
		}
	}

	/**
	 * Prepare plugin for updates.
	 *
	 * @param string $plugin Plugin.
	 *
	 * @return array
	 */
	private static function add_plugin( $plugin ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		$new_version = $plugin_data['Version'];
		$name = $plugin_data['Name'];
		$slug = dirname( plugin_basename( $plugin ) );
		return [
			'type' => 'plugin',
			'name' => $name,
			'slug' => $slug,
			'version' => $new_version,
		];
	}

	/**
	 * Prepare theme for updates.
	 *
	 * @param string $theme Theme.
	 *
	 * @return array
	 */
	private static function add_theme( $theme ) {
		$theme_data = wp_get_theme( $theme );
		$new_version = $theme_data->get( 'Version' );
		$name = $theme_data->get( 'Name' );
		$slug = $theme;
		return [
			'type' => 'theme',
			'name' => $name,
			'slug' => $slug,
			'version' => $new_version,
		];
	}

	/**
	 * Prepare translation for updates.
	 *
	 * @param array $translation Translation.
	 *
	 * @return array
	 */
	private static function add_translation( $translation ) {
		$type = $translation['type'];
		$slug = $translation['slug'];
		$language = $translation['language'];
		if ( 'plugin' === $type ) {
			$plugin_data = static::get_plugin_data( $slug );
			$name = $plugin_data['Name'];
		} elseif ( 'theme' === $type ) {
			$theme_data = wp_get_theme( $slug );
			$name = $theme_data->get( 'Name' );
		} else {
			$name = 'WordPress';
		}
		return [
			'type' => $type,
			'name' => $name,
			'slug' => $slug,
			'language' => $language,
		];
	}

	/**
	 * Get plugin data.
	 *
	 * @param string $plugin_slug_or_file Plugin slug or file.
	 *
	 * @return array
	 */
	private static function get_plugin_data( $plugin_slug_or_file ) {
		$plugin_file = WP_PLUGIN_DIR . '/' . $plugin_slug_or_file;
		$plugin_data = get_plugin_data( $plugin_file );
		if ( empty( $plugin_data['Name'] ) ) {
			$plugins = get_plugins();
			foreach ( $plugins as $file => $local_plugin_data ) {
				$slug = dirname( $file );
				if ( $slug === $plugin_slug_or_file ) {
					$plugin_data = $local_plugin_data;
					break;
				}
			}
		}
		return $plugin_data;
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

	/**
	 * Get WordPress version.
	 *
	 * @return string
	 */
	public static function get_wp_version() {
		return ( function () {
			require ABSPATH . WPINC . '/version.php';
			return $wp_version;
		} )();
	}
}
