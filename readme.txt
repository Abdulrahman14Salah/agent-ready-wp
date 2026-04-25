=== Agent Ready WP ===
Contributors: arqamweb
Tags: ai, wordpress, admin, settings
Requires at least: 6.0
Tested up to: 6.9.4
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Provides a single admin page for reviewing agent-readiness status and configuring Phase 1 + Phase 2 capability settings.

== Description ==

Agent Ready WP by ArqamWeb (https://arqamweb.com) adds a settings page under `Settings > Agent Ready` where administrators can:

- review the latest scan summary,
- run a fresh readiness scan,
- configure the four Phase 1 capability panels,
- serve markdown responses for qualified `Accept: text/markdown` singular requests,
- publish canonical `Content-Signal` directives in generated `robots.txt` output when configured,
- publish a machine-readable `/.well-known/api-catalog` discovery document when API Catalog runtime is enabled,
- expose a public WebMCP runtime asset with settings-driven discovery tools on frontend pages,
- publish `/.well-known/mcp/server-card.json` from saved Phase 2 MCP Server Card settings when complete,
- publish `/.well-known/openid-configuration` from saved Phase 2 OAuth discovery settings when protected APIs are applicable,
- publish `/.well-known/oauth-protected-resource` from saved Phase 2 protected-resource settings when complete,
- review compatibility warnings,
- configure Phase 2 MCP Server Card, OAuth discovery, and protected-resource settings,
- toggle protected API applicability with disabled-but-visible grouped sections,
- review draft Phase 2 previews and in-place validation feedback before save.

== Changelog ==

= 0.1.0 =
- Added the Foundation and Architecture admin page
- Added live Phase 2 admin sections with shared save, draft preview, and validation feedback
- Added Phase 1 runtime markdown negotiation behavior with `Vary: Accept` and `x-markdown-tokens` response contracts
- Added Phase 1 runtime content-signal robots behavior with canonical single-line emission and compatibility-safe fallback handling
- Added Phase 1 discovery runtime behavior for `/.well-known/api-catalog` and frontend WebMCP tool exposure
- Added Phase 2 public discovery runtime behavior for `/.well-known/mcp/server-card.json`, `/.well-known/openid-configuration`, and `/.well-known/oauth-protected-resource`
