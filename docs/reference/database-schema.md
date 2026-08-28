# Database Schema

The plugin creates four tables on activation. All table names use the WordPress
table prefix (shown here as `wp_`). The constants used in the code are
`TOUR_SEASONS`, `TOUR_CATEGORIES`, `TOUR_TRANSPORTS` and `TOUR_EVENTS`.

```text
wp_tour_seasons ──< wp_tour_categories ──< wp_tour_events >── wp_tour_transports
```

## `wp_tour_seasons`

| Column       | Type          | Notes                                     |
| ------------ | ------------- | ----------------------------------------- |
| `id`         | BIGINT UNSIGNED | Primary key, auto-increment.            |
| `uuid`       | CHAR(36)      | Unique.                                   |
| `name`       | VARCHAR(9)    | Season name.                              |
| `start_date` | DATE          | Season start.                            |
| `end_date`   | DATE          | Season end.                              |
| `active`     | TINYINT(1)    | `1` if this is the active season (indexed). |
| `created_at` | DATETIME      | Defaults to current timestamp.            |
| `updated_at` | DATETIME      | Updated on change.                        |

## `wp_tour_transports`

| Column       | Type            | Notes                                   |
| ------------ | --------------- | --------------------------------------- |
| `id`         | BIGINT UNSIGNED | Primary key, auto-increment.            |
| `uuid`       | CHAR(36)        | Unique.                                 |
| `name`       | VARCHAR(255)    | Transport name.                         |
| `default`    | TINYINT(1)      | `1` for the default transport (indexed). |
| `created_at` | DATETIME        | Defaults to current timestamp.          |
| `updated_at` | DATETIME        | Updated on change.                      |

!!! note
    The `default` column is added by
    [migration 05](migrations.md). Fresh installs on version 3.0 include it via
    the creation SQL as well.

## `wp_tour_categories`

| Column       | Type            | Notes                                   |
| ------------ | --------------- | --------------------------------------- |
| `id`         | BIGINT UNSIGNED | Primary key, auto-increment.            |
| `uuid`       | CHAR(36)        | Unique.                                 |
| `title`      | VARCHAR(255)    | Category title.                         |
| `date_start` | DATE            | Category start.                        |
| `date_end`   | DATE            | Category end.                          |
| `public`     | TINYINT(1)      | `1` if public (default `1`).            |
| `sort`       | INT             | Sort order (indexed).                   |
| `season_id`  | BIGINT UNSIGNED | References a season (indexed).          |
| `created_at` | DATETIME        | Defaults to current timestamp.          |
| `updated_at` | DATETIME        | Updated on change.                      |

## `wp_tour_events`

| Column         | Type            | Notes                                        |
| -------------- | --------------- | -------------------------------------------- |
| `id`           | BIGINT UNSIGNED | Primary key, auto-increment.                 |
| `uuid`         | CHAR(36)        | Unique.                                       |
| `name`         | VARCHAR(255)    | Event name.                                  |
| `category_id`  | BIGINT UNSIGNED | References a category (indexed).             |
| `transport_id` | BIGINT UNSIGNED | References a transport (nullable, indexed).  |
| `date`         | DATE            | Event date (indexed).                        |
| `day`          | TINYINT         | Weekday index (0 = Monday).                  |
| `sort`         | INT             | Sort order within the category.              |
| `type`         | TINYINT         | Event type (0–3).                            |
| `organizer`    | VARCHAR(255)    | Nullable.                                    |
| `location`     | VARCHAR(255)    | Nullable.                                    |
| `maps_url`     | VARCHAR(500)    | Nullable.                                    |
| `play`         | TIME            | Performance time.                           |
| `gathering`    | TIME            | Nullable.                                    |
| `makeup`       | TIME            | Nullable.                                    |
| `warehouse`    | TIME            | Nullable.                                    |
| `sun`          | TIME            | Nullable.                                    |
| `trailer`      | VARCHAR(255)    | Nullable.                                    |
| `fix`          | TINYINT(1)      | `1` if confirmed.                           |
| `public`       | TINYINT(1)      | `1` if public.                             |
| `info`         | TEXT            | Nullable.                                   |
| `created_at`   | DATETIME        | Defaults to current timestamp.               |
| `updated_at`   | DATETIME        | Updated on change.                          |

There is a composite index `event_query` on (`public`, `fix`, `date`) to speed
up the public event queries.

!!! note
    The `maps_url` column is added by [migration 06](migrations.md). Fresh
    installs on version 3.0 include it via the creation SQL as well.
