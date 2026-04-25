# Research: Phase 2 Public Discovery Endpoints

## Decision 1: Serve each Phase 2 endpoint through rewrite + query-var routing

**Decision**: Implement `/.well-known/mcp/server-card.json`,
`/.well-known/openid-configuration`, and
`/.well-known/oauth-protected-resource` with WordPress rewrite rules mapped to
plugin-owned query vars, then emit responses during `template_redirect`.

**Rationale**: This matches the existing `004` runtime pattern, keeps routing
inside WordPress-native infrastructure, and avoids brittle raw path parsing or
filesystem mutation.

**Alternatives considered**:

- Raw `REQUEST_URI` matching only: rejected because it bypasses WordPress
  routing and makes the behavior harder to test consistently.
- Physical file generation: rejected because the plugin must not mutate
  `.well-known` files.

## Decision 2: Keep the three public endpoints independently modular

**Decision**: Model MCP Server Card, OAuth discovery, and Protected Resource
as separate runtime modules with their own context factory, eligibility
matcher, document builder, response writer, and handler.

**Rationale**: The spec requires the endpoints to succeed or fail independently.
Separate modules keep the runtime decisions narrow and prevent one endpoint's
incomplete settings from blocking another.

**Alternatives considered**:

- One shared Phase 2 mega-handler: rejected because it would couple three
  different contracts and make fallback reasoning less clear.
- Reusing one generic document builder with many conditionals: rejected because
  the endpoint shapes and completeness rules differ materially.

## Decision 3: Reuse saved Phase 2 settings through explicit runtime getters

**Decision**: Extend `RuntimeFeatureSettingsGateway` with dedicated getters for
`mcp_server_card`, `protected_apis`, `oauth`, and `protected_resource`.

**Rationale**: The settings already exist, are sanitized in `002`, and should
remain authoritative. Explicit getters keep runtime code aligned with the
normalized option shape and avoid repeated ad hoc array access.

**Alternatives considered**:

- Reading raw options directly inside runtime handlers: rejected because it
  spreads option-shape knowledge across multiple classes.
- Introducing a new persistence layer: rejected because the phase is runtime
  only and the current option schema is sufficient.

## Decision 4: Extend compatibility detection with per-endpoint `.well-known` conflicts

**Decision**: Add dedicated compatibility flags and warnings for physical-file
conflicts at:

- `ABSPATH/.well-known/mcp/server-card.json`
- `ABSPATH/.well-known/openid-configuration`
- `ABSPATH/.well-known/oauth-protected-resource`

**Rationale**: The spec explicitly requires safe fallback when host rules or
physical files conflict. Per-endpoint flags let the runtime preserve host
authority and report stable reasons without conflating unrelated endpoints.

**Alternatives considered**:

- One shared generic `.well-known` conflict flag: rejected because the spec
  requires independent endpoint behavior.
- Ignoring physical-file conflicts for nested paths: rejected because that
  would risk the plugin publishing misleading ownership over existing host
  resources.

## Decision 5: Shape documents directly from saved settings with minimal derived metadata

**Decision**: Use small JSON documents that map directly to saved settings:

- MCP Server Card exposes saved `name`, `version`, `transport`, plus a derived
  `capabilities` map based on active plugin features.
- OAuth discovery exposes saved `issuer`, `authorization_endpoint`,
  `token_endpoint`, and `jwks_uri`.
- Protected Resource exposes saved `resource` and
  `authorization_servers`.

**Rationale**: This satisfies the feature requirements without inventing new
  admin fields or a broader schema that the plugin cannot currently populate
  accurately.

**Alternatives considered**:

- Attempting a richer MCP or OAuth schema beyond the saved values: rejected
  because the current settings do not support those extra fields.
- Returning only the raw settings with no derived capability signal for MCP
  Server Card: rejected because `PLAN.md` explicitly calls for capabilities
  auto-detected from active features.

## Decision 6: Withhold output based on runtime completeness evaluators

**Decision**: Each endpoint matcher will evaluate both enablement and required
field completeness at runtime, returning no generated response when data is
disabled, incomplete, inapplicable, or conflicting.

**Rationale**: Stored settings can become stale or only partly configured after
earlier saves, rollbacks, or integration changes. Runtime completeness checks
protect the public contracts from partial or misleading publication.

**Alternatives considered**:

- Trusting the last successful admin save without reevaluation: rejected
  because saved data can become incomplete relative to current applicability.
- Returning partial documents with missing fields: rejected because the spec
  requires withholding output rather than publishing misleading metadata.

## Decision 7: Use stable reason codes per endpoint

**Decision**: Endpoint matchers should return structured decisions with stable
reasons such as `eligible`, `feature_disabled`, `settings_incomplete`,
`protected_apis_disabled`, `not_request_target`, and
`physical_file_conflict`.

**Rationale**: The feature requires compatibility outcomes that explain why an
endpoint was not emitted. Stable reason codes keep tests and future diagnostics
deterministic without adding new admin UI work in this phase.

**Alternatives considered**:

- Silent fallback only: rejected because it is difficult to verify in tests and
  future support work.
- Human-readable messages only: rejected because message text is less stable
  than machine-readable reason codes.

## Decision 8: Cover the contracts with focused unit and routing-level integration tests

**Decision**: Add unit tests for document builders and eligibility evaluators,
plus integration tests for rewrite registration, public responses, response
headers, and conflict fallback across all three endpoints.

**Rationale**: The change affects public HTTP contracts and routing behavior, so
logic-only tests are not enough.

**Alternatives considered**:

- Unit tests only: rejected because rewrite/query-var behavior is an
  integration concern.
- Manual verification only: rejected by the constitution and feature
  requirements.
