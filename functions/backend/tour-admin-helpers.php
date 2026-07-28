<?php
/**
 * Shared helper functions for the Trombongos Tour plugin.
 */

if ( ! function_exists( 'tour_generate_uuid' ) ) {
	/**
	 * Generate UUID v4
	 */
	function tour_generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}

if ( ! function_exists( 'tour_format_date' ) ) {
	/**
	 * Format date to DD.MM.YYYY
	 */
	function tour_format_date( $date ) {
		if ( empty( $date ) ) {
			return null;
		}
		$dt = new DateTime( $date );

		return $dt->format( 'd.m.Y' );
	}
}

if ( ! function_exists( 'tour_format_time' ) ) {
	/**
	 * Format time to HH:MM
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
	 * Get day name from number
	 */
	function tour_get_day_name( $day_num ) {
		$days = [ 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' ];

		return isset( $days[ $day_num ] ) ? $days[ $day_num ] : '';
	}
}

if ( ! function_exists( 'tour_get_type_name' ) ) {
	/**
	 * Get type name from number
	 */
	function tour_get_type_name( $type_num ) {
		$types = [ 'Auftritt', 'Infos', 'GV', 'Anderes' ];

		return isset( $types[ $type_num ] ) ? $types[ $type_num ] : '';
	}
}
