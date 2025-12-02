<?php
global $wpdb;

// Get statistics
$active_season = $wpdb->get_row("SELECT * FROM " . TOUR_SEASONS . " WHERE active = 1 LIMIT 1", ARRAY_A);
$total_seasons = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_SEASONS);
$total_categories = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_CATEGORIES);
$total_transports = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_TRANSPORTS);
$total_events = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_EVENTS);
$fix_events = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE fix = 1");
$public_events = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE public = 1");
$upcoming_events = $wpdb->get_var("SELECT COUNT(*) FROM " . TOUR_EVENTS . " WHERE date >= CURDATE() AND fix = 1");

// Get recent events
$recent_events = $wpdb->get_results(
    "SELECT e.*, c.title as category_title
     FROM " . TOUR_EVENTS . " e
     LEFT JOIN " . TOUR_CATEGORIES . " c ON e.category_id = c.id
     WHERE e.date >= CURDATE() AND e.fix = 1
     ORDER BY e.date ASC
     LIMIT 5",
    ARRAY_A
);
?>

<div class="wrap">
    <h1>Trombongos Tour - Übersicht</h1>

    <div class="tour-dashboard" style="margin-top: 20px;">

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">

            <div class="postbox" style="padding: 15px;">
                <h3 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-calendar-alt" style="color: #2271b1;"></span>
                    Aktive Saison
                </h3>
                <p style="font-size: 24px; margin: 0; font-weight: bold;">
                    <?php echo $active_season ? esc_html($active_season['name']) : 'Keine'; ?>
                </p>
            </div>

            <div class="postbox" style="padding: 15px;">
                <h3 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-tickets-alt" style="color: #00a32a;"></span>
                    Kommende Auftritte
                </h3>
                <p style="font-size: 24px; margin: 0; font-weight: bold;">
                    <?php echo $upcoming_events; ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    Bestätigt & Zukünftig
                </p>
            </div>

            <div class="postbox" style="padding: 15px;">
                <h3 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-yes-alt" style="color: #dba617;"></span>
                    Bestätigte Auftritte
                </h3>
                <p style="font-size: 24px; margin: 0; font-weight: bold;">
                    <?php echo $fix_events; ?> / <?php echo $total_events; ?>
                </p>
            </div>

            <div class="postbox" style="padding: 15px;">
                <h3 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-visibility" style="color: #7e8993;"></span>
                    Öffentliche Auftritte
                </h3>
                <p style="font-size: 24px; margin: 0; font-weight: bold;">
                    <?php echo $public_events; ?>
                </p>
            </div>

        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

            <!-- Upcoming Events -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Nächste Auftritte</h2>
                </div>
                <div class="inside">
                    <?php if (empty($recent_events)): ?>
                        <p>Keine kommenden Auftritte gefunden.</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Name</th>
                                    <th>Kategorie</th>
                                    <th>Ort</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_events as $event): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y', strtotime($event['date'])); ?></td>
                                        <td><strong><?php echo esc_html($event['name']); ?></strong></td>
                                        <td><?php echo esc_html($event['category_title']); ?></td>
                                        <td><?php echo esc_html($event['location']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="margin-top: 10px;">
                            <a href="<?php echo admin_url('admin.php?page=tour_events'); ?>" class="button">Alle Auftritte anzeigen</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links & Info -->
            <div>
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Schnellzugriff</h2>
                    </div>
                    <div class="inside">
                        <p><a href="<?php echo admin_url('admin.php?page=tour_events&action=add'); ?>" class="button button-primary" style="width: 100%; text-align: center;">Neuen Auftritt hinzufügen</a></p>
                        <p><a href="<?php echo admin_url('admin.php?page=tour_events'); ?>" class="button" style="width: 100%; text-align: center;">Alle Auftritte</a></p>
                        <p><a href="<?php echo admin_url('admin.php?page=tour_categories'); ?>" class="button" style="width: 100%; text-align: center;">Kategorien verwalten</a></p>
                        <p><a href="<?php echo admin_url('admin.php?page=tour_seasons'); ?>" class="button" style="width: 100%; text-align: center;">Saisons verwalten</a></p>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Statistiken</h2>
                    </div>
                    <div class="inside">
                        <p><strong>Saisons:</strong> <?php echo $total_seasons; ?></p>
                        <p><strong>Kategorien:</strong> <?php echo $total_categories; ?></p>
                        <p><strong>Transporte:</strong> <?php echo $total_transports; ?></p>
                        <p><strong>Auftritte gesamt:</strong> <?php echo $total_events; ?></p>
                    </div>
                </div>

                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Shortcodes</h2>
                    </div>
                    <div class="inside">
                        <p>Verwenden Sie diese Shortcodes in Ihren Seiten:</p>
                        <p><code>[tourdaten]</code><br><small>Zeigt die öffentlichen Tourdaten an</small></p>
                        <p><strong>REST API Endpoint:</strong><br>
                        <code><?php echo rest_url('tour/v1/tour'); ?></code></p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>