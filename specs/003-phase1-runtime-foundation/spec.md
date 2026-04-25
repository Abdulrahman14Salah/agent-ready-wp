# Feature Specification: Phase 1 Runtime Foundation

**Feature Branch**: `003-phase1-runtime-foundation`  
**Created**: 2026-04-24  
**Status**: Draft  
**Input**: User description: "Read PLAN.md and create a specification for phase 3: Phase 1 Runtime Foundation ONLY. Scope includes F2 Markdown Negotiation and F3 Content Signals runtime behavior, public outputs, hook registration, compatibility handling, and automated tests. Exclude admin-page behavior already covered in 001 and exclude F4 through F8."

## Clarifications

### Session 2026-04-24

- Q: When should markdown negotiation apply for mixed `Accept` headers? → A: Apply markdown only when `text/markdown` is highest or tied-highest in the request preference order.
- Q: How should cache variation be handled for markdown responses? → A: Add `Vary: Accept` on markdown responses only.
- Q: What should happen when markdown is requested for unsupported content? → A: Fall back to normal WordPress handling instead of returning markdown-specific errors.
- Q: How should access restrictions apply to markdown output? → A: Always honor WordPress access controls and never expose content via markdown that is not normally accessible to the requester.
- Q: Which request types are eligible for markdown negotiation in the MVP? → A: Only eligible frontend document requests for supported singular WordPress content; excluded system, admin, auth, feed, sitemap, REST, AJAX, cron, login/register, and asset requests always fall back to normal handling.
- Q: How should non-Latin content be handled in markdown output? → A: Preserve UTF-8 content exactly as normal WordPress rendering provides it, including Arabic text.
- Q: How should existing `Content-Signal` directives in generated robots output be handled? → A: Emit exactly one canonical `Content-Signal` line by replacing any existing generated `Content-Signal` lines before writing configured output.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Serve Markdown To Agents (Priority: P1)

As a site owner, I want compatible AI agents to receive a markdown response for supported public content so the same site content can be consumed by agent workflows without changing my theme output for normal visitors.

**Why this priority**: Markdown negotiation is a direct runtime requirement for scan readiness and delivers immediate value for agent consumption.

**Independent Test**: Can be fully tested by requesting supported content with a markdown `Accept` value and confirming a markdown response contract, while normal browser-style requests still receive HTML.

**Acceptance Scenarios**:

1. **Given** markdown negotiation is enabled and the requested item is a supported public singular frontend content type, **When** the request advertises `Accept: text/markdown`, **Then** the response is returned as markdown with `Content-Type: text/markdown; charset=utf-8`, `Vary: Accept`, and the existing token-count header contract.
2. **Given** markdown negotiation is enabled, **When** a normal browser-style request is made without markdown acceptance, **Then** the original HTML behavior remains unchanged.
3. **Given** a request targets an excluded route or request class such as `wp-admin`, REST API, AJAX, cron, feeds, sitemaps, login/register, or assets, **When** it advertises `Accept: text/markdown`, **Then** the plugin preserves normal HTML or native default behavior and does not emit markdown-specific headers.
4. **Given** eligible frontend content includes Arabic or other UTF-8 text, **When** markdown negotiation applies, **Then** the markdown response preserves that text content without character corruption.
5. **Given** WooCommerce is active and product markdown support is enabled, **When** a product page is requested with markdown acceptance, **Then** the response includes product-relevant content in markdown form.

---

### User Story 2 - Publish Content Signals In robots.txt (Priority: P2)

As a site owner, I want configured content-signal preferences to appear in runtime `robots.txt` output so crawlers and agents receive clear policy signals.

**Why this priority**: Content-signal output is a core runtime requirement and is independent from markdown negotiation.

**Independent Test**: Can be fully tested by enabling content signals, setting signal values, requesting `robots.txt`, and confirming only configured signal pairs are emitted.

**Acceptance Scenarios**:

1. **Given** content signals are enabled and one or more signal values are configured, **When** runtime `robots.txt` is requested, **Then** output includes a single `Content-Signal` directive with only configured values.
2. **Given** content signals are enabled but all values are unset, **When** runtime `robots.txt` is requested, **Then** no empty `Content-Signal` directive is emitted.
3. **Given** content signals are disabled, **When** runtime `robots.txt` is requested, **Then** output remains the WordPress default for this feature.

---

### User Story 3 - Preserve Safe Fallbacks Under Compatibility Limits (Priority: P3)

As a site owner, I want runtime behavior to fail safely when prerequisites are missing or conflicting so default WordPress behavior is preserved and scan outcomes remain diagnosable.

**Why this priority**: Compatibility handling prevents regressions and reduces misconfiguration risk for production sites.

**Independent Test**: Can be fully tested by simulating common incompatibilities (for example, physical `robots.txt` conflicts or unsupported content contexts) and confirming runtime either falls back cleanly or emits only valid outputs.

**Acceptance Scenarios**:

1. **Given** a physical server `robots.txt` overrides generated output, **When** runtime content-signal output cannot be applied, **Then** the plugin does not attempt unsafe file mutation and preserves default server behavior.
2. **Given** a request is not for supported singular frontend content, **When** markdown acceptance is advertised, **Then** the plugin does not emit malformed markdown output and allows normal request handling.
3. **Given** relevant optional dependencies are unavailable, **When** runtime behavior executes, **Then** unsupported capability paths are skipped without breaking page rendering.

### Edge Cases

- A request contains mixed or complex `Accept` values where markdown is present alongside HTML preferences.
- Markdown negotiation is enabled but the requested resource is not a supported public singular frontend item.
- A request targets `wp-admin`, REST API, AJAX, cron, feeds, sitemaps, login/register pages, or asset URLs while still advertising `Accept: text/markdown`.
- A qualifying frontend response contains Arabic or other UTF-8 text that must remain unchanged after conversion.
- WooCommerce is inactive after previously saving Woo-related markdown settings.
- A physical `robots.txt` exists, preventing generated `robots.txt` filters from determining public output.
- Feature toggles are disabled, partially configured, or rolled back after prior enablement.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide runtime markdown negotiation behavior only for eligible frontend document requests for supported public singular content when the markdown feature is enabled and `text/markdown` is the highest or tied-highest accepted representation.
- **FR-002**: The system MUST return markdown responses with the markdown content type contract and an approximate token-count header when markdown negotiation is applied.
- **FR-012**: The system MUST emit `Vary: Accept` on markdown responses so intermediaries can distinguish markdown from non-markdown representations of the same resource.
- **FR-003**: The system MUST preserve normal HTML rendering behavior for requests that do not qualify for markdown negotiation.
- **FR-013**: The system MUST fall back to normal WordPress request handling for unsupported-content markdown requests rather than returning markdown-specific error responses.
- **FR-014**: The system MUST enforce WordPress visibility and access-control rules for markdown negotiation so protected or non-public content is never exposed beyond normal requester permissions.
- **FR-016**: The system MUST treat `wp-admin`, REST API, AJAX, cron, feeds, sitemaps, login/register pages, and asset requests as ineligible for markdown negotiation in this MVP and MUST preserve their normal default handling.
- **FR-017**: The system MUST preserve UTF-8 output fidelity, including Arabic text, when converting eligible content to markdown.
- **FR-015**: The system MUST ensure runtime robots output contains at most one canonical `Content-Signal` directive by replacing any existing generated `Content-Signal` directives before emitting the configured directive.
- **FR-004**: The system MUST scope markdown output in this MVP to supported singular WordPress content types configured in plugin settings, including WooCommerce product content only when Woo support is available and enabled.
- **FR-005**: The system MUST append a `Content-Signal` directive to runtime `robots.txt` output only when content signals are enabled and at least one signal value is configured.
- **FR-006**: The system MUST include only configured signal key-value pairs in runtime `Content-Signal` output and omit unset values.
- **FR-007**: The system MUST preserve default WordPress or host behavior for both markdown and content-signal outputs when the relevant feature is disabled.
- **FR-008**: The system MUST avoid direct mutation of physical server files when compatibility conflicts exist and MUST fail safely without breaking default site behavior.
- **FR-009**: The system MUST expose compatibility outcomes needed for diagnosing why runtime behavior is unavailable in conflict scenarios.
- **FR-010**: The system MUST include automated tests that validate positive, negative, and fallback runtime behavior for markdown negotiation and content-signal output.
- **FR-011**: The specification scope MUST exclude admin-page behavior already defined by `001-foundation-architecture-page` and MUST exclude discovery/auth runtime capabilities covered by F4 through F8.

### Key Entities *(include if feature involves data)*

- **Runtime Feature Settings**: Persisted feature toggles and scoped options used to decide whether markdown or content-signal runtime behavior should execute.
- **Markdown Response Contract**: Runtime response payload and headers produced when a request qualifies for markdown negotiation.
- **Eligible Frontend Document Request**: A non-admin, non-system, non-asset frontend request that resolves to supported singular WordPress content and is therefore allowed to negotiate into markdown in this phase.
- **Content Signal Output State**: Resolved runtime representation of configured content-signal values for `robots.txt` output.
- **Runtime Compatibility State**: Determination of whether environmental constraints (such as physical-file conflicts or missing optional dependencies) block or limit runtime behavior.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios for supported singular frontend content, 100% of qualifying markdown requests return markdown output with required markdown-response headers.
- **SC-002**: In validation scenarios for standard browser requests, 100% of non-qualifying requests keep default HTML output behavior.
- **SC-005**: In validation scenarios for excluded routes or request classes, 100% of requests preserve normal WordPress or server behavior without markdown-specific headers.
- **SC-006**: In validation scenarios containing Arabic or other UTF-8 content, 100% of qualifying markdown responses preserve the original text without encoding corruption.
- **SC-003**: In validation scenarios for content signals, 100% of enabled-and-configured cases emit correctly formed `Content-Signal` output, and 100% of disabled or fully unset cases emit no `Content-Signal` line.
- **SC-004**: In compatibility conflict scenarios, 100% of tested cases preserve default WordPress or host behavior without unsafe file mutation or fatal runtime errors.

## Assumptions

- Existing admin configuration flows from `001` and `002` remain the source of runtime settings and are not re-specified here.
- MVP target remains WordPress 6.0+ on single-site installations with PHP 8.0+.
- Runtime behavior for F2 and F3 is implemented independently from F4 through F8 endpoints and auth-related capabilities.
- Markdown negotiation MVP scope remains limited to supported singular frontend document requests rather than all possible frontend routes.
- Automated tests for this phase include unit and integration coverage aligned to runtime behavior and fallback handling.
