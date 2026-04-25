# Protected Resource Contract

## Endpoint

- Path: `/.well-known/oauth-protected-resource`
- Method: `GET`
- Success status: `200`
- Content-Type: `application/json; charset=utf-8`

## Publication Rules

- The endpoint publishes only when:
  - protected APIs are enabled in saved settings,
  - Protected Resource is enabled in saved settings,
  - the saved `resource` value is present,
  - at least one saved `authorization_servers` entry is present,
  - no physical-file conflict exists at
    `ABSPATH/.well-known/oauth-protected-resource`.
- If any rule fails, the plugin emits no generated response and default
  WordPress or host behavior remains authoritative.

## Success Response

```json
{
  "resource": "https://example.com/wp-json/agent-ready/v1",
  "authorization_servers": [
    "https://auth.example.com"
  ]
}
```

## Field Contract

- `resource`: saved protected-resource URL
- `authorization_servers`: saved authorization server URL list in normalized
  order

## Fallback Contract

- Protected APIs disabled -> no generated document
- Protected Resource disabled -> no generated document
- Missing `resource` -> no generated document
- Empty `authorization_servers` -> no generated document
- Physical-file conflict -> no generated document

## Validation Notes (2026-04-25)

- Direct bootstrap-backed smoke validation confirms eligible requests emit the
  saved `resource` and `authorization_servers` values.
- Integration coverage now targets eligible, disabled, incomplete, and
  physical-file conflict paths for this endpoint.
