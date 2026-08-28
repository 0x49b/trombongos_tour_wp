# Trombongos Tour Plugin

WordPress plugin that manages tour data for [Trombongos](http://www.trombongos.ch): seasons, categories, transports, and events.

Tour data can be edited in the WordPress admin and exposed to members via the frontend shortcode and a public REST API.

## Features

- Admin screens for overview, events, categories, seasons, transports, and CSV import
- Database tables created on activation, with SQL migrations on update
- Public REST endpoint at `/wp-json/tour/v1/tour`
- Frontend shortcode `[tourdaten]` for rendering the active season

## Documentation

This site is built with [MkDocs](https://www.mkdocs.org/) using the [GitBook theme](https://gitlab.com/lramage/mkdocs-gitbook-theme).

```shell
pip install -r requirements-docs.txt
mkdocs serve
```

Open http://127.0.0.1:8000 to preview the docs locally.
