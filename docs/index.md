# Trombongos Tour Plugin

Das **Trombongos Tour Plugin** ist ein WordPress-Plugin, das die Tourdaten der
[Trombongos](http://www.trombongos.ch) verwaltet und bereitstellt. Die Daten
können über das WordPress-Backend gepflegt werden und stehen den Mitgliedern
über eine REST API sowie einen Shortcode zur Verfügung.

## Funktionsumfang

- **Admin-Backend** – Verwaltung von Auftritten, Kategorien, Saisons und
  Transporten direkt im WordPress-Adminbereich, inklusive Import-Funktion.
- **REST API** – Öffentlicher Endpunkt `GET /wp-json/tour/v1/tour`, der die
  Tourdaten der aktiven Saison als JSON liefert.
- **Shortcode** – Mit `[tourdaten]` lässt sich die Tourdaten-Tabelle auf einer
  beliebigen Seite einbinden.
- **Datenbank-Migrationen** – Das Plugin legt bei der Aktivierung seine
  Tabellen an und führt SQL-Migrationen automatisch aus.

## Projektstruktur

```text
trombongos_tour_wp/
├── tour.php                  # Plugin-Einstiegspunkt (Hooks, Menüs, Migrationen)
├── assets/css/               # Styles für das Admin-Backend
├── db_migration/             # SQL-Migrationen (werden automatisch ausgeführt)
└── functions/
    ├── tour-helpers.php      # Gemeinsame Hilfsfunktionen
    ├── api/tour-api.php      # REST-API-Endpunkt
    ├── backend/              # Admin-Seiten (Auftritte, Kategorien, ...)
    └── frontend/             # Shortcode [tourdaten]
```

## Dokumentation

Diese Dokumentation wird mit [MkDocs](https://www.mkdocs.org/) generiert und
verwendet das [GitBook-Theme](https://gitlab.com/lramage/mkdocs-gitbook-theme).
Lokal lässt sie sich wie folgt bauen:

```bash
pip install -r docs/requirements.txt
mkdocs serve
```
