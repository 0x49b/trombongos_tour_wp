# Datenbank & Migrationen

Bei der Plugin-Aktivierung (`tour_plugin_activation()` in `tour.php`) werden
die Tabellen über `dbDelta()` angelegt und anschliessend alle Migrationen
ausgeführt. Migrationen laufen zusätzlich bei jedem `plugins_loaded`-Hook,
damit Updates ohne erneute Aktivierung funktionieren.

## Tabellen

Alle Tabellennamen verwenden das konfigurierte WordPress-Präfix
(`$wpdb->prefix`, Standard `wp_`).

### `wp_tour_seasons`

Saisons mit Start-/Enddatum. Genau eine Saison sollte `active = 1` gesetzt
haben – sie bestimmt, welche Daten API und Shortcode liefern.

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| `id` | BIGINT UNSIGNED | Primärschlüssel |
| `uuid` | CHAR(36) | Eindeutige UUID |
| `name` | VARCHAR(9) | Saisonname, z. B. `2025/2026` |
| `start_date` / `end_date` | DATE | Saisonzeitraum |
| `active` | TINYINT(1) | Aktive Saison |

### `wp_tour_categories`

Kategorien (z. B. Wochenenden) innerhalb einer Saison.

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| `id` | BIGINT UNSIGNED | Primärschlüssel |
| `uuid` | CHAR(36) | Eindeutige UUID |
| `title` | VARCHAR(255) | Titel |
| `date_start` / `date_end` | DATE | Zeitraum |
| `public` | TINYINT(1) | Öffentlich sichtbar |
| `sort` | INT | Sortierreihenfolge |
| `season_id` | BIGINT UNSIGNED | Zugehörige Saison |

### `wp_tour_transports`

Transportmittel; eines kann als Standard (`default = 1`) markiert werden.

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| `id` | BIGINT UNSIGNED | Primärschlüssel |
| `uuid` | CHAR(36) | Eindeutige UUID |
| `name` | VARCHAR(255) | Bezeichnung |
| `default` | TINYINT(1) | Standard-Transport |

### `wp_tour_events`

Die eigentlichen Auftritte/Events.

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| `id` | BIGINT UNSIGNED | Primärschlüssel |
| `uuid` | CHAR(36) | Eindeutige UUID |
| `name` | VARCHAR(255) | Name des Events |
| `category_id` | BIGINT UNSIGNED | Zugehörige Kategorie |
| `transport_id` | BIGINT UNSIGNED | Transport (optional) |
| `date` | DATE | Datum |
| `day` | TINYINT | Wochentag (0 = Montag) |
| `sort` | INT | Sortierung innerhalb des Tages |
| `type` | TINYINT | 0 = Auftritt, 1 = Infos, 2 = GV, 3 = Anderes |
| `organizer` / `location` | VARCHAR(255) | Veranstalter / Ort |
| `maps_url` | VARCHAR(500) | Google-Maps-Link |
| `play`, `gathering`, `makeup`, `warehouse`, `sun` | TIME | Zeiten |
| `trailer` | VARCHAR(255) | Anhänger |
| `fix` | TINYINT(1) | Termin fixiert |
| `public` | TINYINT(1) | Öffentlich sichtbar |
| `info` | TEXT | Zusatzinfos |

## Migrationen

SQL-Migrationen liegen in `db_migration/` und werden alphabetisch sortiert
ausgeführt. Der Fortschritt wird pro Migration als WordPress-Option
(`tour_migration_<name>`) gespeichert; zusätzlich prüft
`tour_migration_schema_matches()` bei Schema-Migrationen, ob die Änderung
tatsächlich in der Datenbank angekommen ist.

| Migration | Zweck |
|-----------|-------|
| `01_migration_time_fields` | Zeitfelder angepasst |
| `02_migration_remove_season_restriction` | Saison-Einschränkung entfernt |
| `03_migration_remove_meal_drinks_cert` | Felder Essen/Getränke/Zertifikat entfernt |
| `04_migration_enforce_season_category_relation` | Beziehung Saison–Kategorie erzwungen |
| `05_migration_add_transport_default` | Spalte `default` für Transporte |
| `06_migration_add_maps_url` | Spalte `maps_url` für Events |

In den Migrationsdateien wird das Präfix `wp_tour_` beim Ausführen automatisch
durch das tatsächliche Tabellenpräfix ersetzt
(`tour_prepare_migration_sql()`).
