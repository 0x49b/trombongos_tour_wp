# Auftritte

Auftritte (Events) sind die einzelnen Termine innerhalb einer Kategorie.

## Menü

**Trombongos Tour → Auftritte**

## Felder

| Feld         | Beschreibung                                                        |
|--------------|--------------------------------------------------------------------|
| `name`       | Bezeichnung des Auftritts                                          |
| `date`       | Datum des Auftritts                                                |
| `day`        | Wochentag (wird als Name ausgegeben)                              |
| `sort`       | Sortierreihenfolge innerhalb desselben Datums                    |
| `type`       | Typ des Termins (siehe unten)                                     |
| `organizer`  | Veranstalter / Organisator                                        |
| `location`   | Ort des Auftritts                                                 |
| `maps_url`   | Link zu einer Karte (z. B. Google Maps)                          |
| `play`       | Spielbeginn (Uhrzeit)                                            |
| `gathering`  | Besammlung (Uhrzeit)                                             |
| `makeup`     | Maske / Vorbereitung (Uhrzeit)                                   |
| `warehouse`  | Lager (Uhrzeit)                                                  |
| `sun`        | Sonnenzeit / weitere Uhrzeit                                     |
| `trailer`    | Anhänger-Information                                             |
| `transport`  | Referenziertes [Transportmittel](transports.md)                 |
| `fix`        | Ob der Termin fix ist                                            |
| `public`     | Ob der Termin öffentlich sichtbar ist                           |
| `info`       | Zusätzliche Informationen                                        |

## Typen

Der `type` wird intern als Zahl gespeichert und in der Ausgabe in einen Namen übersetzt:

| Wert | Name       |
|------|------------|
| 0    | Auftritt   |
| 1    | Infos      |
| 2    | GV         |
| 3    | Anderes    |

## Sortierung und Anzeige

- Auftritte werden nach `date` (aufsteigend) und innerhalb desselben Tages nach `sort`
  sortiert.
- In der öffentlichen Ausgabe werden nur Auftritte **ab dem heutigen Datum**
  (`date >= CURDATE()`) berücksichtigt.
- Das von der API berechnete Feld `firstOnDay` kennzeichnet den ersten Auftritt eines
  Tages und hilft bei der Gruppierung in der Anzeige.
