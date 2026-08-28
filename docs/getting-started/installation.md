# Installation

The Trombongos Tour Plugin is a standard WordPress plugin. It has no external
PHP dependencies and stores its data in dedicated database tables.

## Requirements

- A working WordPress installation.
- PHP support for the WordPress REST API (enabled by default in modern
  WordPress versions).
- Permission to install plugins (an administrator account with the
  `manage_options` capability).

## Install the plugin

1. Copy the plugin folder into your WordPress plugins directory so that the main
   file is located at:

   ```text
   wp-content/plugins/trombongos_tour_wp/tour.php
   ```

2. In the WordPress backend, open **Plugins** and activate
   **Trombongos Tour Plugin**.

On activation the plugin:

- registers the plugin version option (`tour_plugin_version`),
- creates its database tables (seasons, categories, transports, events), and
- runs any pending [database migrations](../reference/migrations.md).

!!! note
    The database tables are created with the WordPress table prefix (for
    example `wp_tour_seasons`). See the
    [Database Schema](../reference/database-schema.md) for details.

## Verify the installation

After activation you should see a **Trombongos Tour** menu item (with a calendar
icon) in the WordPress admin sidebar. Opening it shows the tour overview page.

You can also verify the REST API by requesting:

```text
https://your-site.example/wp-json/tour/v1/tour
```

Until a season is marked active and populated with events, the endpoint returns
a `404` with the message `No active season found`. Continue with
[Configuration](configuration.md) to set up your first season.
