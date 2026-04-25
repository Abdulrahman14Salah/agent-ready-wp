# Implementation Plan: Phase 2 Public Discovery Endpoints

**Branch**: `005-phase2-public-discovery-endpoints` | **Date**: 2026-04-25 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/005-phase2-public-discovery-endpoints/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver runtime-only Phase 2 discovery behavior for F6 MCP Server Card, F7
OAuth/OIDC discovery, and F8 OAuth Protected Resource using WordPress-native
rewrite, query-var, and `template_redirect` handling. The implementation will
reuse the saved and sanitized Phase 2 settings introduced in `002`, publish
three independent `/.well-known` JSON endpoints only when their required
settings are complete and compatible, extend compatibility detection for
physical-file conflicts, and preserve default WordPress or host behavior
whenever an endpoint is disabled, incomplete, or blocked.

## Technical Context

**Language/Version**: PHP 8.0+ for WordPress runtime behavior  
**Primary Dependencies**: WordPress 6.0+ hooks/filters (`init`, `query_vars`, `template_redirect`), existing settings repository/runtime gateways, existing environment detector, existing Phase 1 runtime modules for capability derivation  
**Storage**: Existing `agent_ready_wp_settings` option array only; no new persistent storage for this phase  
**Testing**: PHPUnit unit tests for eligibility and document shaping; WordPress integration tests for rewrite registration, response headers/body, and fallback behavior; manual `curl` and browser validation for all three public endpoints  
**Target Platform**: WordPress 6.0+ single-site installs on PHP 8.0+, public HTTP requests to `/.well-known` endpoints  
**Project Type**: WordPress plugin runtime extension with public JSON discovery contracts  
**Performance Goals**: No remote requests on Phase 2 endpoint paths; endpoint handlers short-circuit before document building when disabled or incomplete; rewrite flushing remains limited to lifecycle or settings-driven events already used by the plugin  
**Constraints**: Must not add admin-page work; must not mutate physical `.well-known` files; must preserve host authority when physical files conflict; must reuse sanitized saved settings from `002`; must keep MCP Server Card, OAuth discovery, and Protected Resource behavior independently eligible  
**Scale/Scope**: Three public JSON endpoints, per-endpoint eligibility/fallback reasoning, capability derivation for MCP Server Card, and automated coverage for enabled, disabled, incomplete, and conflict scenarios

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Uses WordPress-native hooks, filters, rewrite rules, enqueue APIs, options,
      and lifecycle hooks instead of theme edits, core patches, or direct
      mutation of physical server files.
- [x] Defines sanitization, escaping, capability checks, and nonce coverage for
      every admin, AJAX, settings, and external-request path.
- [x] Maps each public contract that changes behavior, such as headers, rewrite
      endpoints, robots output, WebMCP exposure, REST/AJAX responses, or option
      migrations, to automated tests or explicit acceptance coverage.
- [x] Documents fallback behavior for missing prerequisites and conflicts,
      including WooCommerce absence, physical `robots.txt`, `.well-known`
      conflicts, disabled hooks, and remote API failures.
- [x] Shows that performance-sensitive behavior short-circuits when disabled,
      avoids uncached remote work on normal requests, and flushes rewrite rules
      only on lifecycle events.

Post-design review: PASS. The design stays inside existing WordPress runtime
seams, consumes already sanitized saved settings instead of introducing new
input paths, adds explicit fallback reasons for each endpoint, and ties every
public document contract to unit or integration coverage.

## Project Structure

### Documentation (this feature)

```text
specs/005-phase2-public-discovery-endpoints/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── mcp-server-card-contract.md
│   ├── oauth-discovery-contract.md
│   └── protected-resource-contract.md
└── tasks.md
```

### Source Code (repository root)

```text
agent-ready-wp.php
uninstall.php
readme.txt
src/
├── Application/
│   ├── Runtime/
│   │   ├── McpServerCard/
│   │   ├── OAuthDiscovery/
│   │   ├── ProtectedResource/
│   │   ├── RuntimeCompatibilityGateway.php
│   │   └── RuntimeFeatureSettingsGateway.php
│   └── Settings/
├── Infrastructure/
│   └── WordPress/
│       ├── Hooks.php
│       └── Runtime/
│           └── RuntimeHooksRegistrar.php
├── Integrations/
└── Admin/
tests/
├── integration/
│   └── Runtime/
├── unit/
│   └── Runtime/
└── fixtures/
```

**Structure Decision**: Extend the existing runtime architecture under
`src/Application/Runtime/` with three endpoint-focused modules mirroring the
implemented `ApiCatalog` pattern from `004`. Keep shared orchestration in
`src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`, extend the
runtime gateways rather than bypassing them, and place public-boundary coverage
in `tests/integration/Runtime/` with pure decision/document tests in
`tests/unit/Runtime/`.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No constitution violations identified for this feature.
