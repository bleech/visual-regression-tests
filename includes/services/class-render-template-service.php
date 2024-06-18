<?php

namespace Vrts\Services;

class Render_Template_Service {
	function render_template($template, $context = []) {
		// Check if the file exists
		$template_path = plugin_dir_path( VRTS_PLUGIN_FILE ) . 'components/' . $template . '.php';

		if (!file_exists($template_path)) {
			return '';
		}

		// Extract the variables from the context array
		extract($context);

		// Start output buffering
		ob_start();

		// Include the template file
		include $template_path;

		// Get the contents of the buffer
		$output = ob_get_clean();

		return $output;
	}
}

