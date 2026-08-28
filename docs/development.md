# Entwicklung

## Projektstruktur

```
.
├── tour.php                     # Haupt-Plugin-Datei (Hooks, Menüs, Aktivierung)
├── assets/
│   └── css/
│       └── admin.css            # Styles für das Backend
├── functions/
│   ├── tour-helpers.php         # Gemeinsame Helfer (Datum, Zeit, Namen)
│   ├── api/
│   │   └── tour-api.php         # REST-API-Endpunkt
│   ├── backend/                 # Admin-Seiten (Saisons, Kategorien, Auftritte, …)
│   └── frontend/
│       └── tourdaten_shortcode.php  # Shortcode-Ausgabe
├── db_migration/                # SQL-Migrationsskripte
└── docs/                        # Diese Dokumentation (MkDocs)
```

## Gemeinsame Helfer

`functions/tour-helpers.php` stellt u. a. bereit:

- `tour_format_date($date)` – formatiert ein Datum als `d.m.Y`.
- `tour_format_date_range($start, $end)` – formatiert eine Datumsspanne.
- `tour_format_time($time)` – formatiert eine Zeit als `H:i`.
- `tour_get_day_name($day)` – wandelt eine Wochentags-Zahl in den Namen um.

Die Helfer sind mit `function_exists()` abgesichert, um Konflikte zu vermeiden.

## Dokumentation bauen

Diese Dokumentation wird mit [MkDocs](https://www.mkdocs.org/) und dem
[GitBook-Theme](https://gitlab.com/lramage/mkdocs-gitbook-theme) erstellt.

### Voraussetzungen installieren

```bash
pip install -r requirements-docs.txt
```

### Lokale Vorschau

```bash
mkdocs serve
```

Die Vorschau ist anschliessend unter <http://127.0.0.1:8000> erreichbar.

### Statische Site bauen

```bash
mkdocs build
```

Das Ergebnis liegt im Verzeichnis `site/` (nicht eingecheckt).

## Theme: GitBook

Das Theme wird in der `mkdocs.yml` konfiguriert:

```yaml
theme:
  name: gitbook
```

Das Paket wird von PyPI als `mkdocs-gitbook` installiert. Weitere Informationen und
der Quellcode des Themes finden sich unter
<https://gitlab.com/lramage/mkdocs-gitbook-theme>.

## Automatisches Deployment der Doku

Der Workflow `.github/workflows/docs.yml` baut die Dokumentation bei jedem Push auf
`master` und veröffentlicht sie über GitHub Pages.
