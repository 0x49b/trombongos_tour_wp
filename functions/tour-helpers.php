<?php
/**
 * Shared helpers for Trombongos Tour (admin, API, frontend).
 */

if ( ! function_exists( 'tour_format_date' ) ) {
	/**
	 * Format date to DD.MM.YYYY.
	 *
	 * @param string|null $date Date string.
	 * @return string|null
	 */
	function tour_format_date( $date ) {
		if ( empty( $date ) ) {
			return null;
		}

		$dt = new DateTime( $date );

		return $dt->format( 'd.m.Y' );
	}
}

if ( ! function_exists( 'tour_format_date_range' ) ) {
	/**
	 * Format a date range as d.m.Y - d.m.Y.
	 *
	 * @param string $start Start date.
	 * @param string $end   End date.
	 * @return string
	 */
	function tour_format_date_range( $start, $end ) {
		return tour_format_date( $start ) . ' - ' . tour_format_date( $end );
	}
}

if ( ! function_exists( 'tour_format_time' ) ) {
	/**
	 * Format time to HH:MM.
	 *
	 * @param string|null $time Time string.
	 * @return string|null
	 */
	function tour_format_time( $time ) {
		if ( empty( $time ) ) {
			return null;
		}

		$dt = new DateTime( $time );

		return $dt->format( 'H:i' );
	}
}

if ( ! function_exists( 'tour_get_day_name' ) ) {
	/**
	 * Get day name from number.
	 *
	 * @param int $day_num Day index (0 = Monday).
	 * @return string
	 */
	function tour_get_day_name( $day_num ) {
		$days = array( 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' );

		return isset( $days[ $day_num ] ) ? $days[ $day_num ] : '';
	}
}
