<?php

namespace Vrts\Core\Traits;

trait Singleton {
	/**
	 * The reference to Singleton instance of this class.
	 *
	 * @var Singleton
	 */
	protected static $instance;

	/**
	 * Returns the Singleton instance of this class.
	 *
	 * @return Singleton The Singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * Singleton via the `new` operator from outside of this class.
	 */
	protected function __construct() {}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * Singleton instance.
	 */
	private function __clone() {}

	/**
	 * Public unserialize method to prevent unserializing of the Singleton
	 * instance.
	 */
	public function __wakeup() {}
}
