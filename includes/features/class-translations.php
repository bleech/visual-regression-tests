<?php

namespace Vrts\Features;

class Translations {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
		add_action( 'init', [ $this, 'set_script_translations' ] );
	}

	/**
	 * Load Localisation files.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'visual-regression-tests', false, plugin_basename( dirname( VRTS_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Set script translations.
	 */
	public function set_script_translations() {
		wp_set_script_translations( 'vrts-editor', 'visual-regression-tests' );
	}
}
