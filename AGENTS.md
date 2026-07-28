# AGENTS.md

## Cursor Cloud specific instructions

This repository is the **Trombongos Tour** WordPress plugin (PHP). It is not a
standalone app — it runs inside a WordPress install backed by MySQL/MariaDB.
There is no package manager, no build step, and no automated test/lint suite in
the repo (CI only deploys via FTP).

### Environment layout (persisted in the VM snapshot)

- PHP 8.3 CLI + extensions, MariaDB server, and WP-CLI (`wp`) are installed system-wide.
- WordPress core lives at `/home/ubuntu/wp`, configured against a local MariaDB
  database `wordpress` (user `wp` / password `wp`).
- The plugin is symlinked into WordPress at
  `/home/ubuntu/wp/wp-content/plugins/trombongos_tour_wp -> /workspace`, so edits
  in `/workspace` are live immediately (no rebuild needed).
- Site URL is `http://localhost:8080`. WP admin login: `admin` / `admin`.

### Starting services (NOT done by the update script — do this each session)

MariaDB and the PHP dev server are not auto-started on boot. Start them with:

```bash
# 1) Start MariaDB (data dir is persisted, so your tour data survives)
sudo mkdir -p /var/run/mysqld && sudo chown mysql:mysql /var/run/mysqld
sudo mariadbd-safe &
sleep 8 && sudo mysqladmin ping

# 2) Start the WordPress dev server (foreground; run in tmux/background)
cd /home/ubuntu/wp && wp server --host=0.0.0.0 --port=8080 --allow-root
```

WP-CLI commands must be run from `/home/ubuntu/wp` with `--allow-root`.

### Plugin behavior gotchas (non-obvious)

- Permalinks are "plain", so the REST endpoint is reached via
  `http://localhost:8080/index.php?rest_route=/tour/v1/tour` (not `/wp-json/...`).
- The REST API (`functions/api/tour-api.php`) only returns events with
  `date >= CURDATE()`. Seed events with **future** dates or they won't appear.
- The API returns `404 no_active_season` unless a season row has `active = 1`
  (activating a season in the admin deactivates all others).
- The `[tourdaten]` frontend shortcode only renders events that are BOTH
  `public` and `fix` (bestätigt), inside a `public` category.
- Plugin activation (`wp plugin activate trombongos_tour_wp`) creates the four
  `wp_tour_*` tables and applies `db_migration/*.sql` once (tracked via WP options
  `tour_migration_*` and `tour_plugin_version`).

### Quick end-to-end check

```bash
curl -sS "http://localhost:8080/index.php?rest_route=/tour/v1/tour"
```
