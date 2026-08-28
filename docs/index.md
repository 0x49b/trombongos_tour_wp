# Trombongos Tour Plugin

Welcome to the documentation for the **Trombongos Tour Plugin**, a WordPress
plugin that manages and publishes tour dates (*Tourdaten*) for the members of
Trombongos.

The plugin provides an administration area inside the WordPress backend where
you can maintain seasons, categories, events and transports, a bulk import from
a Google Sheet, a public REST API and a shortcode for rendering the current
tour schedule on the website.

## Features

- **Season management** – organise the schedule into seasons and mark the one
  that is currently active.
- **Categories** – group events (for example by tour weekend) within a season.
- **Events (Auftritte)** – store all details of a single appearance, including
  dates, times, location, organizer, transport and visibility flags.
- **Transports** – maintain a list of transport options and a default choice.
- **Google Sheet import** – preview and import events directly from a shared
  Google Sheet.
- **REST API** – a public endpoint (`/wp-json/tour/v1/tour`) returning the
  active season and its upcoming public events as JSON.
- **Shortcode** – `[tourdaten]` renders a formatted table of the public tour
  dates on any page or post.

## How the pieces fit together

```text
Season (active)
└── Category (sorted)
    └── Event (Auftritt)  ──▶  Transport (optional)
```

An **event** always belongs to a **category**, and a category always belongs to
a **season**. Only one season is active at a time; the REST API and the
shortcode always render the active season.

## Quick links

- New to the plugin? Start with [Installation](getting-started/installation.md).
- Managing content? See the [User Guide](user-guide/overview.md).
- Integrating with the data? See the [REST API reference](reference/rest-api.md).
- Contributing? See [Project Structure](development/project-structure.md).

## Plugin metadata

| Field   | Value                          |
| ------- | ------------------------------ |
| Name    | Trombongos Tour Plugin         |
| Version | 3.0                            |
| Author  | Florian Thiévent               |
| Type    | WordPress plugin (PHP)         |
