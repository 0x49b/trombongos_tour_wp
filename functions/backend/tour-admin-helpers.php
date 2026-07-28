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

/**
 * Verify a posted admin action and nonce.
 *
 * @param string $field_name   POST field containing the action.
 * @param string $nonce_action Nonce action name.
 */
function tour_admin_verify_post_action( $field_name, $nonce_action ) {
	if ( ! isset( $_POST[ $field_name ] ) ) {
		return null;
	}

	check_admin_referer( $nonce_action );

	return sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
}

/**
 * Show a notice based on a database write result.
 *
 * @param mixed $result  Database result.
 * @param array $messages Message map with add_success, add_error, edit_success, edit_error keys.
 * @param bool  $is_add  Whether this was an insert.
 */
function tour_admin_save_result_notice( $result, $messages, $is_add ) {
	if ( $is_add ) {
		tour_admin_notice( $result ? 'success' : 'error', $result ? $messages['add_success'] : $messages['add_error'] );
		return;
	}

	tour_admin_notice( $result !== false ? 'success' : 'error', $result !== false ? $messages['edit_success'] : $messages['edit_error'] );
}

/**
 * Unset an exclusive flag on all rows (e.g. active season, default transport).
 *
 * @param string $table  Database table.
 * @param string $column Column name.
 */
function tour_admin_unset_exclusive_flag( $table, $column ) {
	global $wpdb;

	$wpdb->update(
		$table,
		array( $column => 0 ),
		array( $column => 1 ),
		array( '%d' ),
		array( '%d' )
	);
}

/**
 * Validate a required date range.
 *
 * @param bool   $end_must_be_after Whether end date must be strictly after start.
 * @return array List of validation errors.
 */
function tour_validate_date_range( $start, $end, $end_must_be_after = false ) {
	$errors = array();

	if ( empty( $start ) ) {
		$errors[] = 'Startdatum ist erforderlich.';
	}

	if ( empty( $end ) ) {
		$errors[] = 'Enddatum ist erforderlich.';
	}

	if ( ! empty( $start ) && ! empty( $end ) ) {
		$start_time = strtotime( $start );
		$end_time   = strtotime( $end );

		if ( $end_must_be_after && $end_time <= $start_time ) {
			$errors[] = 'Enddatum muss nach dem Startdatum liegen.';
		} elseif ( ! $end_must_be_after && $end_time < $start_time ) {
			$errors[] = 'Enddatum darf nicht vor dem Startdatum liegen.';
		}
	}

	return $errors;
}

/**
 * Render the standard admin page header.
 *
 * @param string $title     Page title.
 * @param string $page_slug Admin page slug.
 */
function tour_admin_page_header( $title, $page_slug ) {
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php echo esc_html( $title ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug ) ); ?>" class="page-title-action">
			<?php echo esc_html( 'Neu hinzufügen' ); ?>
		</a>
		<hr class="wp-header-end">
	<?php
}

/**
 * Open a side-by-side CRUD layout container.
 *
 * @param string $entity Entity slug used in CSS classes (category, season, transport).
 */
function tour_admin_crud_layout_open( $entity ) {
	echo '<div class="tour-' . esc_attr( $entity ) . '-container">';
}

/**
 * Open the form column in a CRUD layout.
 *
 * @param string $entity Entity slug.
 * @param string $title  Postbox title.
 */
function tour_admin_crud_form_open( $entity, $title ) {
	?>
	<div class="tour-<?php echo esc_attr( $entity ); ?>-form">
		<div class="postbox">
			<div class="postbox-header">
				<h2><?php echo esc_html( $title ); ?></h2>
			</div>
			<div class="inside">
	<?php
}

/**
 * Begin a CRUD form with nonce and action fields.
 *
 * @param string   $nonce_action  Nonce action.
 * @param string   $action_field  Hidden action field name.
 * @param string   $action_value  Action value (add/edit).
 * @param int|null $record_id     Existing record ID for edit mode.
 * @param string   $id_field_name Hidden ID field name.
 */
function tour_admin_crud_form_begin( $nonce_action, $action_field, $action_value, $record_id = null, $id_field_name = 'id' ) {
	?>
	<form method="post" action="">
		<?php wp_nonce_field( $nonce_action ); ?>
		<input type="hidden" name="<?php echo esc_attr( $action_field ); ?>" value="<?php echo esc_attr( $action_value ); ?>">
		<?php if ( $record_id ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $id_field_name ); ?>" value="<?php echo esc_attr( $record_id ); ?>">
		<?php endif; ?>
	<?php
}

/**
 * Render standard submit/cancel buttons for CRUD forms.
 *
 * @param string $page_slug  Admin page slug.
 * @param bool   $is_edit    Whether editing an existing record.
 * @param string $add_label  Submit label for add mode.
 * @param string $edit_label Submit label for edit mode.
 */
function tour_admin_crud_form_actions( $page_slug, $is_edit, $add_label = 'Hinzufügen', $edit_label = 'Aktualisieren' ) {
	?>
	<p class="submit tour-submit-row">
		<input type="submit" name="submit" class="button button-primary" value="<?php echo esc_attr( $is_edit ? $edit_label : $add_label ); ?>">
		<?php if ( $is_edit ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug ) ); ?>" class="button"><?php echo esc_html( 'Abbrechen' ); ?></a>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Close a CRUD form and its form column section.
 */
function tour_admin_crud_form_close() {
	?>
			</form>
		</div>
	</div>
</div>
	<?php
}

/**
 * Open the list column in a CRUD layout.
 *
 * @param string $entity Entity slug.
 * @param string $title  Postbox title.
 */
function tour_admin_crud_list_open( $entity, $title ) {
	?>
	<div class="tour-<?php echo esc_attr( $entity ); ?>-list">
		<div class="postbox">
			<div class="postbox-header">
				<h2><?php echo esc_html( $title ); ?></h2>
			</div>
			<div class="inside">
	<?php
}

/**
 * Close the list column and CRUD layout.
 */
function tour_admin_crud_layout_close() {
	?>
			</div>
		</div>
	</div>
</div>
</div>
	<?php
}

/**
 * Render a delete form with confirmation.
 *
 * @param string $nonce_action Nonce action.
 * @param string $action_field Hidden action field name.
 * @param string $action_value Action value (usually delete).
 * @param string $id_field     Hidden ID field name.
 * @param int    $record_id    Record ID.
 * @param string $confirm      Confirmation message.
 * @param string $label        Button label.
 */
function tour_admin_delete_button( $nonce_action, $action_field, $action_value, $id_field, $record_id, $confirm, $label = 'Löschen' ) {
	?>
	<form method="post" data-tour-confirm="<?php echo esc_attr( $confirm ); ?>">
		<?php wp_nonce_field( $nonce_action ); ?>
		<input type="hidden" name="<?php echo esc_attr( $action_field ); ?>" value="<?php echo esc_attr( $action_value ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $id_field ); ?>" value="<?php echo esc_attr( $record_id ); ?>">
		<input type="submit" class="button button-small button-link-delete" value="<?php echo esc_attr( $label ); ?>">
	</form>
	<?php
}
