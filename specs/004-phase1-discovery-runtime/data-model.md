# Data Model: Phase 1 Discovery Runtime

## Entity: DiscoveryRuntimeSettings

Represents the subset of persisted plugin settings consumed by F4/F5 runtime
behavior.

### Fields

- `api_catalog.enabled`: `bool`
- `api_catalog.include_wp_rest`: `bool`
- `api_catalog.include_woo_rest`: `bool`
- `api_catalog.custom_entries`: `ApiCatalogEntry[]`
- `webmcp.enabled`: `bool`
- `webmcp.tools.search`: `bool`
- `webmcp.tools.get_posts`: `bool`
- `webmcp.tools.get_page`: `bool`
- `webmcp.tools.get_products`: `bool`

### Validation Rules

- Missing keys resolve to safe defaults before runtime evaluation.
- Custom API Catalog entries must already be sanitized before runtime use.
- WooCommerce-only settings are effective only when WooCommerce is active.

## Entity: ApiCatalogRequestContext

Represents request state used to decide whether the plugin should emit an API
Catalog response.

### Fields

- `is_catalog_route`: `bool`
- `feature_enabled`: `bool`
- `physical_file_conflict`: `bool`
- `site_url`: `string`
- `rest_root`: `string`
- `woocommerce_active`: `bool`

### Validation Rules

- API Catalog handling applies only when the request resolves to the plugin's
  rewrite target.
- Physical-file conflicts force fallback to host/default behavior.
- Disabled feature state forces fallback with no generated response.

## Entity: ApiCatalogEntry

Represents one normalized discovery link in the published catalog.

### Fields

- `name`: `string`
- `anchor`: `string`
- `href`: `string`
- `rel`: `'service-desc'`
- `title`: `string`
- `source`: `'wordpress'|'woocommerce'|'custom'`

### Validation Rules

- `anchor` and `href` must be valid absolute URLs.
- `title` may reuse the human-readable configured name.
- WooCommerce entries are emitted only when both compatibility and settings
  allow them.

## Entity: ApiCatalogOutputDocument

Represents the HTTP payload returned from `/.well-known/api-catalog`.

### Fields

- `content_type`: `application/linkset+json; charset=utf-8`
- `status`: `200`
- `links`: `ApiCatalogEntry[]`

### Validation Rules

- The document must contain the default WordPress REST entry when enabled and
  selected.
- Custom entries with incomplete data are omitted.
- The response is emitted only when the route and compatibility state are both
  eligible.

## Entity: ApiCatalogResolutionDecision

Represents the evaluated outcome of API Catalog runtime handling.

### Fields

- `applies`: `bool`
- `reason`: `'eligible'|'feature_disabled'|'not_catalog_route'|'physical_file_conflict'`
- `entries`: `ApiCatalogEntry[]`

### State Transitions

- Incoming request context -> decision evaluation
- Eligible decision -> API Catalog response emission
- Non-eligible decision -> default WordPress/host handling

## Entity: WebMcpRuntimeContext

Represents the public frontend state used to decide whether WebMCP output should
be enqueued.

### Fields

- `is_public_frontend`: `bool`
- `feature_enabled`: `bool`
- `woocommerce_active`: `bool`
- `selected_tools`: `array<string,bool>`

### Validation Rules

- WebMCP output is considered only for public frontend requests.
- Disabled feature state prevents any runtime asset emission.
- WooCommerce inactivity removes `get_products` even if previously selected.

## Entity: WebMcpToolRegistrationPayload

Represents the browser-consumed payload used by the WebMCP runtime asset.

### Fields

- `tools`: `array<int,WebMcpToolDefinition>`
- `site_url`: `string`
- `rest_root`: `string`
- `capability_check`: `string`

### Validation Rules

- Payload includes only tools enabled by saved settings and runtime
  compatibility.
- Tool identifiers remain stable across page loads.
- Payload must be serializable without leaking admin-only or privileged data.

## Entity: WebMcpToolDefinition

Represents one frontend-exposed WebMCP tool.

### Fields

- `name`: `'search'|'get_posts'|'get_page'|'get_products'`
- `label`: `string`
- `route`: `string`
- `enabled`: `bool`
- `requires_woocommerce`: `bool`

### Validation Rules

- `get_products` requires WooCommerce and must be omitted otherwise.
- Non-Woo tools remain eligible regardless of WooCommerce state.

## Entity: WebMcpEmissionDecision

Represents the evaluated outcome of WebMCP runtime emission.

### Fields

- `applies`: `bool`
- `reason`: `'eligible'|'feature_disabled'|'unsupported_context'|'no_tools_enabled'`
- `tools`: `array<int,WebMcpToolDefinition>`

### State Transitions

- Frontend request context -> emission decision
- Eligible decision -> runtime asset enqueue + localized payload
- Non-eligible decision -> no frontend discovery output

## Entity: DiscoveryCompatibilityState

Represents environment signals that affect F4/F5 behavior.

### Fields

- `woocommerce_active`: `bool`
- `api_catalog_file_conflict`: `bool`
- `wp_head_supported`: `bool`
- `warnings`: `array<int,array<string,mixed>>`

### Validation Rules

- Physical file conflicts prevent API Catalog output without mutating files.
- WooCommerce absence removes Woo-dependent discovery behavior.
- Compatibility warnings remain reusable for diagnostics outside this phase.

## Entity: DiscoveryContractTestCase

Represents executable verification scenarios for discovery runtime behavior.

### Fields

- `scenario_key`: `string`
- `input_context`: `ApiCatalogRequestContext|WebMcpRuntimeContext`
- `expected_contract`: `ApiCatalogOutputDocument|WebMcpToolRegistrationPayload|null`
- `expected_reason`: `string`
- `expected_fallback`: `bool`

### Purpose

- Links feature requirements to automated unit and integration coverage.
- Ensures enabled, disabled, WooCommerce-conditional, and compatibility
  fallbacks remain regression-safe.
