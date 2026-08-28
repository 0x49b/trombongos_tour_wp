# Configuration

Once the plugin is [installed](installation.md), a small amount of setup is
required before the tour schedule appears on the website.

## 1. Create a season

Open **Trombongos Tour → Saisons** and create a season. A season has a name, a
start date and an end date. See [Seasons](../user-guide/seasons.md) for details.

## 2. Mark a season active

Only one season is active at a time. The REST API and the `[tourdaten]`
shortcode always render the **active** season, so make sure the season you want
to publish is marked active.

## 3. Add transports (optional)

Open **Trombongos Tour → Transport** to maintain the list of transport options.
You can mark one transport as the default. See
[Transports](../user-guide/transports.md).

## 4. Add categories and events

Within the active season, create [categories](../user-guide/categories.md) and
then add [events](../user-guide/events.md) to those categories. Alternatively,
bulk import them from a Google Sheet using the
[Import](../user-guide/import.md) tool.

## 5. Publish on a page

Add the shortcode to any page or post to render the public tour dates:

```text
[tourdaten]
```

See [Frontend Shortcode](../user-guide/shortcode.md) for what gets rendered and
which events are shown.

## Visibility rules

The public output (shortcode and, for upcoming events, the REST API) only shows
content that is explicitly marked public:

- The **category** must be marked public.
- The **event** must be marked public.
- For the shortcode, the event must additionally be marked **fix** (confirmed).

Adjust these flags on each category and event to control what visitors see.
