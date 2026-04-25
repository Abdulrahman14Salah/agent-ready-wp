# Data Model: Phase 2 Public Discovery Endpoints

## Entity: PhaseTwoDiscoverySettings

Represents the subset of persisted plugin settings consumed by the Phase 2
runtime endpoints.

### Fields

- `mcp_server_card.enabled`: `bool`
- `mcp_server_card.name`: `string`
- `mcp_server_card.version`: `string`
- `mcp_server_card.transport`: `string`
- `protected_apis.enabled`: `bool`
- `oauth.enabled`: `bool`
- `oauth.issuer`: `string`
- `oauth.authorization_endpoint`: `string`
- `oauth.token_endpoint`: `string`
- `oauth.jwks_uri`: `string`
- `protected_resource.enabled`: `bool`
- `protected_resource.resource`: `string`
- `protected_resource.authorization_servers`: `string[]`

### Validation Rules

- Missing keys resolve to safe defaults before runtime evaluation.
- Runtime behavior consumes the normalized option shape already enforced by the
  settings repository and sanitizer.
- Protected API metadata is effective only when `protected_apis.enabled` is
  `true`.

## Entity: PhaseTwoCompatibilityState

Represents environment signals that affect publication of the three public
discovery endpoints.

### Fields

- `mcp_server_card_file_conflict`: `bool`
- `openid_configuration_file_conflict`: `bool`
- `oauth_protected_resource_file_conflict`: `bool`
- `mcp_server_card_runtime_available`: `bool`
- `openid_configuration_runtime_available`: `bool`
- `oauth_protected_resource_runtime_available`: `bool`
- `warnings`: `array<int,array<string,mixed>>`

### Validation Rules

- A physical-file conflict for one endpoint must not disable unrelated Phase 2
  endpoints.
- Runtime availability is `false` when a conflicting physical file is present.
- Compatibility warnings remain reusable by runtime tests and future admin
  diagnostics.

## Entity: McpServerCardRequestContext

Represents the request state used to decide whether the MCP Server Card should
be emitted.

### Fields

- `is_server_card_route`: `bool`
- `feature_enabled`: `bool`
- `runtime_available`: `bool`
- `physical_file_conflict`: `bool`
- `name`: `string`
- `version`: `string`
- `transport`: `string`
- `api_catalog_available`: `bool`
- `webmcp_available`: `bool`
- `oauth_discovery_available`: `bool`
- `protected_resource_available`: `bool`

### Validation Rules

- The document is eligible only on the exact plugin-owned route.
- `name`, `version`, and `transport` must all be non-empty for publication.
- Capability flags reflect active plugin features, not merely stored values.

## Entity: McpServerCardDocument

Represents the JSON payload returned from
`/.well-known/mcp/server-card.json`.

### Fields

- `content_type`: `application/json; charset=utf-8`
- `status`: `200`
- `name`: `string`
- `version`: `string`
- `transport`: `array{url: string}`
- `capabilities`: `McpServerCardCapabilities`

### Validation Rules

- The document must contain the saved identity values from settings.
- The `transport.url` value must be the saved transport URL.
- The capability map must expose only features currently available at runtime.

## Entity: McpServerCardCapabilities

Represents the active feature metadata auto-detected for the MCP Server Card.

### Fields

- `api_catalog`: `bool`
- `webmcp`: `bool`
- `oauth_discovery`: `bool`
- `protected_resource`: `bool`

### Validation Rules

- Each capability is derived from runtime eligibility or enablement, not from
  assumptions.
- One capability toggling off must not alter the saved identity fields.

## Entity: OAuthDiscoveryRequestContext

Represents the request state used to decide whether the OAuth/OIDC discovery
document should be emitted.

### Fields

- `is_openid_configuration_route`: `bool`
- `protected_apis_enabled`: `bool`
- `feature_enabled`: `bool`
- `runtime_available`: `bool`
- `physical_file_conflict`: `bool`
- `issuer`: `string`
- `authorization_endpoint`: `string`
- `token_endpoint`: `string`
- `jwks_uri`: `string`

### Validation Rules

- OAuth discovery applies only when protected APIs are applicable.
- All four saved OAuth values must be non-empty for publication.
- Physical-file conflicts force fallback to host or default behavior.

## Entity: OAuthDiscoveryDocument

Represents the JSON payload returned from
`/.well-known/openid-configuration`.

### Fields

- `content_type`: `application/json; charset=utf-8`
- `status`: `200`
- `issuer`: `string`
- `authorization_endpoint`: `string`
- `token_endpoint`: `string`
- `jwks_uri`: `string`

### Validation Rules

- The document must emit only the saved OAuth discovery values.
- The response is emitted only when the route, applicability, completeness, and
  compatibility state are all eligible.

## Entity: ProtectedResourceRequestContext

Represents the request state used to decide whether the protected-resource
document should be emitted.

### Fields

- `is_protected_resource_route`: `bool`
- `protected_apis_enabled`: `bool`
- `feature_enabled`: `bool`
- `runtime_available`: `bool`
- `physical_file_conflict`: `bool`
- `resource`: `string`
- `authorization_servers`: `string[]`

### Validation Rules

- Protected Resource applies only when protected APIs are applicable.
- `resource` must be non-empty.
- `authorization_servers` must contain at least one entry.

## Entity: ProtectedResourceDocument

Represents the JSON payload returned from
`/.well-known/oauth-protected-resource`.

### Fields

- `content_type`: `application/json; charset=utf-8`
- `status`: `200`
- `resource`: `string`
- `authorization_servers`: `string[]`

### Validation Rules

- The document must contain only the saved protected-resource values.
- Incomplete or conflicting configurations must emit no generated response.

## Entity: PhaseTwoEndpointDecision

Represents the evaluated outcome of one Phase 2 endpoint request.

### Fields

- `applies`: `bool`
- `reason`: `'eligible'|'feature_disabled'|'settings_incomplete'|'protected_apis_disabled'|'not_request_target'|'physical_file_conflict'`
- `document`: `array<string,mixed>|null`

### State Transitions

- Incoming request context -> endpoint matcher evaluation
- Eligible decision -> document builder + JSON response writer
- Non-eligible decision -> default WordPress or host handling

## Entity: PhaseTwoContractTestCase

Represents executable verification scenarios for the public discovery contracts.

### Fields

- `scenario_key`: `string`
- `endpoint`: `'mcp_server_card'|'oauth_discovery'|'protected_resource'`
- `input_context`: `McpServerCardRequestContext|OAuthDiscoveryRequestContext|ProtectedResourceRequestContext`
- `expected_reason`: `string`
- `expected_document`: `array<string,mixed>|null`
- `expected_fallback`: `bool`

### Purpose

- Links feature requirements to automated unit and integration coverage.
- Ensures enabled, disabled, incomplete, and physical-file conflict scenarios
  remain regression-safe for each endpoint independently.
