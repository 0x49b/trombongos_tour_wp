# Project Structure

The plugin is organised into a main bootstrap file and a set of function modules
grouped by responsibility.

```text
.
├── tour.php                         # Plugin bootstrap (metadata, hooks, table creation, migrations)
├── index.php                        # Silence-is-golden guard
├── assets/
│   └── css/
│       └── admin.css                # Backend admin styles
├── functions/
│   ├── tour-helpers.php             # Shared helpers (date/time formatting, schema checks)
│   ├── api/
│   │   └── tour-api.php             # REST API endpoint (tour/v1/tour)
│   ├── frontend/
│   │   └── tourdaten_shortcode.php  # [tourdaten] shortcode
│   └── backend/
│       ├── tour-admin-helpers.php   # Admin notices and helpers
│       ├── tour_overview.php        # Übersicht admin page
│       ├── tour_events.php          # Auftritte admin page
│       ├── tour_categories.php      # Kategorien admin page
│       ├── tour_seasons.php         # Saisons admin page
│       ├── tour_transports.php      # Transport admin page
│       └── tour_importer.php        # Google Sheet import
├── db_migration/                    # Incremental SQL migrations
├── docs/                            # This documentation (MkDocs)
├── mkdocs.yml                       # MkDocs configuration (gitbook theme)
└── .github/workflows/              # Deployment workflows
```

## Key entry points

- **`tour.php`** — declares the plugin metadata, defines the table-name
  constants (`TOUR_SEASONS`, `TOUR_CATEGORIES`, `TOUR_TRANSPORTS`,
  `TOUR_EVENTS`), registers the admin menu, wires up activation and migration
  hooks, includes the REST API, and registers the `[tourdaten]` shortcode.
- **`functions/tour-helpers.php`** — shared, dependency-free helpers used by the
  admin, API and frontend (date/time formatting, weekday/type names, schema
  checks, default transport lookup).
- **`functions/api/tour-api.php`** — registers and implements the REST endpoint.
- **`functions/frontend/tourdaten_shortcode.php`** — renders the public table.
- **`functions/backend/`** — one file per admin sub-page plus shared admin
  helpers.

## Admin menu registration

The admin menu is registered in `tour.php` via `setup_theme_admin_menus()`,
which adds a top-level page and one sub-page per management area. Each sub-page
callback includes the corresponding file from `functions/backend/`.
