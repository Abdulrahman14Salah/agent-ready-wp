# Feature Specification: Phase 2 Public Discovery Endpoints

**Feature Branch**: `005-phase2-public-discovery-endpoints`  
**Created**: 2026-04-24  
**Status**: Draft  
**Input**: User description: "Read SPECKIT_SEQUENCE.md and create a specification for phase 5: Phase 2 Public Discovery Endpoints ONLY. Scope includes F6 MCP Server Card, F7 OAuth or OIDC Discovery, and F8 OAuth Protected Resource runtime behavior, public endpoints, validation dependencies on saved settings, compatibility handling, and automated tests. Exclude admin-page behavior already covered in 002."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Publish MCP Server Card (Priority: P1)

As a site owner, I want the site to publish a machine-readable MCP Server Card
endpoint from saved settings so discovery clients can identify the server and
its available capabilities without manual inspection.

**Why this priority**: The MCP Server Card is the primary Phase 2 public
discovery artifact and is the most direct extension of the server metadata
already configured in Phase 2 settings.

**Independent Test**: Can be fully tested by enabling the saved MCP Server Card
settings, requesting `/.well-known/mcp/server-card.json`, and verifying that a
successful JSON document is returned with the expected saved identity values.

**Acceptance Scenarios**:

1. **Given** valid MCP Server Card settings are saved and the feature is
   enabled, **When** a requester calls `/.well-known/mcp/server-card.json`,
   **Then** the site returns a successful JSON document containing the saved
   server name, version, and transport details.
2. **Given** the MCP Server Card settings are incomplete or disabled,
   **When** the same endpoint is requested, **Then** the plugin does not
   publish a misleading generated document and preserves default host or
   WordPress behavior.
3. **Given** the site exposes other supported runtime capabilities,
   **When** the MCP Server Card endpoint is requested, **Then** the response
   reflects the active saved capability metadata rather than unrelated or
   unavailable features.

---

### User Story 2 - Publish OAuth/OIDC Discovery Metadata (Priority: P2)

As a site owner with protected APIs, I want the site to publish OAuth or OIDC
discovery metadata from saved settings so clients can discover the correct
issuer and authorization endpoints automatically.

**Why this priority**: OAuth/OIDC discovery is necessary for protected API
clients, but it depends on the protected-API settings introduced in the earlier
admin-page phase and is only valuable once the server card can already be
published.

**Independent Test**: Can be fully tested by enabling protected APIs and valid
OAuth settings, requesting `/.well-known/openid-configuration`, and verifying
that the saved issuer and endpoint values are returned in a coherent discovery
document.

**Acceptance Scenarios**:

1. **Given** protected APIs are marked applicable and valid OAuth discovery
   settings are saved, **When** a requester calls
   `/.well-known/openid-configuration`, **Then** the site returns a successful
   discovery document containing the saved issuer, authorization endpoint,
   token endpoint, and JWKS URI.
2. **Given** protected APIs are not applicable or OAuth discovery is disabled,
   **When** the discovery endpoint is requested, **Then** the plugin does not
   emit generated OAuth discovery output.
3. **Given** required OAuth discovery values are missing or incomplete,
   **When** the discovery endpoint is requested, **Then** the plugin withholds
   the generated document rather than publishing partial or misleading data.

---

### User Story 3 - Publish Protected Resource Metadata Safely (Priority: P3)

As a site owner with protected APIs, I want the site to publish protected
resource metadata only when the saved configuration is complete so clients can
discover the correct authorization relationship without breaking normal site
behavior when the configuration is absent or incompatible.

**Why this priority**: Protected resource metadata depends on the saved
protected-API and OAuth configuration, and safe fallback behavior is critical to
avoid exposing incorrect security metadata.

**Independent Test**: Can be fully tested by requesting
`/.well-known/oauth-protected-resource` under complete, incomplete, and
disabled protected-API configurations and verifying that the response appears
only in the valid configuration case.

**Acceptance Scenarios**:

1. **Given** protected APIs are applicable and the saved protected-resource
   settings are complete, **When** a requester calls
   `/.well-known/oauth-protected-resource`, **Then** the site returns a
   successful JSON document containing the saved resource identifier and its
   related authorization server metadata.
2. **Given** protected APIs are disabled, **When** the protected-resource
   endpoint is requested, **Then** the plugin does not emit generated protected
   resource metadata and default behavior remains authoritative.
3. **Given** the required protected-resource configuration is incomplete or a
   host conflict prevents safe publication, **When** the endpoint is requested,
   **Then** the plugin fails safely without mutating files or breaking normal
   WordPress routing.

### Edge Cases

- Saved Phase 2 values exist for MCP Server Card, OAuth discovery, or protected
  resource metadata, but one or more required fields are later disabled or
  cleared.
- Protected APIs are marked not applicable even though prior OAuth or protected
  resource values remain stored from an earlier configuration state.
- Host rules or physical files under `.well-known` conflict with plugin-owned
  generated endpoint behavior.
- The site publishes the server card successfully, but OAuth discovery or
  protected resource settings remain incomplete and must not be published.
- Optional integrations or capabilities referenced by saved metadata are no
  longer available at runtime.
- A site rollback or partial settings update leaves Phase 2 metadata only partly
  configured.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST expose a public endpoint at
  `/.well-known/mcp/server-card.json` when saved MCP Server Card settings are
  enabled and complete.
- **FR-002**: The system MUST return MCP Server Card responses as successful
  machine-readable JSON containing the saved server identity values relevant to
  the published server card.
- **FR-003**: The system MUST expose a public endpoint at
  `/.well-known/openid-configuration` only when protected APIs are applicable
  and the required saved OAuth discovery settings are complete.
- **FR-004**: The system MUST return OAuth/OIDC discovery responses as
  successful machine-readable JSON containing the saved issuer,
  authorization endpoint, token endpoint, and JWKS URI.
- **FR-005**: The system MUST expose a public endpoint at
  `/.well-known/oauth-protected-resource` only when protected APIs are
  applicable and the required saved protected-resource metadata is complete.
- **FR-006**: The system MUST return protected-resource responses as successful
  machine-readable JSON containing the saved resource identifier and related
  authorization server metadata.
- **FR-007**: The system MUST consume previously saved and sanitized Phase 2
  settings and MUST NOT introduce new admin configuration flows in this phase.
- **FR-008**: The system MUST withhold generated Phase 2 endpoint output when
  the relevant feature is disabled, protected APIs are not applicable, or
  required saved values are incomplete.
- **FR-009**: The system MUST preserve default WordPress or host behavior and
  MUST NOT mutate physical `.well-known` files when conflicts or host rules
  prevent safe endpoint publication.
- **FR-010**: The system MUST keep the three Phase 2 public discovery endpoints
  independently scoped so MCP Server Card, OAuth discovery, and protected
  resource output can succeed or fail without forcing unrelated endpoints to be
  published or hidden.
- **FR-011**: The system MUST surface runtime compatibility outcomes needed to
  explain why a Phase 2 endpoint was not emitted.
- **FR-012**: The system MUST include automated tests covering enabled behavior,
  disabled behavior, incomplete-settings behavior, and compatibility or fallback
  behavior for all three Phase 2 public discovery endpoints.
- **FR-013**: The scope of this specification MUST exclude the Phase 2 admin
  page behavior already covered by `002-phase2-foundation-page`.

### Key Entities *(include if feature involves data)*

- **MCP Server Card Output Document**: The machine-readable response returned at
  `/.well-known/mcp/server-card.json` using saved server identity and
  capability metadata.
- **OAuth Discovery Output Document**: The machine-readable response returned at
  `/.well-known/openid-configuration` using the saved issuer and related
  discovery endpoint values.
- **Protected Resource Output Document**: The machine-readable response returned
  at `/.well-known/oauth-protected-resource` using the saved resource and
  authorization server metadata.
- **Phase 2 Runtime Eligibility State**: The runtime decision state that
  determines whether each Phase 2 endpoint should publish output, fall back, or
  remain silent.
- **Saved Phase 2 Discovery Settings**: The previously configured server card,
  protected API applicability, OAuth discovery, and protected-resource values
  reused by the public runtime behavior.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios where MCP Server Card settings are
  complete and enabled, 100% of requests to
  `/.well-known/mcp/server-card.json` return a valid discovery document with
  the saved server identity values.
- **SC-002**: In validation scenarios where protected APIs are applicable and
  valid OAuth discovery settings are present, 100% of requests to
  `/.well-known/openid-configuration` return the saved discovery metadata, and
  100% of non-applicable or incomplete cases emit no generated document.
- **SC-003**: In validation scenarios where protected-resource settings are
  complete, 100% of requests to
  `/.well-known/oauth-protected-resource` return the saved protected-resource
  metadata, and 100% of incomplete or disabled cases emit no generated
  document.
- **SC-004**: In compatibility and fallback scenarios, 100% of cases preserve
  default WordPress or host behavior without fatal runtime errors or direct file
  mutation.

## Assumptions

- The existing Phase 2 settings management and validation behavior from
  `002-phase2-foundation-page` remains authoritative and is reused for runtime
  publication.
- The current release scope remains WordPress 6.0+ and PHP 8.0+ on
  single-site installations.
- The Phase 2 public endpoints are published independently from the earlier
  Phase 1 runtime behavior and do not change the already specified `003` and
  `004` runtime contracts.
- Phase 2 endpoint validation includes both automated tests and quickstart-style
  manual verification for the public outputs.
