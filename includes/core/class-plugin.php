<?php

namespace Vrts\Core;

use Vrts\Core\Settings\Manager;
use Vrts\Core\Traits\Singleton;
use Vrts\Core\Traits\Macroable;
use Exception;

/**
 * Main class responsible for defining all plugin functionalities.
 *
 * It has methods that are definied in other classes and made available here using the Macroable.
 *
 * @method string thisMenuHasMacro() Added in Vrts\Features\Menu
 */
class Plugin {
	use Singleton;
	use Macroable;

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Plugin identifier.
	 *
	 * @var string
	 */
	protected $plugin_identifier;

	/**
	 * Modules and objects instances list
	 *
	 * @var array
	 */
	protected $factory = [];

	/**
	 * Load plugin functions and features.
	 *
	 * @param string $plugin_identifier Plugin identifier.
	 * @param array  $features Plugin features.
	 */
	public function setup( $plugin_identifier, $features ) {
		$this->plugin_identifier = $plugin_identifier;
		$this->plugin_file = VRTS_PLUGIN_FILE;

		$this->features( $features );
	}

	/**
	 * Get Plugin identifier.
	 *
	 * @return string Plugin identifier.
	 */
	public function get_plugin_identifier() {
		return str_replace( '-', '_', $this->plugin_identifier );
	}

	/**
	 * Get the plugin url.
	 *
	 * @param string $file File to return the url for in the plugin directory.
	 *
	 * @return string
	 */
	public function get_plugin_url( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$url = plugin_dir_url( $this->plugin_file );
		} else {
			$url = plugin_dir_url( $this->plugin_file ) . $file;
		}

		return $url;
	}

	/**
	 * Get the plugin path.
	 *
	 * @param string $file File to return the path for in the plugin directory.
	 *
	 * @return string
	 */
	public function get_plugin_path( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$path = plugin_dir_path( $this->plugin_file );
		} else {
			$path = plugin_dir_path( $this->plugin_file ) . $file;
		}

		return $path;
	}


	/**
	 * Get the plugin file.
	 *
	 * @return string
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * Auto include files from defined directories.
	 *
	 * @param array $directories Directories to auto include files from.
	 */
	protected function includes( $directories ) {
		foreach ( $directories as $directory ) {
			foreach ( glob( $this->get_plugin_path( "{$directory}/*.php" ) ) as $filename ) {
				require_once $filename;
			}
		}
	}

	/**
	 * Load plugin features.
	 *
	 * @param array $features Plugin features.
	 */
	protected function features( $features ) {
		foreach ( $features as $namespace => $directory ) {
			foreach ( glob( $this->get_plugin_path( "{$directory}/class-*.php" ) ) as $filename ) {
				$dir = $this->get_plugin_path( $directory );
				$filename = str_replace( $dir, '', $filename );
				$class_name = str_replace( [ '/class-', '.php' ], '', $filename );
				$class_name = ucwords( $class_name, '-' );
				$class_name = str_replace( '-', '_', $class_name );
				$class_name = $namespace . $class_name;
				new $class_name();
			}
		}
	}

	/**
	 * Create objects.
	 *
	 * @param string $key Array key that the object will be accessible.
	 * @param object $object object that needs to created.
	 *
	 * @return object Created object.
	 */
	protected function factory( $key, $object ) {
		if ( ! isset( $this->factory[ $key ] ) ) {
			$this->factory[ $key ] = $object;
		}

		return $this->factory[ $key ];
	}

	/**
	 * Get Settings Manager.
	 *
	 * @return Manager Settings Manager.
	 */
	public function settings() {
		return $this->factory( 'settings', new Manager() );
	}

	/**
	 * Get a component with passing arguments.
	 *
	 * Makes it easy for a plugin to reuse sections of code.
	 *
	 * @param string $name The component name.
	 * @param array  $data Pass data with the component load.
	 *
	 * @return string Component markup
	 *
	 * @throws Exception If there is no file found.
	 */
	public function get_component( $name, $data = [] ) {
		ob_start();

		if ( file_exists( $this->get_plugin_path( "components/{$name}/index.php" ) ) ) {
			include $this->get_plugin_path( "components/{$name}/index.php" );
		} else {
			throw new Exception( "Components file 'components/{$name}/index.php' cannot be located" );
		}

		return ob_get_clean();
	}

	/**
	 * Load a component with passing arguments.
	 *
	 * Makes it easy for a plugin to reuse sections of code.
	 *
	 * @param string $name The component name.
	 * @param array  $data Pass data with the component load.
	 *
	 * @throws Exception If there is no file found.
	 */
	public function component( $name, $data = [] ) {
		$safe_escaped_component = $this->get_component( $name, $data );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's escaped.
		echo $safe_escaped_component;
	}

	/**
	 * Get the plugin informations.
	 *
	 * @param string $info What information do we want.
	 *
	 * @return string Desired plugin information.
	 */
	public function get_plugin_info( $info ) {
		$plugin = get_plugin_data( $this->plugin_file );

		$infos = [
			'name' => 'Name',
			'version' => 'Version',
			'uri' => 'PluginURI',
			'author' => 'Author',
			'author_uri' => 'AuthorURI',
			'description' => 'Description',
			'requires_wp'  => 'RequiresWP',
			'requires_php' => 'RequiresPHP',
			'text_domain' => 'TextDomain',
			'domain_path' => 'DomainPath',
		];

		return isset( $infos[ $info ] ) ? $plugin[ $infos[ $info ] ] : '';
	}

	/**
	 * Get the plugin logo icon.
	 *
	 * @param boolean $base64 return base 64 encoded or not.
	 *
	 * @return string the logo as string.
	 */
	public function get_plugin_logo_icon( $base64 = true ) {
		$icon_path = $this->get_plugin_path( 'assets/icons/vrts-logo-icon.svg' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- It's a file.
		$svg = file_get_contents( $icon_path );

		if ( $base64 ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- It's a file.
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
		}

		return $svg;
	}

	/**
	 * Get public post types.
	 *
	 * @return array public post types.
	 */
	public function get_public_post_types() {
		$default_post_types = [ 'post', 'page' ];
		$custom_post_types = get_post_types( [
			'public' => true,
			'_builtin' => false,
		] );

		return array_merge( $default_post_types, $custom_post_types );
	}

	/**
	 * Get snapshot placeholder image.
	 *
	 * @param boolean $base64 return base 64 encoded or not.
	 *
	 * @return string the placeholder image as string.
	 */
	public function get_snapshot_placeholder_image( $base64 = true ) {
		$icon_path = $this->get_plugin_path( 'assets/images/vrts-snapshot-placeholder.svg' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- It's a file.
		$svg = file_get_contents( $icon_path );

		if ( $base64 ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- It's a file.
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
		}

		return $svg;
	}
}
