# Transport

Transportmittel sind wiederverwendbare Einträge, die einem [Auftritt](events.md)
zugeordnet werden können.

## Menü

**Trombongos Tour → Transport**

## Felder

| Feld       | Beschreibung                                                |
|------------|------------------------------------------------------------|
| `name`     | Bezeichnung des Transportmittels                          |
| `default`  | Kennzeichnet das Standard-Transportmittel                 |

## Standard-Transport

Über das Feld `default` lässt sich ein Transportmittel als Standard markieren. Dies
ist praktisch, wenn die meisten Auftritte dasselbe Transportmittel verwenden.

In der [REST API](../api.md) wird pro Auftritt der Name des zugeordneten
Transportmittels als Feld `transport` ausgegeben.
