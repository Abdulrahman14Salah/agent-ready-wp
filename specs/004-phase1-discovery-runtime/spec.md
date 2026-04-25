# Feature Specification: Phase 1 Discovery Runtime

**Feature Branch**: `004-phase1-discovery-runtime`  
**Created**: 2026-04-24  
**Status**: Draft  
**Input**: User description: "Read PLAN.md and create a specification for phase 4: Phase 1 Discovery Runtime ONLY. Scope includes F4 API Catalog and F5 WebMCP public runtime behavior, endpoint or script output, settings integration, compatibility handling, and automated tests. Exclude admin-page behavior already covered in 001 and exclude F2-F3 and F6-F8."

## Clarifications

### Session 2026-04-24

- Q: What should happen at `/.well-known/api-catalog` when API Catalog is disabled? → A: Do not emit generated API Catalog output and fall through to default WordPress/host behavior.
- Q: What response media type should API Catalog use when enabled? → A: Use `application/linkset+json`.
- Q: Where should WebMCP be emitted when enabled? → A: Emit WebMCP on all public frontend pages.
- Q: What is the default WebMCP tool set? → A: Default to `search`, `get_posts`, and `get_page`, with `get_products` added only when WooCommerce is active and enabled.
- Q: How should unsupported browsers handle WebMCP runtime output? → A: Emit the script, but make registration a no-op when the required browser capability is unavailable.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Publish API Catalog Endpoint (Priority: P1)

As a site owner, I want a public API Catalog endpoint that publishes machine-readable service links so discovery agents can detect available APIs without manual endpoint mapping.

**Why this priority**: API Catalog exposure is the primary discovery contract for this phase and directly affects scan readiness for F4.

**Independent Test**: Can be fully tested by requesting `/.well-known/api-catalog` on an enabled site and verifying the expected discovery payload and response contract.

**Acceptance Scenarios**:

1. **Given** API Catalog runtime behavior is enabled, **When** a requester calls `/.well-known/api-catalog`, **Then** the site returns a successful machine-readable discovery document that includes the default WordPress REST API entry.
2. **Given** WooCommerce is active and WooCommerce discovery is enabled, **When** `/.well-known/api-catalog` is requested, **Then** the discovery document includes both WordPress and WooCommerce API entries.
3. **Given** custom API Catalog entries are configured, **When** `/.well-known/api-catalog` is requested, **Then** configured entries are present in the output with their saved labels and service links.

---

### User Story 2 - Expose WebMCP Runtime Script (Priority: P2)

As an agent-capable client user, I want the frontend to expose configured WebMCP tools when enabled so model-capable clients can discover and invoke site tools from page context.

**Why this priority**: WebMCP is the second discovery requirement in this phase and can be shipped independently once API Catalog behavior exists.

**Independent Test**: Can be fully tested by loading a public page with WebMCP enabled, confirming a tool-registration script is emitted, and validating that tool exposure matches saved runtime settings.

**Acceptance Scenarios**:

1. **Given** WebMCP runtime behavior is enabled, **When** any public frontend page is rendered, **Then** a tool-registration script is emitted with the default discovery tools `search`, `get_posts`, and `get_page`, plus `get_products` only when WooCommerce is active and enabled.
2. **Given** WebMCP is enabled and WooCommerce is inactive, **When** the page renders, **Then** WooCommerce-only tools are omitted while non-Woo tools remain available.
3. **Given** WebMCP is disabled, **When** a public page is rendered, **Then** no WebMCP registration script is emitted.

---

### User Story 3 - Preserve Safe Discovery Fallbacks (Priority: P3)

As a site owner, I want discovery runtime behavior to degrade safely under compatibility limits so normal WordPress output stays stable when discovery contracts cannot be safely emitted.

**Why this priority**: Compatibility handling prevents regressions and ensures discovery features do not break existing routing, rendering, or host constraints.

**Independent Test**: Can be fully tested by simulating file/routing conflicts and unsupported browser capability conditions, then confirming default behavior is preserved without fatal errors.

**Acceptance Scenarios**:

1. **Given** a physical `/.well-known/api-catalog` file or host rule conflicts with generated endpoint behavior, **When** discovery runtime executes, **Then** the plugin preserves host/default behavior and avoids unsafe file mutation.
2. **Given** a browser environment lacks the required WebMCP runtime capability, **When** the WebMCP script path executes, **Then** the emitted script performs no tool registration and page behavior remains stable without unhandled runtime errors.
3. **Given** discovery features are disabled or partially configured, **When** runtime behavior executes, **Then** default WordPress routing and frontend rendering remain authoritative.

### Edge Cases

- API Catalog is enabled but no custom entries are configured beyond defaults.
- API Catalog is enabled while WooCommerce toggles were saved previously, but WooCommerce is currently inactive.
- WebMCP is enabled with only a subset of tools selected.
- Frontend caching serves pages across environments with and without WebMCP-capable clients.
- A physical `/.well-known/api-catalog` path conflicts with generated runtime output.
- Settings are rolled back or partially disabled after prior discovery enablement.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST expose a public discovery endpoint at `/.well-known/api-catalog` when API Catalog runtime behavior is enabled.
- **FR-002**: The system MUST return API Catalog responses as machine-readable discovery output using the `application/linkset+json` media type with a successful HTTP response contract for valid enabled requests.
- **FR-003**: The system MUST include a default WordPress REST API entry in API Catalog output when API Catalog behavior is enabled.
- **FR-004**: The system MUST include a WooCommerce discovery entry only when WooCommerce is active and WooCommerce API catalog inclusion is enabled in saved settings.
- **FR-005**: The system MUST include configured custom API Catalog entries in output and omit incomplete or disabled entries.
- **FR-006**: The system MUST preserve default WordPress or host routing behavior for API Catalog when the feature is disabled by emitting no generated API Catalog response.
- **FR-007**: The system MUST emit a frontend WebMCP registration script on all public frontend pages only when WebMCP runtime behavior is enabled.
- **FR-008**: The system MUST scope WebMCP tool exposure to saved tool toggles and runtime compatibility state, using `search`, `get_posts`, and `get_page` as the default tool set and adding `get_products` only when WooCommerce is active and enabled.
- **FR-009**: The system MUST omit the WebMCP registration output entirely when WebMCP is disabled.
- **FR-010**: The system MUST ensure WebMCP runtime output fails safely when required browser capabilities are unavailable by making emitted registration logic a no-op, without breaking normal page rendering.
- **FR-011**: The system MUST consume previously saved and sanitized plugin settings for discovery behavior and MUST NOT introduce new admin configuration flows in this phase.
- **FR-012**: The system MUST preserve default host or server behavior and avoid direct file mutation when physical files or host rules conflict with generated API Catalog output.
- **FR-013**: The system MUST surface compatibility outcomes needed to diagnose why API Catalog or WebMCP behavior was not emitted.
- **FR-014**: The system MUST include automated tests covering enabled behavior, disabled behavior, WooCommerce-conditional behavior, and compatibility/fallback behavior for both API Catalog and WebMCP outputs.
- **FR-015**: The scope of this specification MUST exclude admin-page behavior already covered by `001-foundation-architecture-page` and MUST exclude F2, F3, and F6 through F8 runtime capabilities.

### Key Entities *(include if feature involves data)*

- **API Catalog Output Document**: The runtime discovery payload returned at `/.well-known/api-catalog`, including default entries and optional configured entries.
- **API Catalog Entry**: A normalized discovery item containing an anchor and service-description metadata derived from saved settings and compatibility state.
- **WebMCP Tool Exposure State**: The resolved set of tools eligible for frontend registration based on feature toggles and optional dependency availability.
- **Discovery Compatibility State**: Runtime signals that indicate whether endpoint or script output should execute, degrade, or fall back to default behavior.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios where API Catalog is enabled, 100% of requests to `/.well-known/api-catalog` return a valid discovery document with the default WordPress API entry.
- **SC-002**: In validation scenarios where WooCommerce discovery inclusion is configured, 100% of WooCommerce-active cases include WooCommerce discovery entries, and 100% of WooCommerce-inactive cases omit them.
- **SC-003**: In validation scenarios across enabled and disabled WebMCP states, 100% of enabled cases emit the configured tool-registration output and 100% of disabled cases emit no WebMCP registration output.
- **SC-004**: In compatibility/fallback scenarios (conflicting physical file, missing browser capability, or disabled feature), 100% of cases preserve default WordPress page and routing behavior without fatal runtime errors.

## Assumptions

- Existing settings management, sanitization, and admin UI behavior from `001` and `002` remain authoritative and are reused for this runtime scope.
- Phase 1 target remains WordPress 6.0+ single-site installations with PHP 8.0+.
- API Catalog and WebMCP discovery behavior are specified independently from markdown/content-signal runtime behavior already covered in `003`.
- Discovery runtime tests include both automated unit/integration checks and quickstart-style manual verification for public outputs.
