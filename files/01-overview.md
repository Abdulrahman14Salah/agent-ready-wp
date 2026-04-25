# Agent Ready WP — Plugin Overview

## What It Does

A WordPress plugin that automatically implements all technical requirements needed
to pass the [isitagentready.com](https://isitagentready.com/) scan. The site owner
configures their preferences once, and the plugin handles all server-level fixes
without touching theme files or writing custom code.

## Target Users

- WordPress site owners (no WooCommerce, custom themes, CPT sites)
- WooCommerce store owners
- Developers managing client sites

## Problem It Solves

AI agents need websites to expose structured, machine-readable signals to discover,
interact with, and index content correctly. WordPress sites fail these checks by
default. This plugin fixes that without requiring server access or Cloudflare.

## Plugin Identity

- **Plugin Name:** Agent Ready WP
- **Text Domain:** agent-ready-wp
- **Min WordPress:** 6.0
- **Min PHP:** 8.0
- **License:** GPL-2.0+
- **WordPress.org ready:** Yes (follows Plugin Boilerplate conventions)

## Scope (MVP — Phase 1)

Fix the following isitagentready.com checks:

| Check | Category | MVP |
|---|---|---|
| Markdown Negotiation | Content Accessibility | ✅ |
| Content Signals (robots.txt) | Bot Access Control | ✅ |
| API Catalog `/.well-known/api-catalog` | Discovery | ✅ (minimal/WP REST) |
| WebMCP | Discovery | ✅ |
| MCP Server Card | Discovery | Phase 2 |
| OAuth/OIDC Discovery | API Auth | Phase 2 |
| OAuth Protected Resource | API Auth | Phase 2 |

## Out of Scope (MVP)

- Custom OAuth server implementation
- Full MCP server with tools
- Paid API integrations
- Multisite (future)
