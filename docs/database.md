# Datenbank

Beim Aktivieren des Plugins (`register_activation_hook`) werden die benötigten Tabellen
über `dbDelta()` erstellt (`tour_create_database_tables()`). Anschliessend werden die
Migrationen ausgeführt (`tour_run_migrations()`).

Alle Tabellennamen verwenden das WordPress-Tabellenpräfix (`$wpdb->prefix`, üblicherweise
`wp_`):

| Konstante         | Tabellenname (mit `wp_`) |
|-------------------|--------------------------|
| `TOUR_SEASONS`    | `wp_tour_seasons`        |
| `TOUR_CATEGORIES` | `wp_tour_categories`     |
| `TOUR_TRANSPORTS` | `wp_tour_transports`     |
| `TOUR_EVENTS`     | `wp_tour_events`         |

## Tabelle `tour_seasons`

| Spalte       | Typ            | Hinweise                       |
|--------------|----------------|--------------------------------|
| `id`         | BIGINT UNSIGNED| Primärschlüssel, AUTO_INCREMENT |
| `uuid`       | CHAR(36)       | UNIQUE                          |
| `name`       | VARCHAR(9)     | Name der Saison                 |
| `start_date` | DATE           | Startdatum                      |
| `end_date`   | DATE           | Enddatum                        |
| `active`     | TINYINT(1)     | Standard 0; markiert aktive Saison (indexiert) |
| `created_at` | DATETIME       | Standard CURRENT_TIMESTAMP      |
| `updated_at` | DATETIME       | ON UPDATE CURRENT_TIMESTAMP     |

## Tabelle `tour_transports`

| Spalte       | Typ            | Hinweise                       |
|--------------|----------------|--------------------------------|
| `id`         | BIGINT UNSIGNED| Primärschlüssel                 |
| `uuid`       | CHAR(36)       | UNIQUE                          |
| `name`       | VARCHAR(255)   | Bezeichnung                     |
| `default`    | TINYINT(1)     | Standard 0; markiert Standard-Transport (indexiert) |
| `created_at` | DATETIME       | Standard CURRENT_TIMESTAMP      |
| `updated_at` | DATETIME       | ON UPDATE CURRENT_TIMESTAMP     |

## Tabelle `tour_categories`

| Spalte       | Typ            | Hinweise                       |
|--------------|----------------|--------------------------------|
| `id`         | BIGINT UNSIGNED| Primärschlüssel                 |
| `uuid`       | CHAR(36)       | UNIQUE                          |
| `title`      | VARCHAR(255)   | Titel                           |
| `date_start` | DATE           | Startdatum                      |
| `date_end`   | DATE           | Enddatum                        |
| `public`     | TINYINT(1)     | Standard 1                      |
| `sort`       | INT            | Standard 0; Sortierung (indexiert) |
| `season_id`  | BIGINT UNSIGNED| Verweis auf `tour_seasons` (indexiert) |
| `created_at` | DATETIME       | Standard CURRENT_TIMESTAMP      |
| `updated_at` | DATETIME       | ON UPDATE CURRENT_TIMESTAMP     |

## Tabelle `tour_events`

| Spalte         | Typ            | Hinweise                        |
|----------------|----------------|---------------------------------|
| `id`           | BIGINT UNSIGNED| Primärschlüssel                  |
| `uuid`         | CHAR(36)       | UNIQUE                           |
| `name`         | VARCHAR(255)   | Bezeichnung                      |
| `category_id`  | BIGINT UNSIGNED| Verweis auf `tour_categories` (indexiert) |
| `transport_id` | BIGINT UNSIGNED| Verweis auf `tour_transports` (nullable, indexiert) |
| `date`         | DATE           | Datum (indexiert)               |
| `day`          | TINYINT        | Wochentag (Zahl)                |
| `sort`         | INT            | Standard 0                      |
| `type`         | TINYINT        | Standard 0 (Typ)               |
| `organizer`    | VARCHAR(255)   | nullable                        |
| `location`     | VARCHAR(255)   | nullable                        |
| `maps_url`     | VARCHAR(500)   | nullable                        |
| `play`         | TIME           | Spielbeginn                     |
| `gathering`    | TIME           | nullable                        |
| `makeup`       | TIME           | nullable                        |
| `warehouse`    | TIME           | nullable                        |
| `sun`          | TIME           | nullable                        |
| `trailer`      | VARCHAR(255)   | nullable                        |
| `fix`          | TINYINT(1)     | Standard 0                      |
| `public`       | TINYINT(1)     | Standard 0                      |
| `info`         | TEXT           | nullable                        |
| `created_at`   | DATETIME       | Standard CURRENT_TIMESTAMP      |
| `updated_at`   | DATETIME       | ON UPDATE CURRENT_TIMESTAMP     |

Zusätzlicher kombinierter Index `event_query` über (`public`, `fix`, `date`) für schnelle
Abfragen der öffentlichen Ausgabe.

## Migrationen

Das Verzeichnis `db_migration/` enthält SQL-Migrationsskripte, die beim Aktivieren
ausgeführt werden:

| Datei | Zweck |
|-------|-------|
| `01_migration_time_fields.sql` | Zeitfelder |
| `02_migration_remove_season_restriction.sql` | Entfernt Saison-Einschränkung |
| `03_migration_remove_meal_drinks_cert.sql` | Entfernt Verpflegungs-/Getränke-/Zertifikatsfelder |
| `04_migration_enforce_season_category_relation.sql` | Erzwingt Saison-Kategorie-Beziehung |
| `05_migration_add_transport_default.sql` | Fügt Standard-Transport hinzu |
| `06_migration_add_maps_url.sql` | Fügt `maps_url` hinzu |

Beim Ausführen werden die Präfixe angepasst (`tour_prepare_migration_sql()` ersetzt
`wp_tour_` durch `<prefix>tour_`). Vor der Anwendung wird geprüft, ob eine Migration
bereits ausgeführt wurde.
