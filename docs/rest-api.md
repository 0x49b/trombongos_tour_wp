# REST API

Das Plugin registriert einen öffentlichen REST-Endpunkt
(`functions/api/tour-api.php`), der die Tourdaten der **aktiven Saison**
liefert.

## Endpunkt

```text
GET /wp-json/tour/v1/tour
```

- **Authentifizierung:** keine (öffentlicher Endpunkt)
- **Antwortformat:** JSON

## Antwort

```json
{
  "season": "2025/2026",
  "requestURL": "https://example.com/tour/v1/tour",
  "requestTime": "28.08.2026 10:15",
  "data": [
    {
      "title": "1. Wochenende",
      "date_start": "10.01.2026",
      "date_end": "11.01.2026",
      "public": true,
      "evening_count": 2,
      "evenings": [
        {
          "id": 1,
          "uuid": "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx",
          "name": "Auftritt Dorfplatz",
          "date": "10.01.2026",
          "day": "Samstag",
          "sort": 0,
          "type": "Auftritt",
          "organizer": "Verein XY",
          "location": "Dorfplatz",
          "maps_url": "https://maps.example.com/...",
          "play": "20:00",
          "gathering": "18:30",
          "makeup": "17:00",
          "warehouse": "16:30",
          "sun": null,
          "trailer": "Anhänger 1",
          "transport": "Car",
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

## Verhalten

- Es wird die Saison mit `active = 1` verwendet. Existiert keine aktive
  Saison, antwortet der Endpunkt mit HTTP `404` (`no_active_season`).
- Pro Kategorie werden nur Events mit `date >= CURDATE()` geliefert,
  sortiert nach Datum und `sort`.
- `firstOnDay` markiert das jeweils erste Event eines Tages und dient dem
  Frontend zur Gruppierung.
- `type` wird als Text geliefert: `Auftritt`, `Infos`, `GV` oder `Anderes`.
- Datums- und Zeitwerte werden mit den Hilfsfunktionen `tour_format_date()`
  bzw. `tour_format_time()` formatiert (`DD.MM.YYYY` / `HH:MM`).
