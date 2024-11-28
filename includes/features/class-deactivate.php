<?php

namespace Vrts\Features;

use Vrts\Models\Test_Run;

class Deactivate {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_deactivation_hook( vrts()->get_plugin_file(), [ $this, 'deactivate' ] );
	}

	/**
	 * Deactivate plugin.
	 */
	public function deactivate() {
		Test_Run::delete_all_not_finished();
		Service::disconnect_service();
	}
}
