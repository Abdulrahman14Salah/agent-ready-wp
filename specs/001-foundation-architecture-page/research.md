# Research: Foundation and Architecture Page

## Decision 1: Use a single WordPress admin settings page with page-level save and separate scan action

**Decision**: Implement the feature as one settings page under `Settings > Agent Ready`
with a single page-level save action for all Phase 1 settings and a separate
scan action that refreshes the readiness summary in place.

**Rationale**: The clarified spec requires one entry point, one page-level save
flow, and no implicit save when scans run. This aligns with WordPress admin
conventions, keeps state handling simple, and avoids accidental configuration
changes during a scan refresh.

**Alternatives considered**:

- Per-section save actions: rejected because they complicate the administrator
  mental model and create avoidable partial-save states.
- Automatic save before scan: rejected because it violates the clarification
  that scan execution must stay independent from pending settings changes.
- Separate screens: rejected because the feature scope is explicitly limited to
  a single page.

## Decision 2: Persist page data in one option array plus a cached scan transient

**Decision**: Store all page-controlled configuration in the
`agent_ready_wp_settings` option array and store the latest scan payload in the
`agent_ready_wp_scan_cache` transient.

**Rationale**: The source plan already defines this storage model, and it fits
the constitution’s preference for WordPress-native storage with minimal
operational overhead. It also cleanly separates durable configuration from
ephemeral scan status.

**Alternatives considered**:

- Custom tables: rejected because the data is flat, low-volume, and does not
  justify migration and review complexity.
- Separate options per capability: rejected because one namespaced option array
  simplifies lifecycle management, uninstall cleanup, and future migrations.
- Permanent storage for scan results: rejected because cached scan status is not
  authoritative site content and should expire naturally.

## Decision 3: Use progressive enhancement for the in-page scan refresh

**Decision**: Render the page server-side from saved configuration and cached
scan data, then use a small admin JavaScript module to trigger scans and update
the readiness summary area in place when the AJAX response returns.

**Rationale**: The page must remain usable with standard WordPress form
handling while also supporting immediate summary refresh after scans. A small
progressive enhancement layer satisfies both goals without turning the page into
a client-heavy admin app.

**Alternatives considered**:

- Full client-rendered admin interface: rejected as unnecessary complexity for a
  largely form-based page.
- Full page reload after scan: rejected because the clarified spec requires
  same-page summary refresh.
- Polling-based updates: rejected because scan execution is explicit and returns
  a direct result, so polling adds complexity without value.

## Decision 4: Keep unavailable controls visible but disabled with explanatory text

**Decision**: When WooCommerce or another optional dependency is unavailable, or
when a saved capability can no longer be applied automatically, render the
relevant controls in a disabled state with explanatory text instead of hiding
them.

**Rationale**: This preserves administrator context, prevents confusion about
missing settings, and matches the compatibility and UX guidance in the source
plan.

**Alternatives considered**:

- Hide unavailable controls: rejected because it obscures why a previously known
  capability is missing.
- Leave controls active and fail on save: rejected because it creates a poorer
  experience and avoidable invalid submissions.

## Decision 5: Use compatibility warnings and placeholders as explicit page states

**Decision**: Model physical `robots.txt`, `.well-known` conflicts, absent
WooCommerce, and future-phase capabilities as first-class page states with
notices, disabled controls, or non-interactive placeholders.

**Rationale**: The page’s purpose is not only configuration but also operator
guidance. Making these conditions explicit supports graceful degradation and
keeps the page behavior aligned with the constitution.

**Alternatives considered**:

- Silent degradation: rejected because it hides operational constraints from the
  administrator.
- Blocking the entire page on one limitation: rejected because unrelated Phase 1
  settings must remain configurable.

## Decision 6: Verify with unit, integration, and manual admin acceptance coverage

**Decision**: Test pure setting/scan/compatibility logic with PHPUnit unit
tests, verify page rendering and AJAX contracts with WordPress integration
tests, and use manual acceptance checks for end-to-end admin behaviors such as
unsaved-change separation and same-page summary refresh.

**Rationale**: This matches the constitution requirement that hook-driven and
AJAX-driven boundaries be tested while keeping browser-heavy testing
proportionate to the feature’s complexity.

**Alternatives considered**:

- Manual testing only: rejected because the constitution requires automated
  verification for public integration boundaries and security-sensitive paths.
- Browser automation as the primary test strategy: deferred because the admin
  page behavior can be covered sufficiently with integration tests plus manual
  acceptance in this phase.
