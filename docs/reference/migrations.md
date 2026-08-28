# Database Migrations

The plugin ships incremental SQL migrations in the `db_migration/` directory.
They are applied automatically:

- on **activation** (`tour_plugin_activation`), and
- on every load via the `plugins_loaded` hook (`tour_run_migrations`).

## How migrations run

- Migration files are read in filename order (`sort()`), so the numeric prefix
  (`01_`, `02_`, …) determines the execution order.
- Each migration is tracked with a WordPress option
  (`tour_migration_<name>`). A migration that has already run is skipped.
- Table names in the SQL use the literal `wp_tour_` prefix; before execution the
  plugin rewrites this to the site's actual table prefix
  (`tour_prepare_migration_sql`).
- Each file is split into statements on `;`, comment-only lines are ignored, and
  the statements are executed in sequence.
- For selected migrations the plugin additionally verifies that the expected
  schema change is actually present (`tour_migration_schema_matches`). If a
  migration was marked as run but the column is missing, the plugin clears the
  flag and re-runs it. This currently covers:
    - `05_migration_add_transport_default` → checks the `default` column on
      transports.
    - `06_migration_add_maps_url` → checks the `maps_url` column on events.

## Included migrations

| File                                        | Purpose                                                                 |
| ------------------------------------------- | ---------------------------------------------------------------------- |
| `01_migration_time_fields.sql`              | Replaces old time fields with `gathering`, `makeup`, `warehouse`, `sun`. |
| `02_migration_remove_season_restriction.sql`| Makes `season_id` on categories nullable (cross-season categories).     |
| `03_migration_remove_meal_drinks_cert.sql`  | Removes the `meal`, `drinks` and `cert` columns from events.            |
| `04_migration_enforce_season_category_relation.sql` | Re-enforces a non-null `season_id` on categories.              |
| `05_migration_add_transport_default.sql`    | Adds the `default` column (and index) to transports.                    |
| `06_migration_add_maps_url.sql`             | Adds the `maps_url` column to events.                                   |

## Adding a new migration

1. Create a new file in `db_migration/` with the next numeric prefix, e.g.
   `07_migration_<description>.sql`.
2. Write plain SQL using the `wp_tour_` table prefix. The prefix is rewritten to
   the site's real prefix at runtime.
3. Separate statements with `;`. Lines that start with `--` are treated as
   comments and skipped.
4. (Optional) If the change should be verified after running, extend
   `tour_migration_schema_matches()` in `tour.php` with a check for your
   migration name.

!!! warning
    Migrations run on every page load through `plugins_loaded`. Keep them
    idempotent where possible, and rely on the tracking option so they only take
    effect once.
