# Google Sheet Import

The importer bulk-loads events from a shared Google Sheet. Open it under
**Trombongos Tour → Import**.

## Overview

The import is a three-step, guided process:

1. **Paste the Google Sheet URL** – you provide the share link to the sheet.
2. **Preview and choose a season** – the plugin fetches and parses the sheet,
   shows a preview table, and lets you pick an existing season or create a new
   one.
3. **Import** – the plugin creates any missing categories and inserts the
   events.

## Preparing the Google Sheet

- The sheet must be a **native Google Sheet**, not an uploaded Excel (`.xlsx`)
  file. If the link points to an Excel file, open it in Google Drive and choose
  **File → Save as Google Sheets**, then share that copy.
- Share it so that **anyone with the link** can view it. The importer fetches
  the first tab (`gid=0`) as CSV.
- The first row must contain the column headers listed below.

## Recognised columns

The importer maps the following sheet headers to event fields. The **`Was`**
column (name) is mandatory; all others are optional.

| Sheet header (German) | Event field       | Notes                                            |
| --------------------- | ----------------- | ------------------------------------------------ |
| `Was`                 | name              | **Required.** Rows without a name are skipped.   |
| `Datum`               | date              | Parsed from `d.m.Y`.                             |
| `Art`                 | type              | Matched to Auftritt/Infos/GV/Anderes.            |
| `Ort`                 | location          | Also appended to the name and used for a maps link. |
| `Veranstalter`        | organizer         |                                                  |
| `Zeit`                | play              | Performance time.                                |
| `Besammlung`          | gathering         |                                                  |
| `Schminken`           | makeup            |                                                  |
| `Abfahrt Magazin`     | warehouse         |                                                  |
| `Sonne`               | sun               |                                                  |
| `Trailer`             | trailer           |                                                  |
| `Transport`           | transport_name    | Matched to an existing transport (by name).      |
| `Fix`                 | fix               | `x` means confirmed.                             |
| `Public`              | public            | `x` means public.                                |
| `Info`                | info              |                                                  |
| `Tagesinfo`           | category_name     | Determines the category (created if missing).    |

## How data is processed

- **Categories** are derived from the `Tagesinfo` column. Events are grouped by
  category; each category's start/end dates are set to the earliest/latest event
  date in that group, and categories are sorted by their earliest date.
- **Event ordering** within a category is by date and then performance time.
- **Weekday** (`day`) is derived automatically from the date when not supplied.
- **Location** is appended to the event name, and a Google Maps search URL is
  generated from the location.
- **Types** are mapped from the text values `auftritt`, `infos`, `gv`,
  `anderes` (case-insensitive) to their numeric values, defaulting to
  `Auftritt`.
- **`Fix` / `Public`** are treated as boolean: the value `x` means true.
- **Transport** is matched case-insensitively against existing transports.
  Unknown values are ignored (no transport is set).

## Season selection

In the preview step you can:

- assign the events to an **existing season**, or
- create a **new season** by name. A newly created season defaults to a
  January–December date range for the current year, which you can adjust
  afterwards under [Seasons](seasons.md).

Existing categories in the chosen season are reused (matched by title) so that
re-importing does not create duplicate categories.

!!! note
    The parsed preview data is stored temporarily (for one hour) before you
    confirm the import. If it expires, simply start the process again.
