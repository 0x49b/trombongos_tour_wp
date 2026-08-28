# Saisons

Saisons bilden die oberste Ebene der Tourdaten. Alle Kategorien und Auftritte gehören
zu einer Saison.

## Menü

**Trombongos Tour → Saisons**

## Felder

| Feld         | Beschreibung                                          |
|--------------|------------------------------------------------------|
| `name`       | Name der Saison (max. 9 Zeichen, z. B. `2024/25`)   |
| `start_date` | Startdatum der Saison                               |
| `end_date`   | Enddatum der Saison                                 |
| `active`     | Kennzeichnet die aktive Saison                     |

## Aktive Saison

!!! important "Genau eine aktive Saison"
    Die [REST API](../api.md) und der [Shortcode](../frontend.md) geben immer die
    Daten der Saison mit `active = 1` aus. Existiert keine aktive Saison, liefert die
    API den Fehler `no_active_season` (HTTP 404).

Beim Wechsel der Saison genügt es daher, die neue Saison auf **aktiv** zu setzen –
die öffentliche Ausgabe folgt automatisch.
