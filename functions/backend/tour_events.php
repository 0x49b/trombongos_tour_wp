<?php
/**
 * Events Management UI
 * Comprehensive CRUD for tour events
 */

global $wpdb;

// Handle form submissions
if (isset($_POST['tour_event_action'])) {
    check_admin_referer('tour_event_action');

    $action = sanitize_text_field($_POST['tour_event_action']);

    if ($action === 'add' || $action === 'edit') {
        // Collect and sanitize all form data
        $name = sanitize_text_field($_POST['event_name']);
        $category_id = intval($_POST['category_id']);
        $transport_id = !empty($_POST['transport_id']) ? intval($_POST['transport_id']) : null;
        $date = sanitize_text_field($_POST['event_date']);
        $day = intval($_POST['day']);
        $sort = intval($_POST['sort']);
        $type = intval($_POST['type']);
        $organizer = !empty($_POST['organizer']) ? sanitize_text_field($_POST['organizer']) : null;
        $location = !empty($_POST['location']) ? sanitize_text_field($_POST['location']) : null;
        $play = sanitize_text_field($_POST['play']);
        $gathering = !empty($_POST['gathering']) ? sanitize_text_field($_POST['gathering']) : null;
        $makeup = !empty($_POST['makeup']) ? sanitize_text_field($_POST['makeup']) : null;
        $warehouse = !empty($_POST['warehouse']) ? sanitize_text_field($_POST['warehouse']) : null;
        $sun = !empty($_POST['sun']) ? sanitize_text_field($_POST['sun']) : null;
        $meal = isset($_POST['meal']) ? 1 : 0;
        $drinks = isset($_POST['drinks']) ? 1 : 0;
        $trailer = !empty($_POST['trailer']) ? sanitize_text_field($_POST['trailer']) : null;
        $cert = !empty($_POST['cert']) && $_POST['cert'] !== '' ? intval($_POST['cert']) : null;
        $fix = isset($_POST['fix']) ? 1 : 0;
        $public = isset($_POST['public']) ? 1 : 0;
        $info = !empty($_POST['info']) ? sanitize_textarea_field($_POST['info']) : null;

        // Validation
        $errors = array();

        if (empty($name)) {
            $errors[] = 'Name ist erforderlich.';
        }

        if (empty($category_id)) {
            $errors[] = 'Kategorie ist erforderlich.';
        }

        if (empty($date)) {
            $errors[] = 'Datum ist erforderlich.';
        }

        if (empty($play)) {
            $errors[] = 'Auftrittszeit ist erforderlich.';
        }

        if (empty($errors)) {
            // Build data array with uuid first for add action
            if ($action === 'add') {
                $data = array(
                    'uuid' => tour_generate_uuid(),
                    'name' => $name,
                    'category_id' => $category_id,
                    'transport_id' => $transport_id,
                    'date' => $date,
                    'day' => $day,
                    'sort' => $sort,
                    'type' => $type,
                    'organizer' => $organizer,
                    'location' => $location,
                    'play' => $play,
                    'gathering' => $gathering,
                    'makeup' => $makeup,
                    'warehouse' => $warehouse,
                    'sun' => $sun,
                    'meal' => $meal,
                    'drinks' => $drinks,
                    'trailer' => $trailer,
                    'cert' => $cert,
                    'fix' => $fix,
                    'public' => $public,
                    'info' => $info,
                );

                $format = array('%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%s');
            } else {
                $data = array(
                    'name' => $name,
                    'category_id' => $category_id,
                    'transport_id' => $transport_id,
                    'date' => $date,
                    'day' => $day,
                    'sort' => $sort,
                    'type' => $type,
                    'organizer' => $organizer,
                    'location' => $location,
                    'play' => $play,
                    'gathering' => $gathering,
                    'makeup' => $makeup,
                    'warehouse' => $warehouse,
                    'sun' => $sun,
                    'meal' => $meal,
                    'drinks' => $drinks,
                    'trailer' => $trailer,
                    'cert' => $cert,
                    'fix' => $fix,
                    'public' => $public,
                    'info' => $info,
                );

                $format = array('%s', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%s');
            }

            if ($action === 'add') {

                $result = $wpdb->insert(TOUR_EVENTS, $data, $format);

                if ($result) {
                    echo '<div class="notice notice-success"><p>Auftritt erfolgreich hinzugefügt.</p></div>';
                    // Redirect to avoid form resubmission
                    echo '<script>window.location.href = "' . admin_url('admin.php?page=tour_events') . '";</script>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Hinzufügen des Auftritts.</p></div>';
                }
            } else {
                $id = intval($_POST['event_id']);
                $result = $wpdb->update(TOUR_EVENTS, $data, array('id' => $id), $format, array('%d'));

                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>Auftritt erfolgreich aktualisiert.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Aktualisieren des Auftritts.</p></div>';
                }
            }
        } else {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['event_id']);
        $result = $wpdb->delete(TOUR_EVENTS, array('id' => $id), array('%d'));

        if ($result) {
            echo '<div class="notice notice-success"><p>Auftritt erfolgreich gelöscht.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Fehler beim Löschen des Auftritts.</p></div>';
        }
    } elseif ($action === 'bulk_fix') {
        if (!empty($_POST['event_ids']) && is_array($_POST['event_ids'])) {
            $ids = array_map('intval', $_POST['event_ids']);
            $id_list = implode(',', $ids);
            $wpdb->query("UPDATE " . TOUR_EVENTS . " SET fix = 1 WHERE id IN ($id_list)");
            echo '<div class="notice notice-success"><p>' . count($ids) . ' Auftritt(e) als bestätigt markiert.</p></div>';
        }
    } elseif ($action === 'bulk_public') {
        if (!empty($_POST['event_ids']) && is_array($_POST['event_ids'])) {
            $ids = array_map('intval', $_POST['event_ids']);
            $id_list = implode(',', $ids);
            $wpdb->query("UPDATE " . TOUR_EVENTS . " SET public = 1 WHERE id IN ($id_list)");
            echo '<div class="notice notice-success"><p>' . count($ids) . ' Auftritt(e) als öffentlich markiert.</p></div>';
        }
    } elseif ($action === 'bulk_delete') {
        if (!empty($_POST['event_ids']) && is_array($_POST['event_ids'])) {
            $ids = array_map('intval', $_POST['event_ids']);
            $id_list = implode(',', $ids);
            $wpdb->query("UPDATE " . TOUR_EVENTS . " SET public = 1 WHERE id IN ($id_list)");
            echo '<div class="notice notice-success"><p>' . count($ids) . ' Auftritt(e) als öffentlich markiert.</p></div>';
        }
    } elseif ($action === 'bulk_delete') {
        if (!empty($_POST['event_ids']) && is_array($_POST['event_ids'])) {
            $ids = array_map('intval', $_POST['event_ids']);
            $id_list = implode(',', $ids);
            $wpdb->query("DELETE FROM " . TOUR_EVENTS . " WHERE id IN ($id_list)");
            echo '<div class="notice notice-success"><p>' . count($ids) . ' Auftritt(e) gelöscht.</p></div>';
        }
    }
}

// Get active season first
$active_season = $wpdb->get_row("SELECT * FROM " . TOUR_SEASONS . " WHERE active = 1 LIMIT 1", ARRAY_A);

// Get data for dropdowns
$seasons = $wpdb->get_results("SELECT * FROM " . TOUR_SEASONS . " ORDER BY start_date DESC", ARRAY_A);
$categories = $wpdb->get_results("SELECT c.*, s.name as season_name FROM " . TOUR_CATEGORIES . " c LEFT JOIN " . TOUR_SEASONS . " s ON c.season_id = s.id ORDER BY s.start_date DESC, c.sort ASC", ARRAY_A);
$transports = $wpdb->get_results("SELECT * FROM " . TOUR_TRANSPORTS . " ORDER BY name ASC", ARRAY_A);

// Day names
$days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
$types = ['Auftritt', 'Infos', 'GV', 'Anderes'];
$certs = ['2G+', '2G', '3G', '3G+'];

// Get event to edit if edit action
$edit_event = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_event = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . TOUR_EVENTS . " WHERE id = %d", $edit_id), ARRAY_A);
}

// Check if we're in add/edit mode
$form_mode = (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit'])) || $edit_event;

// Build filters for list view
$where_clauses = array();
// Default to active season if not set
$filter_season = isset($_GET['filter_season']) ? intval($_GET['filter_season']) : ($active_season ? $active_season['id'] : 0);
$filter_category = isset($_GET['filter_category']) ? intval($_GET['filter_category']) : 0;
$filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
$filter_search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

if ($filter_season > 0) {
    $where_clauses[] = "c.season_id = $filter_season";
}

if ($filter_category > 0) {
    $where_clauses[] = "e.category_id = $filter_category";
}

if ($filter_status === 'fix') {
    $where_clauses[] = "e.fix = 1";
} elseif ($filter_status === 'not_fix') {
    $where_clauses[] = "e.fix = 0";
} elseif ($filter_status === 'public') {
    $where_clauses[] = "e.public = 1";
} elseif ($filter_status === 'private') {
    $where_clauses[] = "e.public = 0";
}

if (!empty($filter_search)) {
    $search_term = '%' . $wpdb->esc_like($filter_search) . '%';
    $where_clauses[] = $wpdb->prepare("(e.name LIKE %s OR e.location LIKE %s OR e.organizer LIKE %s)", $search_term, $search_term, $search_term);
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get events for list view
if (!$form_mode) {
    $events_query = "SELECT e.*, c.title as category_title, t.name as transport_name, s.name as season_name
                     FROM " . TOUR_EVENTS . " e
                     LEFT JOIN " . TOUR_CATEGORIES . " c ON e.category_id = c.id
                     LEFT JOIN " . TOUR_TRANSPORTS . " t ON e.transport_id = t.id
                     LEFT JOIN " . TOUR_SEASONS . " s ON c.season_id = s.id
                     $where_sql
                     ORDER BY e.date ASC, e.sort ASC
                     LIMIT 100";

    $events = $wpdb->get_results($events_query, ARRAY_A);
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Auftritte Verwaltung</h1>
    <?php if (!$form_mode): ?>
        <a href="<?php echo admin_url('admin.php?page=tour_events&action=add'); ?>" class="page-title-action">Neu hinzufügen</a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <?php if ($form_mode): ?>
        <!-- Add/Edit Form -->
        <div class="tour-event-form">
            <h2><?php echo $edit_event ? 'Auftritt bearbeiten' : 'Neuer Auftritt'; ?></h2>

            <form method="post" action="" id="event-form">
                <?php wp_nonce_field('tour_event_action'); ?>
                <input type="hidden" name="tour_event_action" value="<?php echo $edit_event ? 'edit' : 'add'; ?>">
                <?php if ($edit_event): ?>
                    <input type="hidden" name="event_id" value="<?php echo esc_attr($edit_event['id']); ?>">
                <?php endif; ?>

                <div class="tour-event-form-columns">
                    <div class="tour-event-form-column">
                        <!-- Section 1: Basic Info -->
                        <div class="postbox">
                            <div class="postbox-header"><h2>Basis Informationen</h2></div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="event_name">Name *</label></th>
                                        <td><input type="text" name="event_name" id="event_name" class="regular-text" value="<?php echo $edit_event ? esc_attr($edit_event['name']) : ''; ?>" required></td>
                                    </tr>
                                    <tr>
                                        <th><label for="category_id">Kategorie *</label></th>
                                        <td>
                                            <select name="category_id" id="category_id" class="regular-text" required>
                                                <option value="">Bitte wählen...</option>
                                                <?php
                                                $current_season = '';
                                                foreach ($categories as $cat):
                                                    if ($cat['season_name'] !== $current_season) {
                                                        if ($current_season !== '') echo '</optgroup>';
                                                        echo '<optgroup label="' . esc_attr($cat['season_name']) . '">';
                                                        $current_season = $cat['season_name'];
                                                    }
                                                ?>
                                                    <option value="<?php echo esc_attr($cat['id']); ?>" <?php if ($edit_event) selected($edit_event['category_id'], $cat['id']); ?>>
                                                        <?php echo esc_html($cat['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if ($current_season !== '') echo '</optgroup>'; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="event_date">Datum *</label></th>
                                        <td>
                                            <input type="date"
                                            name="event_date"
                                            id="event_date"
                                            <?php if ($edit_event && !empty($edit_event['date'])): ?>
                                                value="<?php echo esc_attr($edit_event['date']); ?>"
                                            <?php endif; ?>
                                            required onchange="updateDayFromDate()">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="day">Wochentag *</label></th>
                                        <td>
                                            <select name="day" id="day" required>
                                                <?php foreach ($days as $idx => $day_name): ?>
                                                    <option value="<?php echo $idx; ?>" <?php if ($edit_event) selected($edit_event['day'], $idx); ?>>
                                                        <?php echo esc_html($day_name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="type">Typ *</label></th>
                                        <td>
                                            <select name="type" id="type" required>
                                                <?php foreach ($types as $idx => $type_name): ?>
                                                    <option value="<?php echo $idx; ?>" <?php if ($edit_event) selected($edit_event['type'], $idx); ?>>
                                                        <?php echo esc_html($type_name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="sort">Sortierung</label></th>
                                        <td>
                                            <input type="number" name="sort" id="sort" min="0" value="<?php echo $edit_event ? esc_attr($edit_event['sort']) : '0'; ?>">
                                            <p class="description">Für gleiche Tage - kleinere Zahlen zuerst</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Section 2: Location & Organizer -->
                        <div class="postbox">
                            <div class="postbox-header"><h2>Ort & Organisator</h2></div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="organizer">Organisator</label></th>
                                        <td><input type="text" name="organizer" id="organizer" class="regular-text" value="<?php echo $edit_event ? esc_attr($edit_event['organizer']) : ''; ?>"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="location">Ort</label></th>
                                        <td><input type="text" name="location" id="location" class="regular-text" value="<?php echo $edit_event ? esc_attr($edit_event['location']) : ''; ?>"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tour-event-form-column">
                        <!-- Section 3: Timing -->
                        <div class="postbox">
                            <div class="postbox-header"><h2>Zeiten</h2></div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="play">Auftrittszeit *</label></th>
                                        <td><input type="time" name="play" id="play" value="<?php echo $edit_event ? esc_attr($edit_event['play']) : ''; ?>" required></td>
                                    </tr>
                                    <tr>
                                        <th><label for="gathering">Besammlung</label></th>
                                        <td><input type="time" name="gathering" id="gathering" value="<?php echo $edit_event ? esc_attr($edit_event['gathering']) : ''; ?>"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="makeup">Schminken</label></th>
                                        <td><input type="time" name="makeup" id="makeup" value="<?php echo $edit_event ? esc_attr($edit_event['makeup']) : ''; ?>"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="warehouse">Magazin</label></th>
                                        <td><input type="time" name="warehouse" id="warehouse" value="<?php echo $edit_event ? esc_attr($edit_event['warehouse']) : ''; ?>"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="sun">Sonne</label></th>
                                        <td><input type="time" name="sun" id="sun" value="<?php echo $edit_event ? esc_attr($edit_event['sun']) : ''; ?>"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Section 4: Logistics -->
                        <div class="postbox">
                            <div class="postbox-header"><h2>Logistik</h2></div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="transport_id">Transport</label></th>
                                        <td>
                                            <select name="transport_id" id="transport_id">
                                                <option value="">Keine Auswahl</option>
                                                <?php foreach ($transports as $transport): ?>
                                                    <option value="<?php echo esc_attr($transport['id']); ?>" <?php if ($edit_event) selected($edit_event['transport_id'], $transport['id']); ?>>
                                                        <?php echo esc_html($transport['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="trailer">Anhänger</label></th>
                                        <td><input type="text" name="trailer" id="trailer" class="regular-text" placeholder="Wer bringt den Anhänger?" value="<?php echo $edit_event ? esc_attr($edit_event['trailer']) : ''; ?>"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="cert">COVID Zertifikat</label></th>
                                        <td>
                                            <select name="cert" id="cert">
                                                <option value="">Keine Anforderung</option>
                                                <?php foreach ($certs as $idx => $cert_name): ?>
                                                    <option value="<?php echo $idx; ?>" <?php if ($edit_event && $edit_event['cert'] !== null) selected($edit_event['cert'], $idx); ?>>
                                                        <?php echo esc_html($cert_name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Verpflegung</th>
                                        <td>
                                            <label><input type="checkbox" name="meal" value="1" <?php if ($edit_event) checked($edit_event['meal'], 1); ?>> Essen vorhanden</label><br>
                                            <label><input type="checkbox" name="drinks" value="1" <?php if ($edit_event) checked($edit_event['drinks'], 1); ?>> Getränke vorhanden</label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Section 5: Status & Notes -->
                        <div class="postbox">
                            <div class="postbox-header"><h2>Status & Notizen</h2></div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <label><input type="checkbox" name="fix" value="1" <?php if ($edit_event) checked($edit_event['fix'], 1); ?>> <strong>Bestätigt (Fix)</strong></label>
                                            <p class="description">Nur bestätigte Auftritte erscheinen in der API</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Sichtbarkeit</th>
                                        <td>
                                            <label><input type="checkbox" name="public" value="1" <?php if ($edit_event) checked($edit_event['public'], 1); ?>> <strong>Öffentlich</strong></label>
                                            <p class="description">Auf öffentlicher Website anzeigen</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="info">Zusätzliche Infos</label></th>
                                        <td>
                                            <textarea name="info" id="info" rows="4" class="large-text"><?php echo ($edit_event && $edit_event['info']) ? esc_textarea($edit_event['info']) : ''; ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary button-large" value="<?php echo $edit_event ? 'Auftritt aktualisieren' : 'Auftritt hinzufügen'; ?>">
                    <a href="<?php echo admin_url('admin.php?page=tour_events'); ?>" class="button button-large">Abbrechen</a>
                </p>
            </form>
        </div>

        <script>
        function updateDayFromDate() {
            const dateInput = document.getElementById('event_date');
            const daySelect = document.getElementById('day');
            if (dateInput.value) {
                const dateObj = new Date(dateInput.value + 'T00:00:00');
                // JavaScript: 0=Sunday, 1=Monday, etc.
                // Our system: 0=Monday, 6=Sunday
                let dayNum = dateObj.getDay();
                dayNum = (dayNum === 0) ? 6 : dayNum - 1;
                daySelect.value = dayNum;
            }
        }
        </script>

    <?php else: ?>
        <!-- List View -->

        <!-- Filters -->
        <div class="tablenav top">
            <form method="get" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="page" value="tour_events">

                <select name="filter_season">
                    <option value="0">Alle Saisons</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season['id']); ?>" <?php selected($filter_season, $season['id']); ?>>
                            <?php echo esc_html($season['name']); ?><?php echo $season['active'] ? ' (Aktiv)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="filter_category">
                    <option value="0">Alle Kategorien</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat['id']); ?>" <?php selected($filter_category, $cat['id']); ?>>
                            <?php echo esc_html($cat['season_name'] . ' - ' . $cat['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="filter_status">
                    <option value="">Alle Status</option>
                    <option value="fix" <?php selected($filter_status, 'fix'); ?>>Bestätigt</option>
                    <option value="not_fix" <?php selected($filter_status, 'not_fix'); ?>>Nicht bestätigt</option>
                    <option value="public" <?php selected($filter_status, 'public'); ?>>Öffentlich</option>
                    <option value="private" <?php selected($filter_status, 'private'); ?>>Privat</option>
                </select>

                <input type="search" name="s" placeholder="Suchen..." value="<?php echo esc_attr($filter_search); ?>">

                <input type="submit" class="button" value="Filtern">
                <a href="<?php echo admin_url('admin.php?page=tour_events'); ?>" class="button">Zurücksetzen</a>
            </form>
        </div>

        <!-- Events List -->
        <?php if (empty($events)): ?>
            <p>Keine Auftritte gefunden.</p>
        <?php else: ?>
            <form method="post" id="events-list-form">
                <?php wp_nonce_field('tour_event_action'); ?>

                <div class="tablenav top">
                    <div class="alignleft actions">
                        <select name="bulk_action" id="bulk-action-selector-top">
                            <option value="">Massenaktion</option>
                            <option value="bulk_fix">Als bestätigt markieren</option>
                            <option value="bulk_public">Als öffentlich markieren</option>
                            <option value="bulk_delete">Löschen</option>
                        </select>
                        <button type="submit" class="button" onclick="return applyBulkAction()">Anwenden</button>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="check-column"><input type="checkbox" id="cb-select-all"></td>
                            <th>Datum</th>
                            <th>Name</th>
                            <th>Kategorie</th>
                            <th>Ort</th>
                            <th>Auftrittszeit</th>
                            <th>Fix</th>
                            <th>Öffentlich</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" name="event_ids[]" value="<?php echo esc_attr($event['id']); ?>" class="event-checkbox">
                                </th>
                                <td><?php echo date('d.m.Y', strtotime($event['date'])); ?></td>
                                <td><strong><?php echo esc_html($event['name']); ?></strong></td>
                                <td><?php echo esc_html($event['category_title']); ?></td>
                                <td><?php echo esc_html($event['location']); ?></td>
                                <td><?php echo esc_html(date('H:i', strtotime($event['play']))); ?></td>
                                <td>
                                    <?php if ($event['fix']): ?>
                                        <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-marker" style="color: #dba617;"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($event['public']): ?>
                                        <span class="dashicons dashicons-visibility" style="color: #00a32a;"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-hidden" style="color: #999;"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=tour_events&action=edit&id=' . $event['id']); ?>" class="button button-small">Bearbeiten</a>

                                    <form method="post" style="display: inline;" onsubmit="return confirm('Auftritt wirklich löschen?');">
                                        <?php wp_nonce_field('tour_event_action'); ?>
                                        <input type="hidden" name="tour_event_action" value="delete">
                                        <input type="hidden" name="event_id" value="<?php echo esc_attr($event['id']); ?>">
                                        <input type="submit" class="button button-small button-link-delete" value="Löschen">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            <script>
            document.getElementById('cb-select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.event-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });

            function applyBulkAction() {
                const action = document.getElementById('bulk-action-selector-top').value;
                if (!action) {
                    alert('Bitte wählen Sie eine Aktion aus.');
                    return false;
                }

                const checked = document.querySelectorAll('.event-checkbox:checked');
                if (checked.length === 0) {
                    alert('Bitte wählen Sie mindestens einen Auftritt aus.');
                    return false;
                }

                if (action === 'bulk_delete') {
                    if (!confirm('Sind Sie sicher, dass Sie ' + checked.length + ' Auftritt(e) löschen möchten?')) {
                        return false;
                    }
                }

                // Set the action
                const form = document.getElementById('events-list-form');
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'tour_event_action';
                actionInput.value = action;
                form.appendChild(actionInput);

                return true;
            }
            </script>
        <?php endif; ?>

    <?php endif; ?>
</div>
