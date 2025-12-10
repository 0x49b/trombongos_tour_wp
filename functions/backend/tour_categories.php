<?php
/**
 * Category Management UI
 */

global $wpdb;

// Handle form submissions
if (isset($_POST['tour_category_action'])) {
    check_admin_referer('tour_category_action');

    $action = sanitize_text_field($_POST['tour_category_action']);

    if ($action === 'add' || $action === 'edit') {
        $title = sanitize_text_field($_POST['category_title']);
        $season_id = intval($_POST['season_id']);
        $date_start = sanitize_text_field($_POST['date_start']);
        $date_end = sanitize_text_field($_POST['date_end']);
        $public = isset($_POST['public']) ? 1 : 0;
        $sort = intval($_POST['sort']);

        // Validation
        $errors = array();

        if (empty($title)) {
            $errors[] = 'Titel ist erforderlich.';
        }

        if (empty($season_id)) {
            $errors[] = 'Saison ist erforderlich.';
        }

        if (empty($date_start)) {
            $errors[] = 'Startdatum ist erforderlich.';
        }

        if (empty($date_end)) {
            $errors[] = 'Enddatum ist erforderlich.';
        }

        if (!empty($date_start) && !empty($date_end) && strtotime($date_end) < strtotime($date_start)) {
            $errors[] = 'Enddatum darf nicht vor dem Startdatum liegen.';
        }

        if (empty($errors)) {
            if ($action === 'add') {
                $uuid = tour_generate_uuid();

                $result = $wpdb->insert(
                    TOUR_CATEGORIES,
                    array(
                        'uuid' => $uuid,
                        'title' => $title,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'public' => $public,
                        'sort' => $sort,
                        'season_id' => $season_id,
                    ),
                    array('%s', '%s', '%s', '%s', '%d', '%d', '%d')
                );

                if ($result) {
                    echo '<div class="notice notice-success"><p>Kategorie erfolgreich hinzugefügt.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Hinzufügen der Kategorie.</p></div>';
                }
            } else {
                $id = intval($_POST['category_id']);

                $result = $wpdb->update(
                    TOUR_CATEGORIES,
                    array(
                        'title' => $title,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'public' => $public,
                        'sort' => $sort,
                        'season_id' => $season_id,
                    ),
                    array('id' => $id),
                    array('%s', '%s', '%s', '%d', '%d', '%d'),
                    array('%d')
                );

                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>Kategorie erfolgreich aktualisiert.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Aktualisieren der Kategorie.</p></div>';
                }
            }
        } else {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['category_id']);

        // Check if category has events
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE category_id = %d",
            $id
        ));

        if ($count > 0) {
            echo '<div class="notice notice-error"><p>Kategorie kann nicht gelöscht werden, da sie ' . $count . ' Auftritt(e) enthält.</p></div>';
        } else {
            $result = $wpdb->delete(
                TOUR_CATEGORIES,
                array('id' => $id),
                array('%d')
            );

            if ($result) {
                echo '<div class="notice notice-success"><p>Kategorie erfolgreich gelöscht.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Fehler beim Löschen der Kategorie.</p></div>';
            }
        }
    } elseif ($action === 'copy') {
        $id = intval($_POST['category_id']);
        $target_season_id = intval($_POST['target_season_id']);

        if (empty($target_season_id)) {
            echo '<div class="notice notice-error"><p>Bitte wählen Sie eine Ziel-Saison aus.</p></div>';
        } else {
            // Get the category to copy
            $category = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM " . TOUR_CATEGORIES . " WHERE id = %d",
                $id
            ), ARRAY_A);

            if ($category) {
                // Create new category with new UUID and target season
                $result = $wpdb->insert(
                    TOUR_CATEGORIES,
                    array(
                        'uuid' => tour_generate_uuid(),
                        'title' => $category['title'],
                        'date_start' => $category['date_start'],
                        'date_end' => $category['date_end'],
                        'public' => $category['public'],
                        'sort' => $category['sort'],
                        'season_id' => $target_season_id,
                    ),
                    array('%s', '%s', '%s', '%s', '%d', '%d', '%d')
                );

                if ($result) {
                    echo '<div class="notice notice-success"><p>Kategorie erfolgreich in neue Saison kopiert.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Kopieren der Kategorie.</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>Kategorie nicht gefunden.</p></div>';
            }
        }
    } elseif ($action === 'bulk_copy') {
        if (!empty($_POST['category_ids']) && is_array($_POST['category_ids'])) {
            $target_season_id = intval($_POST['target_season_id']);

            if (empty($target_season_id)) {
                echo '<div class="notice notice-error"><p>Bitte wählen Sie eine Ziel-Saison aus.</p></div>';
            } else {
                $ids = array_map('intval', $_POST['category_ids']);
                $copied_count = 0;

                foreach ($ids as $id) {
                    // Get the category to copy
                    $category = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM " . TOUR_CATEGORIES . " WHERE id = %d",
                        $id
                    ), ARRAY_A);

                    if ($category) {
                        // Create new category with new UUID and target season
                        $result = $wpdb->insert(
                            TOUR_CATEGORIES,
                            array(
                                'uuid' => tour_generate_uuid(),
                                'title' => $category['title'],
                                'date_start' => $category['date_start'],
                                'date_end' => $category['date_end'],
                                'public' => $category['public'],
                                'sort' => $category['sort'],
                                'season_id' => $target_season_id,
                            ),
                            array('%s', '%s', '%s', '%s', '%d', '%d', '%d')
                        );

                        if ($result) {
                            $copied_count++;
                        }
                    }
                }

                if ($copied_count > 0) {
                    echo '<div class="notice notice-success"><p>' . $copied_count . ' Kategorie(n) erfolgreich in neue Saison kopiert.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Kopieren der Kategorien.</p></div>';
                }
            }
        }
    }
}

// Get category to edit if edit action
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_category = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . TOUR_CATEGORIES . " WHERE id = %d",
        $edit_id
    ), ARRAY_A);
}

// Get active season first
$active_season = $wpdb->get_row("SELECT * FROM " . TOUR_SEASONS . " WHERE active = 1 LIMIT 1", ARRAY_A);

// Get all seasons for dropdown
$seasons = $wpdb->get_results("SELECT * FROM " . TOUR_SEASONS . " ORDER BY start_date DESC", ARRAY_A);

// Get filter season - default to active season
$filter_season = isset($_GET['filter_season']) ? intval($_GET['filter_season']) : ($active_season ? $active_season['id'] : 0);

// Get categories with season info and event count
$query = "SELECT c.*,
          s.name as season_name,
          s.start_date as season_start_date,
          (SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE category_id = c.id) as event_count
          FROM " . TOUR_CATEGORIES . " c
          LEFT JOIN " . TOUR_SEASONS . " s ON c.season_id = s.id";

if ($filter_season > 0) {
    $query .= $wpdb->prepare(" WHERE c.season_id = %d", $filter_season);
}

$query .= " ORDER BY c.sort ASC";

$categories = $wpdb->get_results($query, ARRAY_A);

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Kategorien Verwaltung</h1>
    <a href="<?php echo admin_url('admin.php?page=tour_categories'); ?>" class="page-title-action">Neu hinzufügen</a>
    <hr class="wp-header-end">

    <!-- Filter -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="tour_categories">
                <select name="filter_season" id="filter_season">
                    <option value="0">Alle Saisons</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season['id']); ?>"
                                <?php selected($filter_season, $season['id']); ?>>
                            <?php echo esc_html($season['name']); ?>
                            <?php echo $season['active'] ? ' (Aktiv)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="Filtern">
            </form>
        </div>
    </div>

    <div class="tour-category-container">

        <!-- Form Section -->
        <div class="tour-category-form">
            <div class="postbox">
                <div class="postbox-header">
                    <h2><?php echo $edit_category ? 'Kategorie bearbeiten' : 'Neue Kategorie'; ?></h2>
                </div>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('tour_category_action'); ?>
                        <input type="hidden" name="tour_category_action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                        <?php if ($edit_category): ?>
                            <input type="hidden" name="category_id" value="<?php echo esc_attr($edit_category['id']); ?>">
                        <?php endif; ?>

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
                                           value="<?php echo $edit_category ? esc_attr($edit_category['title']) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="season_id">Saison *</label>
                                </th>
                                <td>
                                    <select name="season_id" id="season_id" required>
                                        <option value="">Bitte wählen...</option>
                                        <?php foreach ($seasons as $season): ?>
                                            <option value="<?php echo esc_attr($season['id']); ?>"
                                                <?php
                                                if ($edit_category) {
                                                    selected($edit_category['season_id'], $season['id']);
                                                } elseif ($active_season && $season['id'] == $active_season['id']) {
                                                    echo 'selected';
                                                }
                                                ?>>
                                                <?php echo esc_html($season['name']); ?>
                                                <?php echo $season['active'] ? ' (Aktiv)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                           value="<?php echo $edit_category ? esc_attr($edit_category['date_start']) : ''; ?>"
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
                                           value="<?php echo $edit_category ? esc_attr($edit_category['date_end']) : ''; ?>"
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
                                           value="<?php echo $edit_category ? esc_attr($edit_category['sort']) : '0'; ?>">
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
                                               <?php echo (!$edit_category || $edit_category['public']) ? 'checked' : ''; ?>>
                                        Auf öffentlicher Website anzeigen
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <input type="submit"
                                   name="submit"
                                   class="button button-primary"
                                   value="<?php echo $edit_category ? 'Aktualisieren' : 'Hinzufügen'; ?>">
                            <?php if ($edit_category): ?>
                                <a href="<?php echo admin_url('admin.php?page=tour_categories'); ?>"
                                   class="button">Abbrechen</a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Section -->
        <div class="tour-category-list">
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Alle Kategorien</h2>
                </div>
                <div class="inside">
                    <?php if (empty($categories)): ?>
                        <p>Keine Kategorien gefunden. Fügen Sie eine neue Kategorie hinzu.</p>
                    <?php else: ?>
                        <!-- Bulk Actions -->
                        <form method="post" id="categories-bulk-form">
                            <?php wp_nonce_field('tour_category_action'); ?>
                            <div class="tablenav top">
                                <div class="alignleft actions">
                                    <select name="bulk_action" id="bulk-action-selector">
                                        <option value="">Massenaktion</option>
                                        <option value="bulk_copy">In Saison kopieren</option>
                                    </select>
                                    <select name="target_season_id" id="target-season-selector">
                                        <option value="">Ziel-Saison wählen...</option>
                                        <?php foreach ($seasons as $season): ?>
                                            <option value="<?php echo esc_attr($season['id']); ?>">
                                                <?php echo esc_html($season['name']); ?>
                                                <?php echo $season['active'] ? ' (Aktiv)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="button" onclick="return applyBulkAction()">Anwenden</button>
                                </div>
                            </div>

                            <table class="wp-list-table widefat striped">
                                <thead>
                                    <tr>
                                        <td class="check-column"><input type="checkbox" id="cb-select-all"></td>
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
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <th class="check-column">
                                                <input type="checkbox" name="category_ids[]" value="<?php echo esc_attr($category['id']); ?>" class="category-checkbox">
                                            </th>
                                            <td><strong><?php echo esc_html($category['title']); ?></strong></td>
                                        <td><?php echo esc_html($category['season_name']); ?></td>
                                        <td>
                                            <?php
                                            echo date('d.m.Y', strtotime($category['date_start']));
                                            echo ' - ';
                                            echo date('d.m.Y', strtotime($category['date_end']));
                                            ?>
                                        </td>
                                        <td><?php echo esc_html($category['event_count']); ?></td>
                                        <td><?php echo esc_html($category['sort']); ?></td>
                                        <td>
                                            <?php if ($category['public']): ?>
                                                <span class="dashicons dashicons-yes" style="color: #00a32a;"></span>
                                            <?php else: ?>
                                                <span class="dashicons dashicons-no" style="color: #d63638;"></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=tour_categories&action=edit&id=' . $category['id']); ?>"
                                               class="button button-small">Bearbeiten</a>

                                            <button type="button" class="button button-small" onclick="showCopyModal(<?php echo $category['id']; ?>, '<?php echo esc_js($category['title']); ?>')">
                                                Kopieren
                                            </button>

                                            <form method="post" style="display: inline;"
                                                  onsubmit="return confirm('Sind Sie sicher, dass Sie diese Kategorie löschen möchten?<?php echo $category['event_count'] > 0 ? ' Sie enthält ' . $category['event_count'] . ' Auftritt(e)!' : ''; ?>');">
                                                <?php wp_nonce_field('tour_category_action'); ?>
                                                <input type="hidden" name="tour_category_action" value="delete">
                                                <input type="hidden" name="category_id" value="<?php echo esc_attr($category['id']); ?>">
                                                <input type="submit"
                                                       class="button button-small button-link-delete"
                                                       value="Löschen">
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </form>

                        <!-- Copy Modal -->
                        <div id="copy-category-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
                            <div style="background-color: white; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 500px; border-radius: 5px;">
                                <h2>Kategorie kopieren</h2>
                                <p>Kategorie "<strong id="copy-category-name"></strong>" in welche Saison kopieren?</p>
                                <form method="post" id="copy-category-form">
                                    <?php wp_nonce_field('tour_category_action'); ?>
                                    <input type="hidden" name="tour_category_action" value="copy">
                                    <input type="hidden" name="category_id" id="copy-category-id">
                                    <table class="form-table">
                                        <tr>
                                            <th><label for="copy-target-season">Ziel-Saison *</label></th>
                                            <td>
                                                <select name="target_season_id" id="copy-target-season" required style="width: 100%;">
                                                    <option value="">Bitte wählen...</option>
                                                    <?php foreach ($seasons as $season): ?>
                                                        <option value="<?php echo esc_attr($season['id']); ?>">
                                                            <?php echo esc_html($season['name']); ?>
                                                            <?php echo $season['active'] ? ' (Aktiv)' : ''; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    <p>
                                        <input type="submit" class="button button-primary" value="Kopieren">
                                        <button type="button" class="button" onclick="hideCopyModal()">Abbrechen</button>
                                    </p>
                                </form>
                            </div>
                        </div>

                        <script>
                        function showCopyModal(categoryId, categoryName) {
                            document.getElementById('copy-category-id').value = categoryId;
                            document.getElementById('copy-category-name').textContent = categoryName;
                            document.getElementById('copy-category-modal').style.display = 'block';
                        }

                        function hideCopyModal() {
                            document.getElementById('copy-category-modal').style.display = 'none';
                        }

                        // Close modal when clicking outside
                        window.onclick = function(event) {
                            const modal = document.getElementById('copy-category-modal');
                            if (event.target == modal) {
                                hideCopyModal();
                            }
                        }

                        // Bulk actions
                        document.getElementById('cb-select-all').addEventListener('change', function() {
                            const checkboxes = document.querySelectorAll('.category-checkbox');
                            checkboxes.forEach(cb => cb.checked = this.checked);
                        });

                        function applyBulkAction() {
                            const action = document.getElementById('bulk-action-selector').value;
                            if (!action) {
                                alert('Bitte wählen Sie eine Aktion aus.');
                                return false;
                            }

                            const targetSeason = document.getElementById('target-season-selector').value;
                            if (!targetSeason) {
                                alert('Bitte wählen Sie eine Ziel-Saison aus.');
                                return false;
                            }

                            const checked = document.querySelectorAll('.category-checkbox:checked');
                            if (checked.length === 0) {
                                alert('Bitte wählen Sie mindestens eine Kategorie aus.');
                                return false;
                            }

                            if (!confirm('Möchten Sie ' + checked.length + ' Kategorie(n) in die ausgewählte Saison kopieren?')) {
                                return false;
                            }

                            // Set the action
                            const form = document.getElementById('categories-bulk-form');
                            const actionInput = document.createElement('input');
                            actionInput.type = 'hidden';
                            actionInput.name = 'tour_category_action';
                            actionInput.value = action;
                            form.appendChild(actionInput);

                            return true;
                        }
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
