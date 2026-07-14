<?php
/**
 * Category Management UI
 */

global $wpdb;

// Handle form submissions
$action = tour_admin_verify_post_action( 'tour_category_action', 'tour_category_action' );

if ( $action ) {
    if ( $action === 'add' || $action === 'edit' ) {
        $title      = sanitize_text_field( $_POST['category_title'] );
        $season_id  = intval( $_POST['season_id'] );
        $date_start = sanitize_text_field( $_POST['date_start'] );
        $date_end   = sanitize_text_field( $_POST['date_end'] );
        $public     = isset( $_POST['public'] ) ? 1 : 0;
        $sort       = intval( $_POST['sort'] );

        $errors = tour_validate_date_range( $date_start, $date_end );

        if ( empty( $title ) ) {
            $errors[] = 'Titel ist erforderlich.';
        }

        if ( empty( $season_id ) ) {
            $errors[] = 'Saison ist erforderlich.';
        }

        if ( empty( $errors ) ) {
            if ( $action === 'add' ) {
                $uuid = tour_generate_uuid();

                $result = $wpdb->insert(
                        TOUR_CATEGORIES,
                        array(
                                'uuid'       => $uuid,
                                'title'      => $title,
                                'date_start' => $date_start,
                                'date_end'   => $date_end,
                                'public'     => $public,
                                'sort'       => $sort,
                                'season_id'  => $season_id,
                        ),
                        array( '%s', '%s', '%s', '%s', '%d', '%d', '%d' )
                );

                tour_admin_save_result_notice(
                    $result,
                    array(
                        'add_success'  => 'Kategorie erfolgreich hinzugefügt.',
                        'add_error'    => 'Fehler beim Hinzufügen der Kategorie.',
                        'edit_success' => 'Kategorie erfolgreich aktualisiert.',
                        'edit_error'   => 'Fehler beim Aktualisieren der Kategorie.',
                    ),
                    true
                );
            } else {
                $id = intval( $_POST['category_id'] );

                $result = $wpdb->update(
                        TOUR_CATEGORIES,
                        array(
                                'title'      => $title,
                                'date_start' => $date_start,
                                'date_end'   => $date_end,
                                'public'     => $public,
                                'sort'       => $sort,
                                'season_id'  => $season_id,
                        ),
                        array( 'id' => $id ),
                        array( '%s', '%s', '%s', '%d', '%d', '%d' ),
                        array( '%d' )
                );

                tour_admin_save_result_notice(
                    $result,
                    array(
                        'add_success'  => 'Kategorie erfolgreich hinzugefügt.',
                        'add_error'    => 'Fehler beim Hinzufügen der Kategorie.',
                        'edit_success' => 'Kategorie erfolgreich aktualisiert.',
                        'edit_error'   => 'Fehler beim Aktualisieren der Kategorie.',
                    ),
                    false
                );
            }
        } else {
            tour_admin_notices( 'error', $errors );
        }
    } elseif ( $action === 'delete' ) {
        $id = intval( $_POST['category_id'] );

        // Check if category has events
        $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE category_id = %d",
                $id
        ) );

        if ( $count > 0 ) {
            tour_admin_notice( 'error', 'Kategorie kann nicht gelöscht werden, da sie ' . $count . ' Auftritt(e) enthält.' );
        } else {
            $result = $wpdb->delete(
                    TOUR_CATEGORIES,
                    array( 'id' => $id ),
                    array( '%d' )
            );

            if ( $result ) {
                tour_admin_notice( 'success', 'Kategorie erfolgreich gelöscht.' );
            } else {
                tour_admin_notice( 'error', 'Fehler beim Löschen der Kategorie.' );
            }
        }
    } elseif ( $action === 'copy' ) {
        $id               = intval( $_POST['category_id'] );
        $target_season_id = intval( $_POST['target_season_id'] );

        if ( empty( $target_season_id ) ) {
            tour_admin_notice( 'error', 'Bitte wählen Sie eine Ziel-Saison aus.' );
        } else {
            // Get the category to copy
            $category = $wpdb->get_row( $wpdb->prepare(
                    "SELECT * FROM " . TOUR_CATEGORIES . " WHERE id = %d",
                    $id
            ), ARRAY_A );

            if ( $category ) {
                // Create new category with new UUID and target season
                $result = $wpdb->insert(
                        TOUR_CATEGORIES,
                        array(
                                'uuid'       => tour_generate_uuid(),
                                'title'      => $category['title'],
                                'date_start' => $category['date_start'],
                                'date_end'   => $category['date_end'],
                                'public'     => $category['public'],
                                'sort'       => $category['sort'],
                                'season_id'  => $target_season_id,
                        ),
                        array( '%s', '%s', '%s', '%s', '%d', '%d', '%d' )
                );

                if ( $result ) {
                    tour_admin_notice( 'success', 'Kategorie erfolgreich in neue Saison kopiert.' );
                } else {
                    tour_admin_notice( 'error', 'Fehler beim Kopieren der Kategorie.' );
                }
            } else {
                tour_admin_notice( 'error', 'Kategorie nicht gefunden.' );
            }
        }
    } elseif ( $action === 'bulk_copy' ) {
        if ( ! empty( $_POST['category_ids'] ) && is_array( $_POST['category_ids'] ) ) {
            $target_season_id = intval( $_POST['target_season_id'] );

            if ( empty( $target_season_id ) ) {
                tour_admin_notice( 'error', 'Bitte wählen Sie eine Ziel-Saison aus.' );
            } else {
                $ids          = array_map( 'intval', $_POST['category_ids'] );
                $copied_count = 0;

                foreach ( $ids as $id ) {
                    // Get the category to copy
                    $category = $wpdb->get_row( $wpdb->prepare(
                            "SELECT * FROM " . TOUR_CATEGORIES . " WHERE id = %d",
                            $id
                    ), ARRAY_A );

                    if ( $category ) {
                        // Create new category with new UUID and target season
                        $result = $wpdb->insert(
                                TOUR_CATEGORIES,
                                array(
                                        'uuid'       => tour_generate_uuid(),
                                        'title'      => $category['title'],
                                        'date_start' => $category['date_start'],
                                        'date_end'   => $category['date_end'],
                                        'public'     => $category['public'],
                                        'sort'       => $category['sort'],
                                        'season_id'  => $target_season_id,
                                ),
                                array( '%s', '%s', '%s', '%s', '%d', '%d', '%d' )
                        );

                        if ( $result ) {
                            $copied_count ++;
                        }
                    }
                }

                if ( $copied_count > 0 ) {
                    tour_admin_notice( 'success', $copied_count . ' Kategorie(n) erfolgreich in neue Saison kopiert.' );
                } else {
                    tour_admin_notice( 'error', 'Fehler beim Kopieren der Kategorien.' );
                }
            }
        }
    } elseif ( $action === 'bulk_delete' ) {
	    if ( ! empty( $_POST['category_ids'] ) && is_array( $_POST['category_ids'] ) ) {
		    $ids = array_map( 'intval', $_POST['category_ids'] );
		    $deleted_count = 0;
		    $not_deleted_count = 0;
		    $not_deleted_names = [];

		    foreach ( $ids as $id ) {
			    // Check if category has events
			    $event_count = $wpdb->get_var( $wpdb->prepare(
				    "SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE category_id = %d",
				    $id
			    ) );

			    if ( $event_count == 0 ) {
				    $result = $wpdb->delete(
					    TOUR_CATEGORIES,
					    array( 'id' => $id ),
					    array( '%d' )
				    );
				    if ($result) {
					    $deleted_count++;
				    }
			    } else {
				    $not_deleted_count++;
				    $category_name = $wpdb->get_var( $wpdb->prepare("SELECT title FROM " . TOUR_CATEGORIES . " WHERE id = %d", $id) );
				    $not_deleted_names[] = $category_name . ' (' . $event_count . ' Auftritte)';
			    }
		    }

		    if ( $deleted_count > 0 ) {
			    tour_admin_notice( 'success', $deleted_count . ' leere Kategorie(n) erfolgreich gelöscht.' );
		    }

		    if ( $not_deleted_count > 0 ) {
			    tour_admin_notice( 'warning', $not_deleted_count . ' Kategorie(n) konnten nicht gelöscht werden, da sie noch Auftritte enthalten: ' . implode( ', ', $not_deleted_names ) . '.' );
		    }

		    if ($deleted_count == 0 && $not_deleted_count == 0) {
			    tour_admin_notice( 'info', 'Keine Kategorien zum Löschen ausgewählt.' );
		    }
	    }
    }
}

// Get category to edit if edit action
$edit_category = tour_admin_get_edit_record( TOUR_CATEGORIES );

// Get active season first
$active_season = tour_get_active_season();

// Get all seasons for dropdown
$seasons = tour_get_all_seasons();

// Get filter season - default to active season
$filter_season = tour_get_season_filter();

// Get categories with season info and event count
$query = "SELECT c.*,
          s.name as season_name,
          s.start_date as season_start_date,
          (SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE category_id = c.id) as event_count
          FROM " . TOUR_CATEGORIES . " c
          LEFT JOIN " . TOUR_SEASONS . " s ON c.season_id = s.id";

if ( $filter_season > 0 ) {
    $query .= $wpdb->prepare( " WHERE c.season_id = %d", $filter_season );
}

$query .= " ORDER BY c.sort ASC";

$categories = $wpdb->get_results( $query, ARRAY_A );

?>

<?php
tour_admin_page_header( 'Kategorien Verwaltung', 'tour_categories' );
tour_render_season_filter_form( 'tour_categories', $filter_season, $seasons );
tour_admin_crud_layout_open( 'category' );
tour_admin_crud_form_open( 'category', $edit_category ? 'Kategorie bearbeiten' : 'Neue Kategorie' );
tour_admin_crud_form_begin(
    'tour_category_action',
    'tour_category_action',
    $edit_category ? 'edit' : 'add',
    $edit_category ? $edit_category['id'] : null,
    'category_id'
);
?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="category_title">Titel *</label>
                                </th>
                                <td>
                                    <input type="text"
                                           name="category_title"
                                           id="category_title"
                                           class="regular-text"
                                           placeholder="z.B. Ulaladoga"
                                           value="<?php echo $edit_category ? esc_attr( $edit_category['title'] ) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="season_id">Saison *</label>
                                </th>
                                <td>
                                    <select name="season_id" id="season_id" required>
                                        <?php tour_render_season_options( $seasons, $edit_category ? $edit_category['season_id'] : ( $active_season ? $active_season['id'] : 0 ), true, 'Bitte wählen...' ); ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="date_start">Startdatum *</label>
                                </th>
                                <td>
                                    <input type="date"
                                           name="date_start"
                                           id="date_start"
                                           value="<?php echo $edit_category ? esc_attr( $edit_category['date_start'] ) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="date_end">Enddatum *</label>
                                </th>
                                <td>
                                    <input type="date"
                                           name="date_end"
                                           id="date_end"
                                           value="<?php echo $edit_category ? esc_attr( $edit_category['date_end'] ) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="sort">Sortierung</label>
                                </th>
                                <td>
                                    <input type="number"
                                           name="sort"
                                           id="sort"
                                           min="0"
                                           value="<?php echo $edit_category ? esc_attr( $edit_category['sort'] ) : '0'; ?>">
                                    <p class="description">Kleinere Zahlen erscheinen zuerst</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="public">Öffentlich</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="public"
                                               id="public"
                                               value="1"
                                                <?php echo ( ! $edit_category || $edit_category['public'] ) ? 'checked' : ''; ?>>
                                        Auf öffentlicher Website anzeigen
                                    </label>
                                </td>
                            </tr>
                        </table>

<?php
tour_admin_crud_form_actions( 'tour_categories', (bool) $edit_category );
tour_admin_crud_form_close();
tour_admin_crud_list_open( 'category', 'Alle Kategorien' );
?>
                    <?php if ( empty( $categories ) ): ?>
                        <p>Keine Kategorien gefunden. Fügen Sie eine neue Kategorie hinzu.</p>
                    <?php else: ?>
                        <!-- Bulk Actions -->
                        <form method="post" id="categories-bulk-form">
                            <?php wp_nonce_field( 'tour_category_action' ); ?>
                            <div class="tablenav top">
                                <div class="alignleft actions">
                                    <select name="bulk_action" id="bulk-action-selector">
                                        <option value="">Massenaktion</option>
                                        <option value="bulk_copy">In Saison kopieren</option>
                                        <option value="bulk_delete">Löschen</option>
                                    </select>
                                    <select name="target_season_id" id="target-season-selector">
                                        <?php tour_render_season_options( $seasons, 0, true, 'Ziel-Saison wählen...' ); ?>
                                    </select>
                                    <button type="submit" class="button" data-tour-bulk-apply>Anwenden</button>
                                </div>
                            </div>

                            <table class="wp-list-table widefat striped tour-responsive-table">
                                <thead>
                                <tr>
                                    <td class="check-column"><input type="checkbox"
                                                                    id="cb-select-all"
                                                                    data-tour-select-all="category-checkbox"></td>
                                    <th>Titel</th>
                                    <th>Saison</th>
                                    <th>Zeitraum</th>
                                    <th>Auftritte</th>
                                    <th>Sort</th>
                                    <th>Öffentlich</th>
                                    <th>Aktionen</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ( $categories as $category ): ?>
                                    <tr>
                                        <th class="check-column" data-colname="Auswahl">
                                            <input type="checkbox" name="category_ids[]"
                                                   value="<?php echo esc_attr( $category['id'] ); ?>"
                                                   class="category-checkbox">
                                        </th>
                                        <td data-colname="Titel">
                                            <strong>
                                                <a href="<?php echo admin_url( 'admin.php?page=tour_categories&action=edit&id=' . $category['id'] ); ?>">
                                                    <?php echo esc_html( $category['title'] ); ?>
                                                </a>
                                            </strong>
                                        </td>
                                        <td data-colname="Saison"><?php echo esc_html( $category['season_name'] ); ?></td>
                                        <td data-colname="Zeitraum"><?php echo esc_html( tour_format_date_range( $category['date_start'], $category['date_end'] ) ); ?></td>
                                        <td data-colname="Auftritte"><?php echo esc_html( $category['event_count'] ); ?></td>
                                        <td data-colname="Sort"><?php echo esc_html( $category['sort'] ); ?></td>
                                        <td data-colname="Öffentlich"><?php tour_render_bool_icon( $category['public'] ); ?></td>
                                        <td data-colname="Aktionen">
                                            <div class="tour-table-actions">
                                                <button type="button" class="button button-small"
                                                        data-tour-copy-category
                                                        data-id="<?php echo esc_attr( $category['id'] ); ?>"
                                                        data-name="<?php echo esc_attr( $category['title'] ); ?>">
                                                    Kopieren
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </form>

                        <!-- Copy Modal -->
                        <div id="copy-category-modal" class="tour-modal-overlay">
                            <div class="tour-modal-dialog">
                                <h2>Kategorie kopieren</h2>
                                <p>Kategorie "<strong id="copy-category-name"></strong>" in welche
                                    Saison kopieren?</p>
                                <form method="post" id="copy-category-form">
                                    <?php wp_nonce_field( 'tour_category_action' ); ?>
                                    <input type="hidden" name="tour_category_action" value="copy">
                                    <input type="hidden" name="category_id" id="copy-category-id">
                                    <table class="form-table">
                                        <tr>
                                            <th><label for="copy-target-season">Ziel-Saison
                                                    *</label></th>
                                            <td>
                                                <select name="target_season_id"
                                                        id="copy-target-season" required
                                                        style="width: 100%;">
                                                    <?php tour_render_season_options( $seasons, 0, true, 'Bitte wählen...' ); ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    <p>
                                        <input type="submit" class="button button-primary"
                                               value="Kopieren">
                                        <button type="button" class="button" data-tour-modal-close>Abbrechen</button>
                                    </p>
                                </form>
                            </div>
                        </div>

                    <?php endif; ?>
<?php
tour_admin_crud_layout_close();
