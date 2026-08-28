# Trombongos Tour Plugin

Willkommen zur Dokumentation des **Trombongos Tour Plugins** – einem WordPress-Plugin,
das die Tourdaten des Vereins verwaltet und öffentlich zur Verfügung stellt.

Das Plugin stellt die Tourdaten bereit. Diese können über das WordPress-Backend
bearbeitet werden und werden den Mitgliedern unter der URL
[tour.trombongos.ch](https://tour.trombongos.ch) zur Verfügung gestellt.

## Funktionsüberblick

- **Saisonverwaltung** – Tourdaten werden pro Saison organisiert; genau eine Saison ist aktiv.
- **Kategorien** – Gruppieren die Auftritte innerhalb einer Saison (z. B. Wochenenden).
- **Auftritte** – Einzelne Events mit Datum, Zeiten, Ort, Transport und weiteren Angaben.
- **Transport** – Wiederverwendbare Transportmittel mit optionalem Standardwert.
- **Import** – Bestehende Daten lassen sich importieren.
- **REST API** – Öffentlicher Endpunkt unter `/wp-json/tour/v1/tour`.
- **Shortcode** – Ausgabe der Tourdaten direkt in einer WordPress-Seite.

## Diese Dokumentation

Diese Dokumentation wird mit [MkDocs](https://www.mkdocs.org/) erstellt und verwendet das
[GitBook-Theme](https://gitlab.com/lramage/mkdocs-gitbook-theme).

- Für die **Installation** des Plugins siehe [Installation](installation.md).
- Für die tägliche **Verwaltung** siehe [Administration](admin/overview.md).
- Für Entwickler:innen siehe [REST API](api.md), [Datenbank](database.md) und
  [Entwicklung](development.md).

!!! info "Plugin-Version"
    Diese Dokumentation bezieht sich auf das Trombongos Tour Plugin **Version 3.0**.
