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

if ( ! function_exists( 'tour_table_has_column' ) ) {
	/**
	 * Check whether a database table has a column.
	 *
	 * @param string $table  Table name.
	 * @param string $column Column name.
	 * @return bool
	 */
	function tour_table_has_column( $table, $column ) {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
				DB_NAME,
				$table,
				$column
			)
		);

		return ! empty( $result );
	}
}

if ( ! function_exists( 'tour_get_default_transport' ) ) {
	/**
	 * Get the transport marked as default.
	 *
	 * @return array|null
	 */
	function tour_get_default_transport() {
		global $wpdb;

		if ( ! tour_table_has_column( TOUR_TRANSPORTS, 'default' ) ) {
			return null;
		}

		return $wpdb->get_row(
			'SELECT * FROM ' . TOUR_TRANSPORTS . ' WHERE `default` = 1 LIMIT 1',
			ARRAY_A
		);
	}
}
