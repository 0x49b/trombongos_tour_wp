# Categories

Categories group events within a season — for example a specific tour weekend or
a block of related appearances. Manage them under
**Trombongos Tour → Kategorien**.

## Fields

| Field        | Description                                                    |
| ------------ | ------------------------------------------------------------- |
| `title`      | Category title shown as a section heading.                     |
| `date_start` | Start date of the category.                                   |
| `date_end`   | End date of the category.                                     |
| `public`     | Whether the category (and its events) is shown publicly.      |
| `sort`       | Sort order within the season (ascending).                     |
| `season_id`  | The season this category belongs to.                          |

## Ordering

Categories are returned and rendered ordered by their `sort` value (ascending).
Use the sort value to control the order in which category sections appear in the
[REST API](../reference/rest-api.md) response and in the
[shortcode](shortcode.md) output.

## Visibility

A category is only rendered in the public shortcode output when `public` is set.
Individual events additionally have their own visibility flag — see
[Events](events.md). To hide a whole block of events, unset the category's
`public` flag.

## Relationship to seasons and events

- Each category belongs to exactly one [season](seasons.md).
- Each [event](events.md) belongs to exactly one category.

When importing from a Google Sheet, categories are derived from the
`Tagesinfo` column and created automatically if they do not yet exist. See
[Import](import.md).
