<?php
/**
 * Transport Management UI
 */

global $wpdb;

$action = tour_admin_verify_post_action( 'tour_transport_action', 'tour_transport_action' );

if ( $action === 'add' || $action === 'edit' ) {
	$name       = sanitize_text_field( wp_unslash( $_POST['transport_name'] ?? '' ) );
	$is_default = isset( $_POST['transport_default'] ) ? 1 : 0;

	if ( empty( $name ) ) {
		tour_admin_notice( 'error', 'Name ist erforderlich.' );
	} else {
		if ( $is_default ) {
			tour_admin_unset_exclusive_flag( TOUR_TRANSPORTS, 'default' );
		}

		$data = array(
			'name'    => $name,
			'default' => $is_default,
		);

		if ( $action === 'add' ) {
			$data['uuid'] = tour_generate_uuid();
			$result       = $wpdb->insert( TOUR_TRANSPORTS, $data, array( '%s', '%s', '%d' ) );
		} else {
			$id     = intval( $_POST['transport_id'] );
			$result = $wpdb->update( TOUR_TRANSPORTS, $data, array( 'id' => $id ), array( '%s', '%d' ), array( '%d' ) );
		}

		tour_admin_save_result_notice(
			$result,
			array(
				'add_success'  => 'Transport erfolgreich hinzugefügt.',
				'add_error'    => 'Fehler beim Hinzufügen des Transports.',
				'edit_success' => 'Transport erfolgreich aktualisiert.',
				'edit_error'   => 'Fehler beim Aktualisieren des Transports.',
			),
			$action === 'add'
		);
	}
} elseif ( $action === 'delete' ) {
	$id = intval( $_POST['transport_id'] );

	$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . TOUR_EVENTS . ' WHERE transport_id = %d', $id ) );

	if ( $count > 0 ) {
		tour_admin_notice( 'error', 'Transport kann nicht gelöscht werden, da er von ' . $count . ' Auftritt(en) verwendet wird.' );
	} else {
		$result = $wpdb->delete( TOUR_TRANSPORTS, array( 'id' => $id ), array( '%d' ) );

		if ( $result ) {
			tour_admin_notice( 'success', 'Transport erfolgreich gelöscht.' );
		} else {
			tour_admin_notice( 'error', 'Fehler beim Löschen des Transports.' );
		}
	}
}

$edit_transport = tour_admin_get_edit_record( TOUR_TRANSPORTS );
$transports     = $wpdb->get_results( 'SELECT * FROM ' . TOUR_TRANSPORTS . ' ORDER BY name ASC', ARRAY_A );

tour_admin_page_header( 'Transport Verwaltung', 'tour_transports' );
tour_admin_crud_layout_open( 'transport' );
tour_admin_crud_form_open( 'transport', $edit_transport ? 'Transport bearbeiten' : 'Neuer Transport' );
tour_admin_crud_form_begin(
	'tour_transport_action',
	'tour_transport_action',
	$edit_transport ? 'edit' : 'add',
	$edit_transport ? $edit_transport['id'] : null,
	'transport_id'
);
?>
<table class="form-table">
	<tr>
		<th scope="row"><label for="transport_name">Name *</label></th>
		<td>
			<input type="text" name="transport_name" id="transport_name" class="regular-text"
				   value="<?php echo $edit_transport ? esc_attr( $edit_transport['name'] ) : ''; ?>" required>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="transport_default">Standard</label></th>
		<td>
			<label>
				<input type="checkbox" name="transport_default" id="transport_default" value="1"
					<?php echo ( $edit_transport && $edit_transport['default'] ) ? 'checked' : ''; ?>>
				Als Standard-Transport festlegen
			</label>
			<p class="description">Wird automatisch bei neuen Auftritten vorausgewählt</p>
		</td>
	</tr>
</table>
<?php
tour_admin_crud_form_actions( 'tour_transports', (bool) $edit_transport );
tour_admin_crud_form_close();
tour_admin_crud_list_open( 'transport', 'Alle Transporte' );

if ( empty( $transports ) ) :
	?>
	<p>Keine Transporte gefunden. Fügen Sie einen neuen Transport hinzu.</p>
	<?php
else :
	?>
	<table class="wp-list-table widefat fixed striped tour-responsive-table">
		<thead>
		<tr>
			<th>Name</th>
			<th>Standard</th>
			<th>Erstellt</th>
			<th>Aktionen</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $transports as $transport ) : ?>
			<tr>
				<td data-colname="Name"><strong><?php echo esc_html( $transport['name'] ); ?></strong></td>
				<td data-colname="Standard"><?php tour_render_bool_icon( $transport['default'] ); ?></td>
				<td data-colname="Erstellt"><?php echo esc_html( date( 'd.m.Y H:i', strtotime( $transport['created_at'] ) ) ); ?></td>
				<td data-colname="Aktionen">
					<div class="tour-table-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=tour_transports&action=edit&id=' . $transport['id'] ) ); ?>"
						   class="button button-small">Bearbeiten</a>
						<?php
						tour_admin_delete_button(
							'tour_transport_action',
							'tour_transport_action',
							'delete',
							'transport_id',
							$transport['id'],
							'Sind Sie sicher, dass Sie diesen Transport löschen möchten?'
						);
						?>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
endif;

tour_admin_crud_layout_close();
