<?php

namespace Vrts\Core\Traits;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;

trait Macroable {
	/**
	 * Macros.
	 *
	 * @var array
	 */
	protected static $macros = [];

	/**
	 * Registerd macro.
	 *
	 * @param string   $name Name of macro function.
	 * @param callable $macro Callback function.
	 */
	public static function macro( $name, $macro ) {
		static::$macros[ $name ] = $macro;
	}

	/**
	 * Register mixins.
	 *
	 * @param class $mixin Class with mixins.
	 */
	public static function mixin( $mixin ) {
		$methods = ( new ReflectionClass( $mixin ) )->getMethods(
			ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
		);

		foreach ( $methods as $method ) {
			$method->setAccessible( true );

			static::macro( $method->name, $method->invoke( $mixin ) );
		}
	}

	/**
	 * Return true if has macro.
	 *
	 * @param string $name Name of macro function.
	 *
	 * @return bool
	 */
	public static function has_macro( $name ) {
		return isset( static::$macros[ $name ] );
	}

	/**
	 * Call static method.
	 *
	 * @param string $method The function to be called.
	 * @param array  $parameters The parameters to be passed to the function, as an indexed array.

	 * @return callable The function to be called.
	 *
	 * @throws BadMethodCallException When method doesn't exist.
	 */
	public static function __callStatic( $method, $parameters ) {
		if ( ! static::has_macro( $method ) ) {
			throw new BadMethodCallException( "Method {$method} does not exist." );
		}

		$macro = static::$macros[ $method ];

		if ( $macro instanceof Closure ) {
			return call_user_func_array( Closure::bind( $macro, null, static::class ), $parameters );
		}

		return call_user_func_array( $macro, $parameters );
	}

	/**
	 * Call method.
	 *
	 * @param string $method The function to be called.
	 * @param array  $parameters The parameters to be passed to the function, as an indexed array.

	 * @return callable The function to be called.
	 *
	 * @throws BadMethodCallException When method doesn't exist.
	 */
	public function __call( $method, $parameters ) {
		if ( ! static::has_macro( $method ) ) {
			throw new BadMethodCallException( "Method {$method} does not exist." );
		}

		$macro = static::$macros[ $method ];

		if ( $macro instanceof Closure ) {
			return call_user_func_array( $macro->bindTo( $this, static::class ), $parameters );
		}

		return call_user_func_array( $macro, $parameters );
	}
}
