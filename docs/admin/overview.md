# Administration – Übersicht

Nach der Aktivierung erscheint im WordPress-Backend in der linken Spalte der Menüpunkt
**Trombongos Tour** (Icon: Kalender). Er enthält die folgenden Unterseiten:

| Menüpunkt   | Beschreibung                                              | Seite |
|-------------|----------------------------------------------------------|-------|
| Übersicht   | Startseite mit Zusammenfassung der Tourdaten             | – |
| Auftritte   | Verwaltung einzelner Events                              | [Auftritte](events.md) |
| Kategorien  | Gruppierung der Auftritte innerhalb einer Saison        | [Kategorien](categories.md) |
| Saisons     | Verwaltung der Saisons (eine aktive Saison)             | [Saisons](seasons.md) |
| Transport   | Wiederverwendbare Transportmittel                       | [Transport](transports.md) |
| Import      | Import bestehender Tourdaten                             | [Import](import.md) |

Alle Seiten erfordern die Berechtigung `manage_options` (üblicherweise Administrator:innen).

## Datenmodell

Die Daten sind hierarchisch aufgebaut:

```
Saison (aktiv)
└── Kategorie (nach "sort" sortiert)
    └── Auftritt / Event (nach Datum und "sort" sortiert)
        └── Transport (referenziert)
```

- Eine **Saison** enthält mehrere **Kategorien**.
- Eine **Kategorie** enthält mehrere **Auftritte**.
- Jeder **Auftritt** kann ein **Transportmittel** referenzieren.

Die [REST API](../api.md) und der [Shortcode](../frontend.md) geben immer die Daten der
**aktiven Saison** aus.
