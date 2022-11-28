<?php

namespace Vrts\Core\Settings;

class Manager {
	/**
	 * Sections
	 *
	 * @var array
	 */
	protected $sections = [];

	/**
	 * Settings.
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		$this->add_sections();
		$this->add_settings();
	}

	/**
	 * Get option.
	 *
	 * @param string $id Settings setting ID.
	 *
	 * @return mixed Option value.
	 */
	public function get_option( $id ) {
		$default_value = isset( $this->settings[ $id ] ) ? $this->settings[ $id ]['default'] : false;
		return get_option( $id, $default_value );
	}

	/**
	 * Get settings section page.
	 *
	 * @param string $id Setting section ID.
	 *
	 * @return string
	 */
	private function get_section_page( $id ) {
		return isset( $this->sections[ $id ] ) ? $this->sections[ $id ]['page'] : false;
	}

	/**
	 * Output field.
	 *
	 * @param array $args Setting args.
	 */
	private function get_field( $args ) {
		$value = $this->get_option( $args['id'] );
		include vrts()->get_plugin_path( "includes/core/settings/field-{$args['type']}/index.php" );
	}

	/**
	 * Add all sections.
	 */
	private function add_sections() {
		foreach ( $this->sections as $id => $args ) {
			add_settings_section(
				$id,
				$args['title'] ?? '',
				$args['callback'] ?? '__return_false',
				$args['page'] ?? 'general'
			);
		}
	}

	/**
	 * Add all settings.
	 */
	private function add_settings() {
		foreach ( $this->settings as $id => $args ) {
			register_setting(
				$this->get_section_page( $args['section'] ),
				$id,
				[
					'type' => $args['value_type'] ?? 'string',
					'sanitize_callback' => $args['sanitize_callback'] ?? null,
					'show_in_rest' => $args['show_in_rest'] ?? false,
					'default' => $args['default'] ?? '',
				]
			);

			add_settings_field(
				$id,
				$args['title'],
				function() use ( $args ) {
					$this->get_field( $args );
				},
				$this->get_section_page( $args['section'] ),
				$args['section'],
				[
					'label_for' => ! in_array( $args['type'], [ 'checkbox', 'radio' ], true ) ? $id : '',
				]
			);
		}//end foreach
	}

	/**
	 * Add a settings section.
	 *
	 * @param array $args Array of properties for the new section object.
	 */
	public function add_section( $args = [] ) {
		$this->sections[ $args['id'] ] = $args;
	}

	/**
	 * Add a settings setting.
	 *
	 * @param array $args Array of properties for the new setting and control object.
	 */
	public function add_setting( $args = [] ) {
		$this->settings[ $args['id'] ] = $args;
	}
}
