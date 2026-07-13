<?php
/**
 * Shared helpers for Trombongos Tour admin pages.
 */

/**
 * Output a single admin notice.
 *
 * @param string $type    WordPress notice type (success, error, warning, info).
 * @param string $message Notice message.
 */
function tour_admin_notice( $type, $message ) {
	echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . esc_html( $message ) . '</p></div>';
}

/**
 * Output multiple admin notices of the same type.
 *
 * @param string       $type     WordPress notice type.
 * @param string|array $messages One or more messages.
 */
function tour_admin_notices( $type, $messages ) {
	foreach ( (array) $messages as $message ) {
		tour_admin_notice( $type, $message );
	}
}

/**
 * Store a flash notice for the current user (redirect-safe).
 *
 * @param string $type    WordPress notice type.
 * @param string $message Notice message.
 */
function tour_admin_set_notice( $type, $message ) {
	set_transient(
		'tour_admin_notice_' . get_current_user_id(),
		array(
			'type'    => $type,
			'message' => $message,
		),
		60
	);
}

/**
 * Display and clear a flash notice for the current user.
 */
function tour_admin_show_notice() {
	$notice = get_transient( 'tour_admin_notice_' . get_current_user_id() );

	if ( ! $notice ) {
		return;
	}

	tour_admin_notice( $notice['type'], $notice['message'] );
	delete_transient( 'tour_admin_notice_' . get_current_user_id() );
}

/**
 * Redirect to an admin page with a flash notice.
 *
 * @param string $url     Redirect URL.
 * @param string $type    WordPress notice type.
 * @param string $message Notice message.
 */
function tour_admin_redirect_with_notice( $url, $type, $message ) {
	tour_admin_set_notice( $type, $message );
	wp_safe_redirect( $url );
	exit;
}

/**
 * Load a record for edit mode from GET parameters.
 *
 * @param string $table     Database table name.
 * @param string $id_column Primary key column name.
 * @return array|null
 */
function tour_admin_get_edit_record( $table, $id_column = 'id' ) {
	global $wpdb;

	if ( ! isset( $_GET['action'], $_GET['id'] ) || $_GET['action'] !== 'edit' ) {
		return null;
	}

	$edit_id = intval( $_GET['id'] );

	return $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE {$id_column} = %d", $edit_id ),
		ARRAY_A
	);
}

/**
 * Get the currently active season.
 *
 * @return array|null
 */
function tour_get_active_season() {
	global $wpdb;

	return $wpdb->get_row(
		'SELECT * FROM ' . TOUR_SEASONS . ' WHERE active = 1 LIMIT 1',
		ARRAY_A
	);
}

/**
 * Get all seasons ordered by start date descending.
 *
 * @return array
 */
function tour_get_all_seasons() {
	global $wpdb;

	return $wpdb->get_results(
		'SELECT * FROM ' . TOUR_SEASONS . ' ORDER BY start_date DESC',
		ARRAY_A
	);
}

/**
 * Resolve season filter from GET, defaulting to the active season.
 *
 * @return int Season ID, or 0 for all seasons.
 */
function tour_get_season_filter() {
	$active_season = tour_get_active_season();

	if ( isset( $_GET['filter_season'] ) ) {
		return intval( $_GET['filter_season'] );
	}

	return $active_season ? (int) $active_season['id'] : 0;
}

/**
 * Format a date as d.m.Y.
 *
 * @param string $date Date string.
 * @return string
 */
function tour_format_date( $date ) {
	return date( 'd.m.Y', strtotime( $date ) );
}

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

/**
 * Render a yes/no dashicon for boolean values.
 *
 * @param bool $value Boolean value.
 */
function tour_render_bool_icon( $value ) {
	if ( $value ) {
		echo '<span class="dashicons dashicons-yes tour-icon-yes"></span>';
	} else {
		echo '<span class="dashicons dashicons-no tour-icon-no"></span>';
	}
}

/**
 * Render season options for a select element.
 *
 * @param array $seasons       Season rows.
 * @param int   $selected_id   Selected season ID.
 * @param bool   $show_active   Append "(Aktiv)" label for active seasons.
 * @param string $empty_label   Optional empty option label.
 */
function tour_render_season_options( $seasons, $selected_id = 0, $show_active = true, $empty_label = '' ) {
	if ( $empty_label !== '' ) {
		echo '<option value="">' . esc_html( $empty_label ) . '</option>';
	}

	foreach ( $seasons as $season ) {
		$label = $season['name'];
		if ( $show_active && ! empty( $season['active'] ) ) {
			$label .= ' (Aktiv)';
		}

		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $season['id'] ),
			selected( (int) $selected_id, (int) $season['id'], false ),
			esc_html( $label )
		);
	}
}

/**
 * Render a season filter dropdown form.
 *
 * @param string $page          Admin page slug.
 * @param int    $filter_season Current filter value.
 * @param array  $seasons       Season rows.
 */
function tour_render_season_filter_form( $page, $filter_season, $seasons ) {
	?>
	<div class="tablenav top">
		<div class="alignleft actions">
			<form method="get" action="">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>">
				<select name="filter_season" id="filter_season">
					<option value="0"><?php echo esc_html( 'Alle Saisons' ); ?></option>
					<?php tour_render_season_options( $seasons, $filter_season ); ?>
				</select>
				<input type="submit" class="button" value="<?php echo esc_attr( 'Filtern' ); ?>">
			</form>
		</div>
	</div>
	<?php
}
