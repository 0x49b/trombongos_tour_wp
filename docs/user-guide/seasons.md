# Seasons

Seasons group the schedule for a period of time (for example a calendar year).
Manage them under **Trombongos Tour → Saisons**.

## Fields

| Field        | Description                                                       |
| ------------ | --------------------------------------------------------------- |
| `name`       | Season name (up to 9 characters, e.g. a year).                   |
| `start_date` | Start date of the season.                                       |
| `end_date`   | End date of the season.                                         |
| `active`     | Whether this season is the active one (only one at a time).      |

## The active season

Only **one** season should be active at a time. The active season determines
what is served by:

- the [REST API](../reference/rest-api.md) (`/wp-json/tour/v1/tour`), and
- the [`[tourdaten]` shortcode](shortcode.md).

If no season is marked active, the REST API returns a `404`
(`No active season found`) and the shortcode has nothing to render.

## Typical workflow

1. Create a new season at the start of a period.
2. Add [categories](categories.md) and [events](events.md) to it (manually or
   via [import](import.md)).
3. Mark it active when you are ready to publish it.

!!! tip
    The [import tool](import.md) can create a new season for you as part of the
    import. A season created this way defaults to a January–December date range,
    which you can adjust afterwards.
