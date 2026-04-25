# Research: Phase 1 Discovery Runtime

## Decision 1: Serve `/.well-known/api-catalog` through rewrite + query-var routing

**Decision**: Implement API Catalog publication with a WordPress rewrite rule
that maps `/.well-known/api-catalog` to a plugin-owned query var, then emit the
response from a runtime handler during `template_redirect`.

**Rationale**: This is WordPress-native, builds directly on the repository's
existing rewrite-flush behavior for API Catalog settings changes, and avoids
filesystem mutation or brittle request-path parsing.

**Alternatives considered**:

- Raw `REQUEST_URI` matching only: rejected because it bypasses WordPress
  rewrite plumbing and makes routing behavior harder to test.
- Physical file generation: rejected because the constitution explicitly
  disallows mutating `.well-known` files.

## Decision 2: Use Linkset JSON with explicit `service-desc` links

**Decision**: Shape API Catalog output as `application/linkset+json` with a top
level `links` array whose items describe discovery entries using `anchor`,
`href`, `rel`, and a human-readable label/title.

**Rationale**: This satisfies the specified media type while giving a stable,
machine-readable contract for default WordPress, optional WooCommerce, and
custom configured entries.

**Alternatives considered**:

- Ad hoc JSON fields per capability: rejected because it creates a custom
  contract not implied by the chosen media type.
- HTML or plain-text output: rejected because the feature requires
  machine-readable discovery behavior.

## Decision 3: Treat physical `/.well-known/api-catalog` conflicts as strict fallback

**Decision**: When compatibility detection reports a physical
`/.well-known/api-catalog` conflict, the runtime handler must not emit a plugin
response and must allow host or default behavior to remain authoritative.

**Rationale**: This preserves site/operator intent, matches existing
compatibility messaging, and avoids ambiguous ownership of the endpoint.

**Alternatives considered**:

- Override the physical file anyway: rejected because it is unsafe and violates
  WordPress/host expectations.
- Return a plugin-generated error response: rejected because the spec requires
  default host behavior to remain authoritative in conflict scenarios.

## Decision 4: Deliver WebMCP through an enqueued frontend runtime asset

**Decision**: Emit WebMCP behavior by conditionally enqueueing a small public
JavaScript asset that receives a localized or inline configuration payload with
the resolved tool set.

**Rationale**: This follows WordPress asset conventions, keeps server output
minimal, and allows browser capability detection to happen client-side without
string-building large inline scripts directly in PHP.

**Alternatives considered**:

- Echo a raw inline `<script>` block from `wp_head`: rejected because enqueue
  infrastructure is the safer, more maintainable WordPress-native path.
- Server-side registration with no browser runtime: rejected because tool
  registration depends on client capability detection.

## Decision 5: Unsupported browsers still receive the asset, but registration is a no-op

**Decision**: When WebMCP is enabled, public pages still receive the runtime
asset, but the script performs feature detection before any registration and
exits quietly when the required capability is missing.

**Rationale**: This matches the clarified requirement, preserves cacheable page
output across mixed client capabilities, and avoids runtime errors on ordinary
browsers.

**Alternatives considered**:

- Suppress the asset per user agent: rejected because capability cannot be
  determined reliably server-side and would create inconsistent cache behavior.
- Throw a console/runtime error on unsupported browsers: rejected because the
  feature must degrade safely.

## Decision 6: Resolve tool exposure from saved toggles plus compatibility state

**Decision**: Build the WebMCP tool list from persisted settings and runtime
compatibility signals, always honoring the saved tool toggles and allowing
`get_products` only when WooCommerce is active and the tool is enabled.

**Rationale**: This keeps the admin page authoritative for configuration while
ensuring runtime output cannot expose incompatible WooCommerce-only tools.

**Alternatives considered**:

- Hard-code the default tool list at runtime regardless of settings: rejected
  because it ignores the saved configuration contract.
- Hide all tools when WooCommerce is inactive: rejected because only the Woo
  tool is conditional; the other tools remain valid.

## Decision 7: Surface compatibility outcomes as stable runtime reasons

**Decision**: API Catalog and WebMCP evaluators should return structured
decision reasons such as `feature_disabled`, `physical_file_conflict`,
`woocommerce_unavailable`, `unsupported_context`, or
`browser_capability_unavailable` for tests and future diagnostics.

**Rationale**: The spec requires compatibility outcomes to be diagnosable, and
stable reason codes keep fallback behavior testable without forcing new admin UI
work into this phase.

**Alternatives considered**:

- Rely on implicit behavior only: rejected because silent fallback is difficult
  to verify and debug.
- Add new admin messaging in this phase: rejected because admin-page behavior is
  outside scope and already covered by earlier features.

## Decision 8: Cover discovery with focused unit tests and public-boundary integration tests

**Decision**: Add unit tests for catalog document building and WebMCP tool
resolution, plus integration tests for rewrite registration, endpoint
responses, frontend script emission, and compatibility fallbacks.

**Rationale**: Public discovery behavior changes both HTTP and frontend-script
contracts, so hook-level verification is required in addition to pure logic
tests.

**Alternatives considered**:

- Unit tests only: rejected because routing and enqueue behavior are
  integration concerns.
- Manual verification only: rejected by the constitution's automated coverage
  requirement.
