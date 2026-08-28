# REST API

The plugin exposes a public REST API endpoint that returns the active season and
its upcoming public events as JSON. The output format mirrors the previous
Django-based API.

## Endpoint

```text
GET /wp-json/tour/v1/tour
```

- **Namespace / route:** `tour/v1` `/tour`
- **Method:** `GET`
- **Authentication:** none (public endpoint)

## Behaviour

- The endpoint always uses the **active** [season](../user-guide/seasons.md).
- Categories are returned ordered by their `sort` value.
- For each category, events are included where the **event date is today or in
  the future**, ordered by date and then by `sort`.
- If no season is active, the endpoint returns a `404` error:

  ```json
  {
    "code": "no_active_season",
    "message": "No active season found",
    "data": { "status": 404 }
  }
  ```

## Response shape

```json
{
  "season": "2026",
  "requestURL": "https://your-site.example/wp-json/tour/v1/tour",
  "requestTime": "28.08.2026 08:29",
  "data": [
    {
      "title": "1. Wochenende",
      "date_start": "01.06.2026",
      "date_end": "02.06.2026",
      "public": true,
      "evening_count": 1,
      "evenings": [
        {
          "id": 42,
          "uuid": "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx",
          "name": "Dorffest Musterhausen",
          "date": "01.06.2026",
          "day": "Montag",
          "sort": 1,
          "type": "Auftritt",
          "organizer": "Musikverein",
          "location": "Musterhausen",
          "maps_url": "https://www.google.com/maps/search/?api=1&query=Musterhausen",
          "play": "20:00",
          "gathering": "18:30",
          "makeup": null,
          "warehouse": "17:45",
          "sun": null,
          "trailer": null,
          "transport": "Bus",
          "fix": true,
          "public": true,
          "info": null,
          "firstOnDay": true
        }
      ]
    }
  ]
}
```

## Top-level fields

| Field         | Type   | Description                                        |
| ------------- | ------ | -------------------------------------------------- |
| `season`      | string | Name of the active season.                         |
| `requestURL`  | string | The URL that was requested.                        |
| `requestTime` | string | Server time of the request (`d.m.Y H:i`).          |
| `data`        | array  | List of categories with their events.              |

## Category fields (`data[]`)

| Field           | Type    | Description                                     |
| --------------- | ------- | ----------------------------------------------- |
| `title`         | string  | Category title.                                 |
| `date_start`    | string  | Category start date (`d.m.Y`).                  |
| `date_end`      | string  | Category end date (`d.m.Y`).                    |
| `public`        | boolean | Whether the category is public.                 |
| `evening_count` | integer | Number of events (evenings) in the category.    |
| `evenings`      | array   | List of events.                                 |

## Event fields (`evenings[]`)

| Field        | Type            | Description                                             |
| ------------ | --------------- | ------------------------------------------------------ |
| `id`         | integer         | Numeric event ID.                                      |
| `uuid`       | string          | Event UUID.                                            |
| `name`       | string          | Event name.                                            |
| `date`       | string          | Event date (`d.m.Y`).                                  |
| `day`        | string          | Weekday name in German.                                |
| `sort`       | integer         | Sort order within the category.                        |
| `type`       | string          | Event type name (Auftritt / Infos / GV / Anderes).     |
| `organizer`  | string \| null  | Organizer.                                             |
| `location`   | string \| null  | Location.                                              |
| `maps_url`   | string \| null  | Map link.                                              |
| `play`       | string \| null  | Performance time (`H:i`).                              |
| `gathering`  | string \| null  | Gathering time (`H:i`).                                |
| `makeup`     | string \| null  | Make-up time (`H:i`).                                  |
| `warehouse`  | string \| null  | Warehouse departure time (`H:i`).                     |
| `sun`        | string \| null  | Sun time (`H:i`).                                      |
| `trailer`    | string \| null  | Trailer information.                                   |
| `transport`  | string \| null  | Transport name (or `null` if none).                   |
| `fix`        | boolean         | Whether the appearance is confirmed.                  |
| `public`     | boolean         | Whether the event is public.                          |
| `info`       | string \| null  | Additional information.                               |
| `firstOnDay` | boolean         | `true` for the first event on a given date.           |

## Notes

- Dates are formatted as `d.m.Y` and times as `H:i`. Empty times are returned as
  `null`.
- `firstOnDay` is computed per category and is useful for rendering the date only
  once per day.
