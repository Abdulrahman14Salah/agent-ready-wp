# OAuth Discovery Contract

## Endpoint

- Path: `/.well-known/openid-configuration`
- Method: `GET`
- Success status: `200`
- Content-Type: `application/json; charset=utf-8`

## Publication Rules

- The endpoint publishes only when:
  - protected APIs are enabled in saved settings,
  - OAuth discovery is enabled in saved settings,
  - saved `issuer`, `authorization_endpoint`, `token_endpoint`, and `jwks_uri`
    values are all present,
  - no physical-file conflict exists at
    `ABSPATH/.well-known/openid-configuration`.
- If any rule fails, the plugin emits no generated response and default
  WordPress or host behavior remains authoritative.

## Success Response

```json
{
  "issuer": "https://example.com",
  "authorization_endpoint": "https://auth.example.com/authorize",
  "token_endpoint": "https://auth.example.com/token",
  "jwks_uri": "https://auth.example.com/.well-known/jwks.json"
}
```

## Field Contract

- `issuer`: saved OAuth issuer URL
- `authorization_endpoint`: saved authorization endpoint URL
- `token_endpoint`: saved token endpoint URL
- `jwks_uri`: saved JWKS URI

## Fallback Contract

- Protected APIs disabled -> no generated document
- OAuth discovery disabled -> no generated document
- Missing required OAuth values -> no generated document
- Physical-file conflict -> no generated document

## Validation Notes (2026-04-25)

- Direct bootstrap-backed smoke validation confirms incomplete OAuth settings
  short-circuit with the stable `settings_incomplete` reason and no body output.
- Integration coverage now targets eligible, disabled, incomplete, and
  physical-file conflict paths for this endpoint.
