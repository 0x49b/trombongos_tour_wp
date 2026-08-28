# Installation

Das Trombongos Tour Plugin ist ein klassisches WordPress-Plugin.

## Voraussetzungen

- WordPress mit Zugriff auf das Plugin-Verzeichnis (`wp-content/plugins/`)
- PHP 7.4 oder neuer
- MySQL/MariaDB (die Datenbanktabellen werden bei der Aktivierung automatisch erstellt)

## Installation der Plugin-Dateien

1. Die Plugin-Dateien in das Verzeichnis
   `wp-content/plugins/trombongos_tour_wp/` kopieren.
2. Im WordPress-Backend unter **Plugins** das Plugin **Trombongos Tour Plugin** aktivieren.

Beim Aktivieren legt das Plugin automatisch die benötigten Datenbanktabellen an
(siehe [Datenbank](database.md)).

## Automatisches Deployment

Das Repository enthält GitHub-Actions-Workflows, die das Plugin per FTP auf die
Test- bzw. Produktivumgebung übertragen:

- `.github/workflows/deploy-test.yml` – Deployment auf die Testumgebung (bei Push/PR auf `master`).
- `.github/workflows/deploy-prod.yml` – manuelles Deployment auf die Produktivumgebung
  (`workflow_dispatch`, mit Dry-Run-Option).

Die Zielverzeichnisse und Zugangsdaten werden über GitHub-Secrets konfiguriert.

## Erste Schritte nach der Aktivierung

1. Eine [Saison](admin/seasons.md) anlegen und auf **aktiv** setzen.
2. [Kategorien](admin/categories.md) für die Saison erstellen.
3. [Auftritte](admin/events.md) zu den Kategorien hinzufügen.
4. Optional [Transportmittel](admin/transports.md) definieren.
5. Die Tourdaten über den [Shortcode](frontend.md) oder die [REST API](api.md) ausgeben.
