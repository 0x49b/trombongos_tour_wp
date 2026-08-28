# Admin-Backend

Nach der Aktivierung erscheint im WordPress-Backend der Menüpunkt
**Trombongos Tour** (Dashicon `dashicons-calendar-alt`). Alle Seiten setzen
die Berechtigung `manage_options` voraus.

## Seiten

| Menüpunkt | Slug | Datei | Zweck |
|-----------|------|-------|-------|
| Übersicht | `trb_tour` | `functions/backend/tour_overview.php` | Übersicht über die aktive Saison |
| Auftritte | `tour_events` | `functions/backend/tour_events.php` | Auftritte/Events verwalten |
| Kategorien | `tour_categories` | `functions/backend/tour_categories.php` | Kategorien (z. B. Wochenenden) verwalten |
| Saisons | `tour_seasons` | `functions/backend/tour_seasons.php` | Saisons anlegen und aktivieren |
| Transport | `tour_transports` | `functions/backend/tour_transports.php` | Transportmittel verwalten |
| Import | `tour_importer` | `functions/backend/tour_importer.php` | Daten importieren |

Die Menüs werden in `tour.php` über `setup_theme_admin_menus()` registriert.
Jede Seite ruft zunächst `tour_admin_show_notice()` auf (definiert in
`functions/backend/tour-admin-helpers.php`), um Statusmeldungen anzuzeigen.

## Styles

Für das Backend wird das Stylesheet `assets/css/admin.css` über den Hook
`admin_enqueue_scripts` eingebunden (`tour_scripts_backend()` in `tour.php`).

## Hilfsfunktionen

`functions/tour-helpers.php` stellt gemeinsame Funktionen für Backend, API und
Frontend bereit:

| Funktion | Beschreibung |
|----------|--------------|
| `tour_format_date( $date )` | Formatiert ein Datum als `DD.MM.YYYY` |
| `tour_format_date_range( $start, $end )` | Formatiert einen Datumsbereich |
| `tour_format_time( $time )` | Formatiert eine Zeit als `HH:MM` |
| `tour_get_day_name( $day_num )` | Wochentagsname (0 = Montag) |
| `tour_table_has_column( $table, $column )` | Prüft, ob eine Tabellenspalte existiert |
| `tour_get_default_transport()` | Liefert den als Standard markierten Transport |
