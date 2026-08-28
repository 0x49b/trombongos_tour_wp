# Events (Auftritte)

An event (*Auftritt*) represents a single appearance. Manage events under
**Trombongos Tour → Auftritte**. Each event belongs to a
[category](categories.md) and optionally references a [transport](transports.md).

## Fields

| Field          | Type      | Description                                                     |
| -------------- | --------- | -------------------------------------------------------------- |
| `name`         | text      | Name of the appearance.                                        |
| `category_id`  | reference | The category this event belongs to.                            |
| `transport_id` | reference | Optional transport used for this event.                        |
| `date`         | date      | Date of the appearance.                                        |
| `day`          | number    | Weekday index (0 = Monday … 6 = Sunday).                       |
| `sort`         | number    | Sort order within the category.                                |
| `type`         | number    | Event type (see below).                                        |
| `organizer`    | text      | Organizer of the appearance.                                   |
| `location`     | text      | Location of the appearance.                                    |
| `maps_url`     | url       | Link to the location on a map.                                 |
| `play`         | time      | Performance time (*Auftrittszeit*).                            |
| `gathering`    | time      | Gathering time (*Besammlung*).                                 |
| `makeup`       | time      | Make-up time (*Schminken*).                                    |
| `warehouse`    | time      | Departure from the warehouse (*Abfahrt Magazin*).             |
| `sun`          | time      | Sunset / sun time (*Sonne*).                                   |
| `trailer`      | text      | Trailer information.                                           |
| `fix`          | boolean   | Whether the appearance is confirmed (*fix*).                   |
| `public`       | boolean   | Whether the event is shown publicly.                           |
| `info`         | text      | Free-form additional information.                              |

## Event types

The numeric `type` field maps to the following names (used in the API output):

| Value | Name       |
| ----- | ---------- |
| 0     | Auftritt   |
| 1     | Infos      |
| 2     | GV         |
| 3     | Anderes    |

## Weekday values

The numeric `day` field maps to a German weekday name:

| Value | Day        |
| ----- | ---------- |
| 0     | Montag     |
| 1     | Dienstag   |
| 2     | Mittwoch   |
| 3     | Donnerstag |
| 4     | Freitag    |
| 5     | Samstag    |
| 6     | Sonntag    |

## Visibility and the `fix` flag

Two flags control whether an event appears on the website:

- `public` – the event may be shown publicly.
- `fix` – the appearance is confirmed.

The [`[tourdaten]` shortcode](shortcode.md) only renders events where **both**
the event is `public`/`fix` **and** its category is `public`. The
[REST API](../reference/rest-api.md) returns all events for public categories
(with the `fix` and `public` flags included in the payload), but only for dates
from today onwards.

## Ordering

Within a category, events are ordered by `date` (ascending) and then by `sort`
(ascending).
