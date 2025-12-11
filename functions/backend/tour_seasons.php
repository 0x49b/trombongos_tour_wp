<?php
/**
 * Season Management UI
 */

global $wpdb;

// Handle form submissions
if ( isset( $_POST['tour_season_action'] ) ) {
    check_admin_referer( 'tour_season_action' );

    $action = sanitize_text_field( $_POST['tour_season_action'] );

    if ( $action === 'add' || $action === 'edit' ) {
        $name       = sanitize_text_field( $_POST['season_name'] );
        $start_date = sanitize_text_field( $_POST['start_date'] );
        $end_date   = sanitize_text_field( $_POST['end_date'] );
        $active     = isset( $_POST['active'] ) ? 1 : 0;

        // Validation
        $errors = array();

        if ( empty( $name ) ) {
            $errors[] = 'Name ist erforderlich.';
        } elseif ( ! preg_match( '/^\d{4}\/\d{4}$/', $name ) ) {
            $errors[] = 'Name muss im Format YYYY/YYYY sein (z.B. 2025/2026).';
        }

        if ( empty( $start_date ) ) {
            $errors[] = 'Startdatum ist erforderlich.';
        }

        if ( empty( $end_date ) ) {
            $errors[] = 'Enddatum ist erforderlich.';
        }

        if ( ! empty( $start_date ) && ! empty( $end_date ) && strtotime( $end_date ) <= strtotime( $start_date ) ) {
            $errors[] = 'Enddatum muss nach dem Startdatum liegen.';
        }

        if ( empty( $errors ) ) {
            // If activating this season, deactivate all others
            if ( $active ) {
                $wpdb->update(
                        TOUR_SEASONS,
                        array( 'active' => 0 ),
                        array( 'active' => 1 ),
                        array( '%d' ),
                        array( '%d' )
                );
            }

            if ( $action === 'add' ) {
                $uuid   = tour_generate_uuid();
                $result = $wpdb->insert(
                        TOUR_SEASONS,
                        array(
                                'uuid'       => $uuid,
                                'name'       => $name,
                                'start_date' => $start_date,
                                'end_date'   => $end_date,
                                'active'     => $active,
                        ),
                        array( '%s', '%s', '%s', '%s', '%d' )
                );

                if ( $result ) {
                    echo '<div class="notice notice-success"><p>Saison erfolgreich hinzugefügt.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Hinzufügen der Saison.</p></div>';
                }
            } else {
                $id     = intval( $_POST['season_id'] );
                $result = $wpdb->update(
                        TOUR_SEASONS,
                        array(
                                'name'       => $name,
                                'start_date' => $start_date,
                                'end_date'   => $end_date,
                                'active'     => $active,
                        ),
                        array( 'id' => $id ),
                        array( '%s', '%s', '%s', '%d' ),
                        array( '%d' )
                );

                if ( $result !== false ) {
                    echo '<div class="notice notice-success"><p>Saison erfolgreich aktualisiert.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Aktualisieren der Saison.</p></div>';
                }
            }
        } else {
            foreach ( $errors as $error ) {
                echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
            }
        }
    } elseif ( $action === 'delete' ) {
        $id = intval( $_POST['season_id'] );

        // Check if season is active
        $is_active = $wpdb->get_var( $wpdb->prepare(
                "SELECT active FROM " . TOUR_SEASONS . " WHERE id = %d",
                $id
        ) );

        if ( $is_active ) {
            echo '<div class="notice notice-error"><p>Aktive Saison kann nicht gelöscht werden.</p></div>';
        } else {
            // Check if season has categories
            $count = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM " . TOUR_CATEGORIES . " WHERE season_id = %d",
                    $id
            ) );

            if ( $count > 0 ) {
                echo '<div class="notice notice-error"><p>Saison kann nicht gelöscht werden, da sie ' . $count . ' Kategorie(n) enthält.</p></div>';
            } else {
                $result = $wpdb->delete(
                        TOUR_SEASONS,
                        array( 'id' => $id ),
                        array( '%d' )
                );

                if ( $result ) {
                    echo '<div class="notice notice-success"><p>Saison erfolgreich gelöscht.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Löschen der Saison.</p></div>';
                }
            }
        }
    } elseif ( $action === 'toggle_active' ) {
        $id = intval( $_POST['season_id'] );

        // Deactivate all seasons
        $wpdb->update(
                TOUR_SEASONS,
                array( 'active' => 0 ),
                array( 'active' => 1 ),
                array( '%d' ),
                array( '%d' )
        );

        // Activate this season
        $result = $wpdb->update(
                TOUR_SEASONS,
                array( 'active' => 1 ),
                array( 'id' => $id ),
                array( '%d' ),
                array( '%d' )
        );

        if ( $result !== false ) {
            echo '<div class="notice notice-success"><p>Saison aktiviert.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Fehler beim Aktivieren der Saison.</p></div>';
        }
    }
}

// Get season to edit if edit action
$edit_season = null;
if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) {
    $edit_id     = intval( $_GET['id'] );
    $edit_season = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . TOUR_SEASONS . " WHERE id = %d",
            $edit_id
    ), ARRAY_A );
}

// Get all seasons
$seasons = $wpdb->get_results( "SELECT * FROM " . TOUR_SEASONS . " ORDER BY start_date DESC", ARRAY_A );

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Saison Verwaltung</h1>
    <a href="<?php echo admin_url( 'admin.php?page=tour_seasons' ); ?>" class="page-title-action">Neu
        hinzufügen</a>
    <hr class="wp-header-end">

    <div class="tour-season-container">

        <!-- Form Section -->
        <div class="tour-season-form">
            <div class="postbox">
                <div class="postbox-header">
                    <h2><?php echo $edit_season ? 'Saison bearbeiten' : 'Neue Saison'; ?></h2>
                </div>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field( 'tour_season_action' ); ?>
                        <input type="hidden" name="tour_season_action"
                               value="<?php echo $edit_season ? 'edit' : 'add'; ?>">
                        <?php if ( $edit_season ): ?>
                            <input type="hidden" name="season_id"
                                   value="<?php echo esc_attr( $edit_season['id'] ); ?>">
                        <?php endif; ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="season_name">Name *</label>
                                </th>
                                <td>
                                    <input type="text"
                                           name="season_name"
                                           id="season_name"
                                           class="regular-text"
                                           placeholder="2025/2026"
                                           pattern="\d{4}/\d{4}"
                                           value="<?php echo $edit_season ? esc_attr( $edit_season['name'] ) : ''; ?>"
                                           required>
                                    <p class="description">Format: YYYY/YYYY (z.B. 2025/2026)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="start_date">Startdatum *</label>
                                </th>
                                <td>
                                    <input type="date"
                                           name="start_date"
                                           id="start_date"
                                           value="<?php echo $edit_season ? esc_attr( $edit_season['start_date'] ) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="end_date">Enddatum *</label>
                                </th>
                                <td>
                                    <input type="date"
                                           name="end_date"
                                           id="end_date"
                                           value="<?php echo $edit_season ? esc_attr( $edit_season['end_date'] ) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="active">Aktiv</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="active"
                                               id="active"
                                               value="1"
                                                <?php echo ( $edit_season && $edit_season['active'] ) ? 'checked' : ''; ?>>
                                        Diese Saison aktivieren
                                    </label>
                                    <p class="description" style="color: #d63638;">
                                        ⚠️ Nur eine Saison kann aktiv sein. Das Aktivieren dieser
                                        Saison deaktiviert alle anderen.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <input type="submit"
                                   name="submit"
                                   class="button button-primary"
                                   value="<?php echo $edit_season ? 'Aktualisieren' : 'Hinzufügen'; ?>">
                            <?php if ( $edit_season ): ?>
                                <a href="<?php echo admin_url( 'admin.php?page=tour_seasons' ); ?>"
                                   class="button">Abbrechen</a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Section -->
        <div class="tour-season-list">
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Alle Saisons</h2>
                </div>
                <div class="inside">
                    <?php if ( empty( $seasons ) ): ?>
                        <p>Keine Saisons gefunden. Fügen Sie eine neue Saison hinzu.</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Zeitraum</th>
                                <th>Aktiv</th>
                                <th>Aktionen</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $seasons as $season ): ?>
                                <tr <?php echo $season['active'] ? 'style="background-color: #f0f6fc;"' : ''; ?>>
                                    <td>
                                        <strong><?php echo esc_html( $season['name'] ); ?></strong>
                                        <?php if ( $season['active'] ): ?>
                                            <span class="dashicons dashicons-star-filled"
                                                  style="color: #f0b849;"
                                                  title="Aktive Saison"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo date( 'd.m.Y', strtotime( $season['start_date'] ) );
                                        echo ' - ';
                                        echo date( 'd.m.Y', strtotime( $season['end_date'] ) );
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ( $season['active'] ): ?>
                                            <span class="dashicons dashicons-yes-alt"
                                                  style="color: #00a32a;"></span>
                                        <?php else: ?>
                                            <form method="post" style="display: inline;">
                                                <?php wp_nonce_field( 'tour_season_action' ); ?>
                                                <input type="hidden" name="tour_season_action"
                                                       value="toggle_active">
                                                <input type="hidden" name="season_id"
                                                       value="<?php echo esc_attr( $season['id'] ); ?>">
                                                <button type="submit" class="button button-small"
                                                        title="Aktivieren">
                                                    Aktivieren
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url( 'admin.php?page=tour_seasons&action=edit&id=' . $season['id'] ); ?>"
                                           class="button button-small">Bearbeiten</a>

                                        <?php if ( ! $season['active'] ): ?>
                                            <form method="post" style="display: inline;"
                                                  onsubmit="return confirm('Sind Sie sicher, dass Sie diese Saison löschen möchten?');">
                                                <?php wp_nonce_field( 'tour_season_action' ); ?>
                                                <input type="hidden" name="tour_season_action"
                                                       value="delete">
                                                <input type="hidden" name="season_id"
                                                       value="<?php echo esc_attr( $season['id'] ); ?>">
                                                <input type="submit"
                                                       class="button button-small button-link-delete"
                                                       value="Löschen">
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
