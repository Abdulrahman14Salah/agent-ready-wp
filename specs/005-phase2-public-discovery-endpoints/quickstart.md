# Quickstart: Phase 2 Public Discovery Endpoints

## Goal

Validate public runtime publication for the Phase 2 MCP Server Card, OAuth/OIDC
discovery, and Protected Resource endpoints, including successful responses,
withheld-output cases, and physical-file conflict fallback.

## Prerequisites

- WordPress 6.0+ local environment
- PHP 8.0+
- Plugin branch `005-phase2-public-discovery-endpoints` active
- Administrator account to review the already implemented Phase 2 settings in
  `Settings > Agent Ready`
- Ability to run public HTTP requests in a browser and with `curl`

## Setup

1. Activate the plugin build containing the `005` runtime implementation.
2. In `Settings > Agent Ready`, save complete MCP Server Card values:
   - enabled
   - server name
   - version
   - transport URL
3. Enable protected APIs if OAuth discovery or Protected Resource will be
   validated.
4. Save complete OAuth settings if validating `/.well-known/openid-configuration`:
   - enabled
   - issuer
   - authorization endpoint
   - token endpoint
   - JWKS URI
5. Save complete Protected Resource settings if validating
   `/.well-known/oauth-protected-resource`:
   - enabled
   - resource URL
   - at least one authorization server URL

## Validation Scenarios

### 1. MCP Server Card positive path

1. Request `/.well-known/mcp/server-card.json`.
2. Confirm HTTP success with `Content-Type: application/json`.
3. Confirm the response includes the saved `name`, `version`, and `transport.url`.
4. Confirm the response includes a `capabilities` map reflecting currently
   active plugin features.

### 2. MCP Server Card disabled or incomplete behavior

1. Disable MCP Server Card or clear one required field.
2. Request `/.well-known/mcp/server-card.json`.
3. Confirm the plugin does not emit a generated server card document and
   default WordPress or host behavior remains authoritative.

### 3. OAuth discovery positive path

1. Ensure protected APIs are enabled and OAuth settings are complete.
2. Request `/.well-known/openid-configuration`.
3. Confirm HTTP success with `Content-Type: application/json`.
4. Confirm the response includes the saved `issuer`,
   `authorization_endpoint`, `token_endpoint`, and `jwks_uri`.

### 4. OAuth discovery inapplicable or incomplete behavior

1. Disable protected APIs, disable OAuth, or clear one required OAuth field.
2. Request `/.well-known/openid-configuration`.
3. Confirm the plugin emits no generated discovery document.

### 5. Protected Resource positive path

1. Ensure protected APIs are enabled and Protected Resource settings are
   complete.
2. Request `/.well-known/oauth-protected-resource`.
3. Confirm HTTP success with `Content-Type: application/json`.
4. Confirm the response includes the saved `resource` value and
   `authorization_servers` list.

### 6. Protected Resource disabled or incomplete behavior

1. Disable protected APIs, disable Protected Resource, remove the resource URL,
   or remove all authorization servers.
2. Request `/.well-known/oauth-protected-resource`.
3. Confirm the plugin emits no generated protected-resource document.

### 7. Physical-file conflict fallback

1. Simulate or detect a physical file conflict for one Phase 2 path under
   `ABSPATH/.well-known`.
2. Request the corresponding endpoint.
3. Confirm the plugin does not override the physical-file or host behavior.
4. Confirm unrelated Phase 2 endpoints still behave according to their own
   settings and compatibility state.

## Expected Outcome

The site publishes each Phase 2 public discovery document only when its own
saved settings are complete and applicable, emits stable JSON responses for
eligible requests, and preserves default routing or host ownership whenever an
endpoint is disabled, incomplete, or physically conflicting.

## Validation Outcomes (2026-04-25)

- Executed `php -l` across all new and changed Phase 2 runtime source and test
  files with no syntax errors.
- Executed direct bootstrap-backed smoke checks and confirmed:
  - MCP Server Card emits the saved identity fields and capability map,
  - OAuth discovery with incomplete settings returns the stable
    `settings_incomplete` fallback with no body output,
  - Protected Resource emits the saved `resource` and authorization server
    values when eligible,
  - hook registration adds the three rewrite rules and query vars.
- PHPUnit execution remains unavailable in this environment because neither
  `phpunit` nor `vendor/bin/phpunit` is installed locally.
