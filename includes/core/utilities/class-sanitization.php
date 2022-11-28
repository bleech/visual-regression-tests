<?php

namespace Vrts\Core\Utilities;

class Sanitization {
	/**
	 * Checkbox sanitization callback.
	 *
	 * @param bool $checked Whether the checkbox is checked.
	 *
	 * @return bool Whether the checkbox is checked.
	 */
	public static function sanitize_checkbox( $checked ) {
		return $checked ? true : false;
	}

	/**
	 * HTML sanitization callback.
	 *
	 * @param string $html HTML to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_html( $html ) {
		return wp_filter_post_kses( $html );
	}

	/**
	 * No-HTML sanitization callback.
	 *
	 * @param string $nohtml The no-HTML content to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_nohtml( $nohtml ) {
		return wp_filter_nohtml_kses( $nohtml );
	}

	/**
	 * Number sanitization callback.
	 *
	 * @param int $number Number to sanitize.
	 *
	 * @return int
	 */
	public static function sanitize_number_absint( $number ) {
		// Ensure $number is an absolute integer (whole number, zero or greater).
		return absint( $number );
	}

	/**
	 * URL sanitization callback.
	 *
	 * @param string $url URL to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_url( $url ) {
		return esc_url_raw( $url );
	}
}
