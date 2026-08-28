# Transports

Transports are optional means of transport that can be attached to an
[event](events.md). Manage them under **Trombongos Tour → Transport**.

## Fields

| Field     | Description                                        |
| --------- | ------------------------------------------------- |
| `name`    | Name of the transport option.                     |
| `default` | Whether this transport is the default choice.     |

## Default transport

You can mark exactly one transport as the default. The default transport is
available through the helper `tour_get_default_transport()` and can be used as a
sensible pre-selected value when creating new events.

!!! note
    The `default` column was added by a
    [database migration](../reference/migrations.md). The plugin checks whether
    the column exists before querying it, so older databases continue to work
    until the migration runs.

## Using transports on events

Each event may reference a transport via `transport_id`. In the
[REST API](../reference/rest-api.md) output the transport's `name` is exposed on
each event as the `transport` field. Events without a transport report `null`.

When importing from a Google Sheet, the `Transport` column is matched against
existing transport names (case-insensitively). Unmatched values leave the event
without a transport rather than creating a new transport entry — create the
transport first if you want the match to succeed. See [Import](import.md).
