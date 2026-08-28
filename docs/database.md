# Database

On activation the plugin creates four tables (using the WordPress table prefix):

| Constant | Table | Contents |
|----------|-------|----------|
| `TOUR_SEASONS` | `{prefix}tour_seasons` | Seasons (`active` flag) |
| `TOUR_CATEGORIES` | `{prefix}tour_categories` | Categories tied to a season |
| `TOUR_TRANSPORTS` | `{prefix}tour_transports` | Transport options |
| `TOUR_EVENTS` | `{prefix}tour_events` | Events / performances |

## Migrations

SQL files in `db_migration/` run on activation and on `plugins_loaded` when the plugin loads. Each file is tracked with a WordPress option (`tour_migration_<name>`). Some migrations also verify that expected columns exist before being marked complete.

Placeholders like `wp_tour_` in migration SQL are rewritten to the current `$wpdb->prefix` at runtime.
