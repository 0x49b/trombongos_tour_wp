# User Guide Overview

All management happens in the WordPress backend under the **Trombongos Tour**
menu. This section documents each admin page.

## Admin menu structure

The plugin registers a top-level **Trombongos Tour** menu with the following
sub-pages:

| Page       | Menu label   | What it manages                                   |
| ---------- | ------------ | ------------------------------------------------- |
| Overview   | Übersicht    | Landing page / summary of the tour data           |
| Events     | Auftritte    | Individual appearances ([events](events.md))      |
| Categories | Kategorien   | Groupings within a season ([categories](categories.md)) |
| Seasons    | Saisons      | Seasons ([seasons](seasons.md))                   |
| Transport  | Transport    | Transport options ([transports](transports.md))   |
| Import     | Import       | Google Sheet bulk import ([import](import.md))    |

All pages require the `manage_options` capability (administrators).

## Data model recap

```text
Season (one active at a time)
└── Category (belongs to a season, sorted)
    └── Event (belongs to a category, optionally references a Transport)
```

- A **season** groups the schedule for a period of time. Exactly one season is
  active and it is the one shown publicly.
- A **category** groups events inside a season (e.g. a tour weekend). It has its
  own visibility flag and sort order.
- An **event** (*Auftritt*) is a single appearance with a date, times, location
  and various flags.
- A **transport** is an optional means of transport that can be attached to an
  event.

Continue to the individual pages for the fields and behaviour of each entity.
