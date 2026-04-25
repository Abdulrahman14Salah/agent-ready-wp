# Implementation Plan: Phase 1 Discovery Runtime

**Branch**: `004-phase1-discovery-runtime` | **Date**: 2026-04-24 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/004-phase1-discovery-runtime/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver runtime-only discovery behavior for F4 API Catalog and F5 WebMCP using
WordPress-native rewrite, frontend hook, and enqueue mechanisms. The
implementation will publish a machine-readable `/.well-known/api-catalog`
endpoint when enabled, emit a public frontend WebMCP registration runtime only
when enabled, reuse existing saved settings and compatibility detection from
`001`/`002`, and preserve default WordPress or host behavior whenever features
are disabled, conflicting physical files exist, or browser support is missing.

## Technical Context

**Language/Version**: PHP 8.0+ for WordPress runtime behavior, JavaScript for a minimal frontend WebMCP runtime asset  
**Primary Dependencies**: WordPress 6.0+ hooks/filters (`init`, `query_vars`, `template_redirect`, `wp_enqueue_scripts` or `wp_head`-adjacent enqueue flow), existing settings repository/runtime gateways, existing environment detector, optional WooCommerce detection  
**Storage**: Existing `agent_ready_wp_settings` option array only; no new persistent storage for this phase  
**Testing**: PHPUnit unit tests for catalog document shaping and tool exposure resolution; WordPress integration tests for endpoint routing, response headers/body, script emission, and fallback behavior; manual browser/curl acceptance checks for public discovery outputs  
**Target Platform**: WordPress 6.0+ single-site installs on PHP 8.0+, public frontend requests, optional WooCommerce, browsers with and without the required WebMCP capability  
**Project Type**: WordPress plugin runtime extension with public HTTP and frontend-script contracts  
**Performance Goals**: No remote requests on discovery runtime paths; API Catalog output built from local settings/environment only; WebMCP assets short-circuit when disabled; rewrite flushing remains limited to activation/deactivation and settings changes already tracked by the repository  
**Constraints**: Must preserve default routing when discovery is disabled; must not mutate physical `.well-known` files; must reuse existing saved settings and admin sanitization flow; must emit no WebMCP output on non-public/admin contexts; must no-op safely when browser capability is absent; scope excludes admin-page work and excludes F2/F3/F6-F8  
**Scale/Scope**: Runtime coverage for API Catalog endpoint publication, default/custom discovery entries, WebMCP tool exposure for `search`, `get_posts`, `get_page`, and WooCommerce-conditional `get_products`, plus compatibility diagnostics and automated verification

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

Post-design review: PASS. The design keeps discovery behavior inside
WordPress-native runtime seams, reuses the existing option schema and
compatibility detector, preserves host authority for physical-file conflicts,
and ties all public endpoint/script contracts to unit or integration coverage.

## Project Structure

### Documentation (this feature)

```text
specs/004-phase1-discovery-runtime/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── api-catalog-contract.md
│   └── webmcp-runtime-contract.md
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
│   │   ├── ApiCatalog/
│   │   ├── WebMcp/
│   │   ├── RuntimeCompatibilityGateway.php
│   │   └── RuntimeFeatureSettingsGateway.php
│   └── Settings/
├── Infrastructure/
│   └── WordPress/
│       ├── Hooks.php
│       └── Runtime/
│           └── RuntimeHooksRegistrar.php
├── Integrations/
│   └── WooCommerce/
└── Admin/
assets/
└── js/
    └── webmcp-runtime.js
tests/
├── integration/
│   └── Runtime/
├── unit/
│   └── Runtime/
└── fixtures/
```

**Structure Decision**: Extend the existing runtime architecture under
`src/Application/Runtime/` with dedicated `ApiCatalog` and `WebMcp` modules,
keep orchestration in `src/Infrastructure/WordPress/Runtime/RuntimeHooksRegistrar.php`,
reuse the current settings and compatibility gateways, and add a small
frontend runtime asset for browser-side registration. This preserves the
implemented F2/F3 runtime pattern while adding the minimal new asset surface
required for safe WebMCP capability detection.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No constitution violations identified for this feature.
