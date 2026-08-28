# Getting started

## Requirements

- WordPress with a MySQL/MariaDB database
- PHP compatible with your WordPress version

## Installation

1. Copy this repository into `wp-content/plugins/trombongos_tour_wp/`
2. Activate **Trombongos Tour Plugin** in the WordPress admin
3. On activation the plugin creates its database tables and runs pending migrations

## Local documentation preview

Install dependencies and start the MkDocs development server:

```shell
pip install -r requirements-docs.txt
mkdocs serve
```

Build a static site into `site/`:

```shell
mkdocs build
```
