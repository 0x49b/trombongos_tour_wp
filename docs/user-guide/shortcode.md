# Frontend Shortcode

The plugin registers a `[tourdaten]` shortcode that renders a table of the
public tour dates for the active season.

## Usage

Add the shortcode to any page or post:

```text
[tourdaten]
```

The shortcode takes no attributes. It always renders the
[active season](seasons.md).

## What is rendered

The shortcode outputs a heading (`Tourdaten <season name>`) followed by a table
with three columns:

| Column          | Source field |
| --------------- | ------------ |
| Datum           | event date   |
| Anlass          | event name   |
| Auftrittszeit   | event `play` |

Category titles are rendered as full-width section rows above their events.

## Which events appear

An event is rendered only when **all** of the following are true:

- its [category](categories.md) is marked **public**, and
- the event is marked **public**, and
- the event is marked **fix** (confirmed).

The date is only printed on the first event of each date to avoid repetition.

## How it works

Internally the shortcode calls the same data function as the
[REST API](../reference/rest-api.md) directly (instead of making an HTTP
request), which avoids permalink and REST URL issues. As a result, the set of
events it can display is limited to upcoming events of the active season (dates
from today onwards). If the active season is missing or an error occurs, the
shortcode returns a short error message instead of the table.
