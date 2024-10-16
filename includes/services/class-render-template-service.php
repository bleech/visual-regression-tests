<?php

namespace Vrts\Services;

class Render_Template_Service {
	/**
	 * Render a template.
	 *
	 * @param string $template Template name.
	 * @param array  $context Context.
	 *
	 * @return string
	 */
	public function render_template( $template, $context = [] ) {
		$template_path = plugin_dir_path( VRTS_PLUGIN_FILE ) . 'components/' . $template . '.php';

		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- It's ok.
		extract( $context );

		ob_start();

		include $template_path;

		$output = ob_get_clean();

		return $output;
	}
}

