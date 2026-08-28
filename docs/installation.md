# Installation & Deployment

## Voraussetzungen

- WordPress-Installation mit MySQL/MariaDB
- Schreibrechte auf das Verzeichnis `wp-content/plugins/`

## Installation

1. Repository in das Plugin-Verzeichnis kopieren:

    ```bash
    cd wp-content/plugins
    git clone https://github.com/0x49b/trombongos_tour_wp.git trombongos_tour_wp
    ```

2. Das Plugin **Trombongos Tour Plugin** im WordPress-Backend unter
   *Plugins* aktivieren.

Bei der Aktivierung legt das Plugin seine Datenbanktabellen an und führt alle
ausstehenden Migrationen aus (siehe
[Datenbank & Migrationen](database.md)).

## Deployment über GitHub Actions

Das Repository enthält zwei Workflows, die das Plugin per FTP auf die
Zielumgebungen synchronisieren:

| Workflow | Datei | Ziel |
|----------|-------|------|
| Deploy PROD | `.github/workflows/deploy-prod.yml` | Produktionswebsite |
| Deploy TEST | `.github/workflows/deploy-test.yml` | Testumgebung |

Beide Workflows werden manuell über *workflow_dispatch* gestartet und bieten
eine `dryRun`-Option, mit der der Sync zunächst nur simuliert wird. Die
Zugangsdaten (`SERVER`, `USER_PROD`, `PASSWORD_PROD`, ...) sind als GitHub
Secrets hinterlegt.

## Dokumentation bauen

Die Dokumentation wird mit MkDocs und dem GitBook-Theme generiert:

```bash
pip install -r docs/requirements.txt

# Lokaler Entwicklungsserver mit Live-Reload
mkdocs serve

# Statische Site in das Verzeichnis site/ bauen
mkdocs build
```
