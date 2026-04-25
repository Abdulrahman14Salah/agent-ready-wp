# API Catalog Contract: Phase 1 Discovery Runtime

## Scope

Defines the public runtime HTTP behavior for F4 API Catalog only.

## Routing Contract

- The plugin owns `/.well-known/api-catalog` only when API Catalog runtime
  behavior is enabled and no physical-file conflict blocks plugin ownership.
- Routing is implemented through a WordPress rewrite target and runtime handler,
  not by mutating physical files.
- Non-catalog requests must fall through to normal WordPress handling.

## Response Contract (when eligible)

- Status is successful for valid enabled requests.
- `Content-Type` is `application/linkset+json; charset=utf-8`.
- Response body is a JSON object containing a top-level `links` array.
- Each emitted link item contains:
  - `anchor`
  - `href`
  - `rel` with value `service-desc`
  - a human-readable label/title field

## Entry Contract

- When enabled and selected, the catalog includes the default WordPress REST API
  entry.
- WooCommerce discovery is included only when WooCommerce is active and the Woo
  inclusion setting is enabled.
- Configured custom entries are emitted with their saved labels and service
  links.
- Incomplete or disabled custom entries are omitted.

## Fallback Contract

- If API Catalog is disabled, the plugin must not emit generated catalog output.
- If a physical `/.well-known/api-catalog` file conflict exists, the plugin must
  preserve host/default behavior and emit no overriding response.
- Fallback behavior must not mutate files or force a plugin-specific error page.

## Runtime Notes

- Runtime decisions should expose stable reasons such as `feature_disabled`,
  `not_catalog_route`, and `physical_file_conflict` for automated verification.
- Routing uses the plugin-owned query var `arwp_api_catalog` behind a WordPress
  rewrite rule for `/.well-known/api-catalog`.
- API Catalog output is derived only from saved settings and local environment
  state; no remote discovery calls occur on request.
