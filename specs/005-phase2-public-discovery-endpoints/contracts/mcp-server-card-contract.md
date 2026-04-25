# MCP Server Card Contract

## Endpoint

- Path: `/.well-known/mcp/server-card.json`
- Method: `GET`
- Success status: `200`
- Content-Type: `application/json; charset=utf-8`

## Publication Rules

- The endpoint publishes only when:
  - MCP Server Card is enabled in saved settings.
  - Saved `name`, `version`, and `transport` values are all present.
  - No physical-file conflict exists at `ABSPATH/.well-known/mcp/server-card.json`.
- If any rule fails, the plugin emits no generated response and default
  WordPress or host behavior remains authoritative.

## Success Response

```json
{
  "name": "Example MCP Server",
  "version": "1.0.0",
  "transport": {
    "url": "https://example.com/wp-json/agent-ready/v1/mcp"
  },
  "capabilities": {
    "api_catalog": true,
    "webmcp": true,
    "oauth_discovery": false,
    "protected_resource": false
  }
}
```

## Field Contract

- `name`: saved MCP Server Card name
- `version`: saved MCP Server Card version
- `transport.url`: saved MCP Server Card transport URL
- `capabilities.api_catalog`: `true` only when the API Catalog runtime is
  currently publishable
- `capabilities.webmcp`: `true` only when WebMCP is currently enabled and
  runtime-eligible
- `capabilities.oauth_discovery`: `true` only when OAuth discovery is
  currently publishable
- `capabilities.protected_resource`: `true` only when Protected Resource is
  currently publishable

## Fallback Contract

- Disabled feature -> no generated document
- Missing required MCP values -> no generated document
- Physical-file conflict -> no generated document
- One capability becoming unavailable must not suppress the server card when the
  required MCP identity fields are still complete

## Validation Notes (2026-04-25)

- Direct bootstrap-backed smoke validation confirms the endpoint emits the saved
  identity fields and capability map when eligible.
- Conflict and disabled/incomplete scenarios short-circuit without generated
  output or file mutation.
