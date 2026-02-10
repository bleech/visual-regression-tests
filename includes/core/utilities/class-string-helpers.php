<?php

namespace Vrts\Core\Utilities;

class String_Helpers {
	/**
	 * Converts a string from camel case to kebap case.
	 *
	 * @param string $str The string to convert.
	 *
	 * @return string
	 */
	public static function camel_case_to_kebap( $str ) {
		return strtolower( preg_replace( '/([a-zA-Z])(?=[A-Z])/', '$1-', $str ) );
	}

	/**
	 * Strips all HTML tags including script and style,
	 * and trims text to a certain number of words.
	 *
	 * @param string $str The string to trim and strip.
	 * @param int    $length The string length to return.
	 *
	 * @return string
	 */
	public static function trim_strip( $str = '', $length = 25 ) {
		return wp_trim_words( wp_strip_all_tags( $str ), $length, '&hellip;' );
	}

	/**
	 * Splits a camel case string.
	 *
	 * @param string $str The string to split.
	 *
	 * @return string
	 */
	public static function split_camel_case( $str ) {
		$a = preg_split(
			'/(^[^A-Z]+|[A-Z][^A-Z]+)/',
			$str,
			-1,
			// no limit for replacement count.
			PREG_SPLIT_NO_EMPTY
			// don't return empty elements.
			| PREG_SPLIT_DELIM_CAPTURE
			// don't strip anything from output array.
		);
		return implode( ' ', $a );
	}

	/**
	 * Converts a string from kebap case to camel case.
	 *
	 * @param string  $str The string to convert.
	 * @param boolean $capitalize_first_character Sets if the first character should be capitalized.
	 *
	 * @return string
	 */
	public static function kebap_case_to_camel_case( $str, $capitalize_first_character = false ) {
		$str = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $str ) ) );
		if ( false === $capitalize_first_character ) {
			$str[0] = strtolower( $str[0] );
		}
		return $str;
	}

	/**
	 * Removes a prefix from a string.
	 *
	 * @param string $prefix The prefix to be removed.
	 * @param string $str The string to manipulate.
	 *
	 * @return string
	 */
	public static function remove_prefix( $prefix, $str ) {
		if ( self::starts_with( $prefix, $str ) ) {
			return substr( $str, strlen( $prefix ) );
		}

		return $str;
	}

	/**
	 * Checks if a string starts with a certain string.
	 *
	 * @param string $search The string to search for.
	 * @param string $str The string to look into.
	 *
	 * @return boolean Returns true if the subject string starts with the search string.
	 */
	public static function starts_with( $search, $str ) {
		$starts_with = substr( $str, 0, strlen( $search ) );
		return $search === $starts_with;
	}

	/**
	 * Checks if a string ends with a certain string.
	 *
	 * @param string $search The string to search for.
	 * @param string $str The string to look into.
	 *
	 * @return boolean Returns true if the subject string ends with the search string.
	 */
	public static function ends_with( $search, $str ) {
		$search_length = strlen( $search );
		$str_length = strlen( $str );
		if ( $search_length > $str_length ) {
			return false;
		}
		return 0 === substr_compare( $str, $search, $str_length - $search_length, $search_length );
	}

	/**
	 * Remove white space in string.
	 *
	 * @param string $str The string to look into.
	 *
	 * @return string String without whitespace.
	 */
	public function remove_white_space( $str ) {
		$str = str_replace( "\t", ' ', $str );
		$str = str_replace( "\n", '', $str );
		$str = str_replace( "\r", '', $str );

		while ( stristr( $str, ' ' ) ) {
			$str = str_replace( ' ', '', $str );
		}

		return $str;
	}

	/**
	 * Format lines.
	 *
	 * @param array $lines Lines to format.
	 * @param int   $tabs Number of tabs for the offest.
	 *
	 * @return string Formated lines.
	 */
	public static function format_lines( $lines, $tabs = 1 ) {
		$line_tabs = str_repeat( "\t", $tabs );
		$end_tabs = str_repeat( "\t", $tabs - 1 );

		$lines = array_map( function ( $line ) use ( $line_tabs ) {
			return "\n{$line_tabs}{$line}";
		}, $lines);

		return implode( '', $lines ) . "\n{$end_tabs}";
	}

	/**
	 * Truncate a string.
	 *
	 * @param string $str The string to truncate.
	 * @param int    $length The length to truncate to.
	 * @param string $append The string to append.
	 *
	 * @return string
	 */
	public static function truncate( $str, $length = 100, $append = '...' ) {
		$str = trim( $str );
		return ( strlen( $str ) > $length ) ? substr( $str, 0, $length - strlen( $append ) ) . $append : $str;
	}
}
