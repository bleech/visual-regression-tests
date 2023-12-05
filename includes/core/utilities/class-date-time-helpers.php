<?php

namespace Vrts\Core\Utilities;

use DateTimeZone;

class Date_Time_Helpers {
	/**
	 * Get the date and time WordPress native formatted.
	 *
	 * @param mixed $date a DateTime string.
	 *
	 * @return string Formatted date and time.
	 */
	public static function get_formatted_date_time( $date = null ) {
		if ( null === $date ) {
			return null;
		}
		$date = self::date_from_gmt( $date );

		$formatted_date = sprintf(
			/* translators: 1: Date, 2: Time. */
			esc_html_x( '%1$s at %2$s', 'date at time', 'visual-regression-tests' ),
			/* translators: Date format. See https://www.php.net/manual/datetime.format.php */
			date_format( $date, esc_html_x( 'Y/m/d', 'date format', 'visual-regression-tests' ) ),
			/* translators: Time format. See https://www.php.net/manual/datetime.format.php */
			date_format( $date, esc_html_x( 'g:i a', 'time format', 'visual-regression-tests' ) )
		);
		return $formatted_date;
	}

	/**
	 * Get the date and time WordPress native formatted.
	 *
	 * @param mixed $date a DateTime string.
	 *
	 * @return string Formatted date and time.
	 */
	public static function get_formatted_relative_date_time( $date = null ) {
		if ( null === $date ) {
			return null;
		}
		$date = self::date_from_gmt( $date );

		$formatted_date = sprintf(
			/* translators: 1: Date, 2: Time. */
			esc_html_x( '%1$s at %2$s', 'date at time', 'visual-regression-tests' ),
			/* translators: Date format. See https://www.php.net/manual/datetime.format.php */
			date_format( $date, esc_html_x( 'Y/m/d', 'date format', 'visual-regression-tests' ) ),
			/* translators: Time format. See https://www.php.net/manual/datetime.format.php */
			date_format( $date, esc_html_x( 'g:i a', 'time format', 'visual-regression-tests' ) )
		);
		return '<vrts-relative-time time="' . date_format( $date, 'c' ) . '">' . $formatted_date . '</vrts-relative-time>';
	}

	/**
	 * Get the date WordPress native formatted.
	 *
	 * @param mixed $date a DateTime string.
	 *
	 * @return string Formatted date.
	 */
	private static function date_from_gmt( $date ) {
		$date = date_create( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( wp_timezone() );

		return $date;
	}
}
