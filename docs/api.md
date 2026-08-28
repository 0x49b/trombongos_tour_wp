# REST API

## Endpoint

```
GET /wp-json/tour/v1/tour
```

Public endpoint (no authentication). Returns tour data for the **active** season.

## Response

On success, the body mirrors the historical Django API shape and includes categories and events for the active season.

### Errors

| Status | Code | When |
|--------|------|------|
| 404 | `no_active_season` | No season is marked active |

## Event types

Numeric `type` values map to:

| Value | Name |
|-------|------|
| 0 | Auftritt |
| 1 | Infos |
| 2 | GV |
| 3 | Anderes |
