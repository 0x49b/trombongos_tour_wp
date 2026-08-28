# REST API

Das Plugin registriert einen öffentlichen REST-API-Endpunkt
(`functions/api/tour-api.php`), der die Tourdaten der aktiven Saison ausgibt.

## Endpunkt

```
GET /wp-json/tour/v1/tour
```

- **Namespace:** `tour/v1`
- **Route:** `/tour`
- **Methode:** `GET`
- **Zugriff:** öffentlich (`permission_callback` = `__return_true`)

## Antwortformat

Die Antwort repliziert das Ausgabeformat der früheren Django-API.

```json
{
  "season": "2024/25",
  "requestURL": "https://tour.trombongos.ch/wp-json/tour/v1/tour",
  "requestTime": "28.08.2026 08:29",
  "data": [
    {
      "title": "1. Wochenende",
      "date_start": "01.09.2026",
      "date_end": "03.09.2026",
      "public": true,
      "evening_count": 2,
      "evenings": [
        {
          "id": 1,
          "uuid": "…",
          "name": "Auftritt Dorffest",
          "date": "01.09.2026",
          "day": "Montag",
          "sort": 0,
          "type": "Auftritt",
          "organizer": "…",
          "location": "…",
          "maps_url": "https://maps.google.com/…",
          "play": "20:00",
          "gathering": "18:30",
          "makeup": "18:00",
          "warehouse": "17:30",
          "sun": "19:00",
          "trailer": "…",
          "transport": "Bus",
          "fix": true,
          "public": true,
          "info": "…",
          "firstOnDay": true
        }
      ]
    }
  ]
}
```

## Felder der Antwort

### Wurzelobjekt

| Feld          | Typ    | Beschreibung                              |
|---------------|--------|-------------------------------------------|
| `season`      | string | Name der aktiven Saison                   |
| `requestURL`  | string | URL der Anfrage                           |
| `requestTime` | string | Zeitpunkt der Anfrage (`d.m.Y H:i`)       |
| `data`        | array  | Liste der Kategorien                      |

### Kategorie (`data[]`)

| Feld            | Typ     | Beschreibung                                    |
|-----------------|---------|-------------------------------------------------|
| `title`         | string  | Titel der Kategorie                             |
| `date_start`    | string  | Startdatum (`d.m.Y`)                            |
| `date_end`      | string  | Enddatum (`d.m.Y`)                              |
| `public`        | boolean | Ob die Kategorie öffentlich ist                 |
| `evening_count` | integer | Anzahl der Auftritte                            |
| `evenings`      | array   | Liste der Auftritte                             |

### Auftritt (`evenings[]`)

| Feld         | Typ     | Beschreibung                                            |
|--------------|---------|--------------------------------------------------------|
| `id`         | integer | Interne ID                                            |
| `uuid`       | string  | UUID des Auftritts                                    |
| `name`       | string  | Bezeichnung                                           |
| `date`       | string  | Datum (`d.m.Y`)                                       |
| `day`        | string  | Wochentag (Name)                                     |
| `sort`       | integer | Sortierwert innerhalb des Tages                     |
| `type`       | string  | Typ (`Auftritt`, `Infos`, `GV`, `Anderes`)          |
| `organizer`  | string  | Veranstalter                                         |
| `location`   | string  | Ort                                                  |
| `maps_url`   | string  | Kartenlink                                           |
| `play`       | string  | Spielbeginn (`H:i`)                                  |
| `gathering`  | string  | Besammlung (`H:i`)                                   |
| `makeup`     | string  | Maske (`H:i`)                                        |
| `warehouse`  | string  | Lager (`H:i`)                                        |
| `sun`        | string  | weitere Uhrzeit (`H:i`)                              |
| `trailer`    | string  | Anhänger-Information                                 |
| `transport`  | string  | Name des Transportmittels                           |
| `fix`        | boolean | Ob der Termin fix ist                               |
| `public`     | boolean | Ob der Termin öffentlich ist                        |
| `info`       | string  | Zusätzliche Informationen                           |
| `firstOnDay` | boolean | Ob es der erste Auftritt des Tages ist             |

## Fehler

| Fehlercode          | HTTP-Status | Bedeutung                          |
|---------------------|-------------|------------------------------------|
| `no_active_season`  | 404         | Es existiert keine aktive Saison.  |

## Hinweise

- Es werden nur Auftritte **ab dem heutigen Datum** (`date >= CURDATE()`) zurückgegeben.
- Kategorien werden nach `sort`, Auftritte nach `date` und `sort` sortiert.
