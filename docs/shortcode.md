# Shortcode

Mit dem Shortcode `[tourdaten]` lässt sich die Tourdaten-Tabelle auf einer
beliebigen WordPress-Seite oder in einem Beitrag einbinden.

## Verwendung

```text
[tourdaten]
```

## Funktionsweise

Der Shortcode ist in `functions/frontend/tourdaten_shortcode.php` implementiert
und in `tour.php` registriert:

```php
add_shortcode( 'tourdaten', 'tourdaten_shortcode' );
```

- Die Daten werden **direkt** über die API-Funktion `tour_api_get_tour_data()`
  geladen – es wird kein HTTP-Request abgesetzt. Damit werden Probleme mit
  Permalinks bzw. REST-URLs vermieden.
- Ausgegeben wird eine Bootstrap-Tabelle mit den Spalten **Datum**,
  **Anlass** und **Auftrittszeit**, überschrieben mit
  `Tourdaten <Saisonname>`.
- Es werden nur Kategorien mit `public = true` und mindestens einem Event
  angezeigt. Kategorientitel werden als Zwischenüberschriften eingefügt.
- Schlägt der API-Aufruf fehl (z. B. keine aktive Saison), wird eine
  Fehlermeldung ausgegeben und der Fehler in das PHP-Error-Log geschrieben.
