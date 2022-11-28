<?php

namespace Vrts\Core\Utilities;

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
		$formatted_date = get_date_from_gmt( $date );
		$date = date_create( $formatted_date );

		$date = sprintf(
			/* translators: 1: Date, 2: Time. */
			esc_html_x( '%1$s at %2$s', 'date at time', 'visual-regression-tests' ),
			/* translators: Date format. See https://www.php.net/manual/datetime.format.php */
			date_format( $date, esc_html_x( 'Y/m/d', 'date format', 'visual-regression-tests' ) ),
			/* translators: Time format. See https://www.php.net/manual/datetime.format.php */
			date_format( $date, esc_html_x( 'g:i a', 'time format', 'visual-regression-tests' ) )
		);
		return $date;
	}
}
