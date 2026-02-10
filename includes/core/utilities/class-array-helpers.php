<?php

namespace Vrts\Core\Utilities;

class Array_Helpers {
	/**
	 * Parses the string into variables without the max_input_vars limitation.
	 *
	 * @param string $str String.
	 *
	 * @return array Parsed array.
	 */
	public static function parse_str( $str ) {
		if ( '' === $str ) {
			return false;
		}

		$result = [];
		$pairs = explode( '&', $str );

		foreach ( $pairs as $key => $pair ) {

			// use the original parse_str() on each element.
			parse_str( $pair, $params );

			$k = key( $params );

			if ( ! isset( $result[ $k ] ) ) {
				$result += $params;
			} else {
				$result[ $k ] = self::array_merge_recursive( $result[ $k ], $params[ $k ] );
			}
		}

		return $result;
	}

	/**
	 * Merge arrays without converting values with duplicate keys to arrays as array_merge_recursive does.
	 *
	 * As seen here http://php.net/manual/en/function.array-merge-recursive.php#92195
	 *
	 * @param array $array1 First array.
	 * @param array $array2 Second array.
	 *
	 * @return array Merged array.
	 */
	public static function array_merge_recursive( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => $value ) {

			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = self::array_merge_recursive( $merged[ $key ], $value );
			} elseif ( is_numeric( $key ) && isset( $merged[ $key ] ) ) {
				$merged[] = $value;
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Recursive wp_parse_args for multidimensional arrays.
	 *
	 * @see http://mekshq.com/recursive-wp-parse-args-wordpress-function/.
	 *
	 * @param array $args Value to merge with $defaults.
	 * @param array $defaults Array that serves as the defaults.
	 *
	 * @return array Merged user defined values with defaults.
	 */
	public static function parse_args( $args, $defaults ) {
		$args = (array) $args;
		$defaults = (array) $defaults;
		$result = $defaults;

		foreach ( $args as $k => $v ) {
			if ( is_array( $v ) && isset( $result[ $k ] ) ) {
				$result[ $k ] = self::parse_args( $v, $result[ $k ] );
			} else {
				$result[ $k ] = $v;
			}
		}

		return $result;
	}

	/**
	 * Implode array keys with desired value.
	 *
	 * @param array        $arr Array to implode.
	 * @param string       $value Checked for this value.
	 * @param string|array $remove Array key or keys to remove before implode.
	 * @param string       $before Value to prepend to array keys.
	 * @param string       $after Value to append to array keys.
	 *
	 * @return string Imploded array keys with value.
	 */
	public static function implode_array_keys( $arr, $value, $remove = false, $before = '', $after = '' ) {
		if ( is_array( $remove ) ) {
			foreach ( $remove as $key ) {
				unset( $arr[ $key ] );
			}
		} elseif ( $remove ) {
			unset( $arr[ $remove ] );
		}

		$new_array = [];

		foreach ( $arr as $key => $item ) {
			if ( $arr[ $key ] === $value ) {
				$new_array[ $before . $key . $after ] = $item;
			}
		}

		return implode( ' ', array_keys( $new_array ) );
	}
}
