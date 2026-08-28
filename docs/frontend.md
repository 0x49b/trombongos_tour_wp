# Frontend & Shortcode

Die Tourdaten werden im Frontend über einen Shortcode ausgegeben.

## Shortcode

Das Plugin registriert die Funktion `tourdaten_shortcode`
(`functions/frontend/tourdaten_shortcode.php`), die die Tourdaten als HTML rendert.

Der Shortcode ruft intern dieselbe Logik wie die [REST API](api.md) auf
(`tour_api_get_tour_data`), anstatt einen HTTP-Request zu stellen. Dadurch werden
Probleme mit Permalinks bzw. REST-URLs vermieden.

## Ausgabe

- Ausgegeben werden die Kategorien und Auftritte der **aktiven** [Saison](admin/seasons.md).
- Nur Auftritte **ab dem heutigen Datum** werden angezeigt.
- Doppelte Überschriften (z. B. „1. Wochenende") werden unterdrückt.

## Fehlerbehandlung

Wenn keine aktive Saison existiert oder die Antwort ungültig ist, gibt der Shortcode
eine Fehlermeldung aus und protokolliert Details über `error_log()`:

- `Failed to retrieve data: …` – Fehler beim Abrufen der Daten.
- `Invalid response format.` – Die Antwort hatte kein gültiges Format.
