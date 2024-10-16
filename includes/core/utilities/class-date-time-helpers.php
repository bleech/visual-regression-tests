<?php

namespace Vrts\Core\Utilities;

use DateTime;
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
			static::extract_date( $date ),
			/* translators: Time format. See https://www.php.net/manual/datetime.format.php */
			static::extract_time( $date )
		);
		return '<time datetime="' . date_format( $date, 'c' ) . '">' . $formatted_date . '</time>';
	}

	/**
	 * Get the date WordPress native formatted.
	 *
	 * @param mixed $date a DateTime string.
	 *
	 * @return DateTime DateTime instance.
	 */
	private static function date_from_gmt( $date ) {
		$date = date_create( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( wp_timezone() );

		return $date;
	}

	/**
	 * Extract the date from a DateTime object.
	 *
	 * @param DateTime $input_date a DateTime object.
	 *
	 * @return string Formatted date.
	 */
	private static function extract_date( $input_date ) {
		// Get today's date at midnight.
		$today = new DateTime( 'today' );

		// Clone input date and set time to midnight.
		$comparison_date = clone $input_date;
		$comparison_date->setTime( 0, 0, 0 );

		// Calculate the difference in days.
		$difference_in_seconds = $comparison_date->getTimestamp() - $today->getTimestamp();
		$difference_in_days = (int) round( $difference_in_seconds / ( 60 * 60 * 24 ) );

		// Determine if the date is today, tomorrow, or yesterday.
		if ( 0 === $difference_in_days ) {
			return __( 'Today', 'visual-regression-testing' );
		} elseif ( 1 === $difference_in_days ) {
			return __( 'Tomorrow', 'visual-regression-testing' );
		} elseif ( -1 === $difference_in_days ) {
			return __( 'Yesterday', 'visual-regression-testing' );
		}
		return $input_date->format( 'D, Y/m/d' );
	}

	/**
	 * Extract the time from a DateTime object.
	 *
	 * @param DateTime $input_date a DateTime object.
	 *
	 * @return string Formatted time.
	 */
	private static function extract_time( $input_date ) {
		return $input_date->setTimezone( wp_timezone() )->format( 'g:i a' );
	}
}
