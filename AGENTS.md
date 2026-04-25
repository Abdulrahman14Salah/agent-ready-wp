# agent-ready-wp Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-04-25

## Active Technologies
- PHP 8.0+ for plugin runtime, JavaScript for progressive admin-page enhancement + WordPress 6.0+ core admin APIs, Settings API/options handling, existing Agent Ready page classes, admin notices, optional WooCommerce detection only for shared page continuity (002-phase2-foundation-page)
- Existing `agent_ready_wp_settings` option array extended with Phase 2 keys for protected API applicability, MCP Server Card settings, OAuth discovery settings, and protected-resource settings (002-phase2-foundation-page)
- PHP 8.0+ for plugin runtime behavior + WordPress 6.0+ hooks/filters (`template_redirect`, `robots_txt`), existing settings repository and compatibility detector, optional WooCommerce detection for product markdown scope (003-phase1-runtime-foundation)
- Existing `agent_ready_wp_settings` option array only; no new persistent store for this phase (003-phase1-runtime-foundation)
- PHP 8.0+ for WordPress runtime behavior, JavaScript for a minimal frontend WebMCP runtime asse + WordPress 6.0+ hooks/filters (`init`, `query_vars`, `template_redirect`, `wp_enqueue_scripts` or `wp_head`-adjacent enqueue flow), existing settings repository/runtime gateways, existing environment detector, optional WooCommerce detection (004-phase1-discovery-runtime)
- Existing `agent_ready_wp_settings` option array only; no new persistent storage for this phase (004-phase1-discovery-runtime)
- PHP 8.0+ for WordPress runtime behavior + WordPress 6.0+ hooks/filters (`init`, `query_vars`, `template_redirect`), existing settings repository/runtime gateways, existing environment detector, existing Phase 1 runtime modules for capability derivation (005-phase2-public-discovery-endpoints)

- PHP 8.0+ for plugin runtime, JavaScript for progressive admin-page enhancement + WordPress 6.0+ core admin APIs, Settings API/options handling, admin-ajax, transients, HTTP API, optional WooCommerce detection (001-foundation-architecture-page)

## Project Structure

```text
agent-ready-wp.php
uninstall.php
src/
assets/
languages/
tests/
```

## WordPress Plugin Constraints

- Prefer WordPress-native APIs, hooks, filters, options, transients, rewrite
  rules, and lifecycle hooks before introducing custom infrastructure
- Sanitize input, escape output, enforce capabilities and nonces on privileged
  actions, and use the WordPress HTTP API for remote requests
- Preserve default site behavior when a feature is disabled or prerequisites are
  unavailable
- Keep plugin-owned identifiers prefixed and localize user-facing strings with
  the configured text domain

## Commands

- `phpunit` for unit and WordPress integration tests
- `phpcs` for WordPress coding standards checks when configured
- WordPress admin manual validation via `Settings > Agent Ready`

## Code Style

- Follow WordPress PHP coding standards and WordPress admin UI conventions
- Prefer WordPress-native APIs for settings, AJAX, transients, HTTP requests,
  hooks, and capability checks
- Keep admin-page JavaScript small and progressive; server-render first, enhance
  scan refresh behavior client-side

## Recent Changes
- 005-phase2-public-discovery-endpoints: Added PHP 8.0+ for WordPress runtime behavior + WordPress 6.0+ hooks/filters (`init`, `query_vars`, `template_redirect`), existing settings repository/runtime gateways, existing environment detector, existing Phase 1 runtime modules for capability derivation
- 004-phase1-discovery-runtime: Added PHP 8.0+ for WordPress runtime behavior, JavaScript for a minimal frontend WebMCP runtime asse + WordPress 6.0+ hooks/filters (`init`, `query_vars`, `template_redirect`, `wp_enqueue_scripts` or `wp_head`-adjacent enqueue flow), existing settings repository/runtime gateways, existing environment detector, optional WooCommerce detection
- 003-phase1-runtime-foundation: Added PHP 8.0+ for plugin runtime behavior + WordPress 6.0+ hooks/filters (`template_redirect`, `robots_txt`), existing settings repository and compatibility detector, optional WooCommerce detection for product markdown scope

<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
