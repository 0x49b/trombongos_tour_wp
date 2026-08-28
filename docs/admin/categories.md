# Kategorien

Kategorien gruppieren die Auftritte innerhalb einer Saison (z. B. einzelne Wochenenden
oder Tourblöcke).

## Menü

**Trombongos Tour → Kategorien**

## Felder

| Feld         | Beschreibung                                                  |
|--------------|--------------------------------------------------------------|
| `title`      | Titel der Kategorie                                          |
| `date_start` | Startdatum der Kategorie                                     |
| `date_end`   | Enddatum der Kategorie                                       |
| `public`     | Ob die Kategorie öffentlich sichtbar ist (Standard: ja)     |
| `sort`       | Sortierreihenfolge (aufsteigend)                            |
| `season_id`  | Zugehörige Saison                                           |

## Sortierung

Kategorien werden über das Feld `sort` aufsteigend sortiert ausgegeben. Über diesen
Wert lässt sich die Reihenfolge in der Ausgabe steuern.

## Zusammenhang mit Saisons

Jede Kategorie gehört über `season_id` zu genau einer [Saison](seasons.md). In der
öffentlichen Ausgabe erscheinen nur die Kategorien der aktiven Saison.
