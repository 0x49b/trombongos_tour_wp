<?php
/**
 * Transport Management UI
 */

global $wpdb;

// Handle form submissions
if (isset($_POST['tour_transport_action'])) {
    check_admin_referer('tour_transport_action');

    $action = sanitize_text_field($_POST['tour_transport_action']);

    if ($action === 'add' || $action === 'edit') {
        $name = sanitize_text_field($_POST['transport_name']);

        if (empty($name)) {
            echo '<div class="notice notice-error"><p>Name ist erforderlich.</p></div>';
        } else {
            if ($action === 'add') {
                $uuid = tour_generate_uuid();
                $result = $wpdb->insert(
                    TOUR_TRANSPORTS,
                    array(
                        'uuid' => $uuid,
                        'name' => $name,
                    ),
                    array('%s', '%s')
                );

                if ($result) {
                    echo '<div class="notice notice-success"><p>Transport erfolgreich hinzugefügt.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Hinzufügen des Transports.</p></div>';
                }
            } else {
                $id = intval($_POST['transport_id']);
                $result = $wpdb->update(
                    TOUR_TRANSPORTS,
                    array('name' => $name),
                    array('id' => $id),
                    array('%s'),
                    array('%d')
                );

                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>Transport erfolgreich aktualisiert.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Fehler beim Aktualisieren des Transports.</p></div>';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['transport_id']);

        // Check if transport is used by any events
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE transport_id = %d",
            $id
        ));

        if ($count > 0) {
            echo '<div class="notice notice-error"><p>Transport kann nicht gelöscht werden, da er von ' . $count . ' Auftritt(en) verwendet wird.</p></div>';
        } else {
            $result = $wpdb->delete(
                TOUR_TRANSPORTS,
                array('id' => $id),
                array('%d')
            );

            if ($result) {
                echo '<div class="notice notice-success"><p>Transport erfolgreich gelöscht.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Fehler beim Löschen des Transports.</p></div>';
            }
        }
    }
}

// Get transport to edit if edit action
$edit_transport = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_transport = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . TOUR_TRANSPORTS . " WHERE id = %d",
        $edit_id
    ), ARRAY_A);
}

// Get all transports
$transports = $wpdb->get_results("SELECT * FROM " . TOUR_TRANSPORTS . " ORDER BY name ASC", ARRAY_A);

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Transport Verwaltung</h1>
    <a href="<?php echo admin_url('admin.php?page=tour_transports'); ?>" class="page-title-action">Neu hinzufügen</a>
    <hr class="wp-header-end">

    <div class="tour-transport-container">

        <!-- Form Section -->
        <div class="tour-transport-form">
            <div class="postbox">
                <div class="postbox-header">
                    <h2><?php echo $edit_transport ? 'Transport bearbeiten' : 'Neuer Transport'; ?></h2>
                </div>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('tour_transport_action'); ?>
                        <input type="hidden" name="tour_transport_action" value="<?php echo $edit_transport ? 'edit' : 'add'; ?>">
                        <?php if ($edit_transport): ?>
                            <input type="hidden" name="transport_id" value="<?php echo esc_attr($edit_transport['id']); ?>">
                        <?php endif; ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="transport_name">Name *</label>
                                </th>
                                <td>
                                    <input type="text"
                                           name="transport_name"
                                           id="transport_name"
                                           class="regular-text"
                                           value="<?php echo $edit_transport ? esc_attr($edit_transport['name']) : ''; ?>"
                                           required>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <input type="submit"
                                   name="submit"
                                   class="button button-primary"
                                   value="<?php echo $edit_transport ? 'Aktualisieren' : 'Hinzufügen'; ?>">
                            <?php if ($edit_transport): ?>
                                <a href="<?php echo admin_url('admin.php?page=tour_transports'); ?>"
                                   class="button">Abbrechen</a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Section -->
        <div class="tour-transport-list">
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Alle Transporte</h2>
                </div>
                <div class="inside">
                    <?php if (empty($transports)): ?>
                        <p>Keine Transporte gefunden. Fügen Sie einen neuen Transport hinzu.</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Erstellt</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transports as $transport): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($transport['name']); ?></strong></td>
                                        <td><?php echo esc_html(date('d.m.Y H:i', strtotime($transport['created_at']))); ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=tour_transports&action=edit&id=' . $transport['id']); ?>"
                                               class="button button-small">Bearbeiten</a>

                                            <form method="post" style="display: inline;"
                                                  onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Transport löschen möchten?');">
                                                <?php wp_nonce_field('tour_transport_action'); ?>
                                                <input type="hidden" name="tour_transport_action" value="delete">
                                                <input type="hidden" name="transport_id" value="<?php echo esc_attr($transport['id']); ?>">
                                                <input type="submit"
                                                       class="button button-small button-link-delete"
                                                       value="Löschen">
                                            </form>
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
