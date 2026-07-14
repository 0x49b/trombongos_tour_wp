<?php
/**
 * Season Management UI
 */

global $wpdb;

$action = tour_admin_verify_post_action( 'tour_season_action', 'tour_season_action' );

if ( $action === 'add' || $action === 'edit' ) {
	$name       = sanitize_text_field( wp_unslash( $_POST['season_name'] ?? '' ) );
	$start_date = sanitize_text_field( wp_unslash( $_POST['start_date'] ?? '' ) );
	$end_date   = sanitize_text_field( wp_unslash( $_POST['end_date'] ?? '' ) );
	$active     = isset( $_POST['active'] ) ? 1 : 0;
	$errors     = tour_validate_date_range( $start_date, $end_date, true );

	if ( empty( $name ) ) {
		$errors[] = 'Name ist erforderlich.';
	} elseif ( ! preg_match( '/^\d{4}\/\d{4}$/', $name ) ) {
		$errors[] = 'Name muss im Format YYYY/YYYY sein (z.B. 2025/2026).';
	}

	if ( empty( $errors ) ) {
		if ( $active ) {
			tour_admin_unset_exclusive_flag( TOUR_SEASONS, 'active' );
		}

		$data = array(
			'name'       => $name,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'active'     => $active,
		);

		if ( $action === 'add' ) {
			$data['uuid'] = tour_generate_uuid();
			$result       = $wpdb->insert( TOUR_SEASONS, $data, array( '%s', '%s', '%s', '%s', '%d' ) );
		} else {
			$id     = intval( $_POST['season_id'] );
			$result = $wpdb->update( TOUR_SEASONS, $data, array( 'id' => $id ), array( '%s', '%s', '%s', '%d' ), array( '%d' ) );
		}

		tour_admin_save_result_notice(
			$result,
			array(
				'add_success'  => 'Saison erfolgreich hinzugefügt.',
				'add_error'    => 'Fehler beim Hinzufügen der Saison.',
				'edit_success' => 'Saison erfolgreich aktualisiert.',
				'edit_error'   => 'Fehler beim Aktualisieren der Saison.',
			),
			$action === 'add'
		);
	} else {
		tour_admin_notices( 'error', $errors );
	}
} elseif ( $action === 'delete' ) {
	$id = intval( $_POST['season_id'] );

	$is_active = $wpdb->get_var( $wpdb->prepare( 'SELECT active FROM ' . TOUR_SEASONS . ' WHERE id = %d', $id ) );

	if ( $is_active ) {
		tour_admin_notice( 'error', 'Aktive Saison kann nicht gelöscht werden.' );
	} else {
		$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . TOUR_CATEGORIES . ' WHERE season_id = %d', $id ) );

		if ( $count > 0 ) {
			tour_admin_notice( 'error', 'Saison kann nicht gelöscht werden, da sie ' . $count . ' Kategorie(n) enthält.' );
		} else {
			$result = $wpdb->delete( TOUR_SEASONS, array( 'id' => $id ), array( '%d' ) );

			if ( $result ) {
				tour_admin_notice( 'success', 'Saison erfolgreich gelöscht.' );
			} else {
				tour_admin_notice( 'error', 'Fehler beim Löschen der Saison.' );
			}
		}
	}
} elseif ( $action === 'toggle_active' ) {
	$id = intval( $_POST['season_id'] );

	tour_admin_unset_exclusive_flag( TOUR_SEASONS, 'active' );

	$result = $wpdb->update( TOUR_SEASONS, array( 'active' => 1 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );

	if ( $result !== false ) {
		tour_admin_notice( 'success', 'Saison aktiviert.' );
	} else {
		tour_admin_notice( 'error', 'Fehler beim Aktivieren der Saison.' );
	}
}

$edit_season = tour_admin_get_edit_record( TOUR_SEASONS );
$seasons     = tour_get_all_seasons();

tour_admin_page_header( 'Saison Verwaltung', 'tour_seasons' );
tour_admin_crud_layout_open( 'season' );
tour_admin_crud_form_open( 'season', $edit_season ? 'Saison bearbeiten' : 'Neue Saison' );
tour_admin_crud_form_begin(
	'tour_season_action',
	'tour_season_action',
	$edit_season ? 'edit' : 'add',
	$edit_season ? $edit_season['id'] : null,
	'season_id'
);
?>
<table class="form-table">
	<tr>
		<th scope="row"><label for="season_name">Name *</label></th>
		<td>
			<input type="text" name="season_name" id="season_name" class="regular-text" placeholder="2025/2026"
				   pattern="\d{4}/\d{4}" value="<?php echo $edit_season ? esc_attr( $edit_season['name'] ) : ''; ?>" required>
			<p class="description">Format: YYYY/YYYY (z.B. 2025/2026)</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="start_date">Startdatum *</label></th>
		<td>
			<input type="date" name="start_date" id="start_date"
				   value="<?php echo $edit_season ? esc_attr( $edit_season['start_date'] ) : ''; ?>" required>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="end_date">Enddatum *</label></th>
		<td>
			<input type="date" name="end_date" id="end_date"
				   value="<?php echo $edit_season ? esc_attr( $edit_season['end_date'] ) : ''; ?>" required>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="active">Aktiv</label></th>
		<td>
			<label>
				<input type="checkbox" name="active" id="active" value="1"
					<?php echo ( $edit_season && $edit_season['active'] ) ? 'checked' : ''; ?>>
				Diese Saison aktivieren
			</label>
			<p class="description" style="color: #d63638;">
				Nur eine Saison kann aktiv sein. Das Aktivieren dieser Saison deaktiviert alle anderen.
			</p>
		</td>
	</tr>
</table>
<?php
tour_admin_crud_form_actions( 'tour_seasons', (bool) $edit_season );
tour_admin_crud_form_close();
tour_admin_crud_list_open( 'season', 'Alle Saisons' );

if ( empty( $seasons ) ) :
	?>
	<p>Keine Saisons gefunden. Fügen Sie eine neue Saison hinzu.</p>
	<?php
else :
	?>
	<table class="wp-list-table widefat fixed striped tour-responsive-table">
		<thead>
		<tr>
			<th>Name</th>
			<th>Zeitraum</th>
			<th>Aktiv</th>
			<th>Aktionen</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $seasons as $season ) : ?>
			<tr class="<?php echo $season['active'] ? 'tour-season-active' : ''; ?>">
				<td data-colname="Name">
					<strong><?php echo esc_html( $season['name'] ); ?></strong>
					<?php if ( $season['active'] ) : ?>
						<span class="dashicons dashicons-star-filled tour-icon-star" title="Aktive Saison"></span>
					<?php endif; ?>
				</td>
				<td data-colname="Zeitraum"><?php echo esc_html( tour_format_date_range( $season['start_date'], $season['end_date'] ) ); ?></td>
				<td data-colname="Aktiv">
					<?php if ( $season['active'] ) : ?>
						<span class="dashicons dashicons-yes-alt tour-icon-yes"></span>
					<?php else : ?>
						<form method="post">
							<?php wp_nonce_field( 'tour_season_action' ); ?>
							<input type="hidden" name="tour_season_action" value="toggle_active">
							<input type="hidden" name="season_id" value="<?php echo esc_attr( $season['id'] ); ?>">
							<button type="submit" class="button button-small" title="Aktivieren">Aktivieren</button>
						</form>
					<?php endif; ?>
				</td>
				<td data-colname="Aktionen">
					<div class="tour-table-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=tour_seasons&action=edit&id=' . $season['id'] ) ); ?>"
						   class="button button-small">Bearbeiten</a>
						<?php if ( ! $season['active'] ) : ?>
							<?php
							tour_admin_delete_button(
								'tour_season_action',
								'tour_season_action',
								'delete',
								'season_id',
								$season['id'],
								'Sind Sie sicher, dass Sie diese Saison löschen möchten?'
							);
							?>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
endif;

tour_admin_crud_layout_close();
