# Quickstart: Phase 1 Discovery Runtime

## Goal

Validate runtime-only discovery behavior for API Catalog (F4) and WebMCP (F5),
including endpoint publication, frontend script emission, and graceful
compatibility fallbacks.

## Prerequisites

- WordPress 6.0+ local environment
- PHP 8.0+
- Plugin branch `004-phase1-discovery-runtime` active
- Administrator account to configure existing Phase 1 settings
- Ability to run public HTTP requests in a browser and with `curl`
- Optional WooCommerce activation for Woo-specific discovery checks

## Setup

1. Activate the plugin build containing the `004` runtime implementation.
2. In `Settings > Agent Ready`, confirm API Catalog and WebMCP settings exist
   from prior phases.
3. Enable API Catalog and WebMCP, keeping the default WordPress and WebMCP tool
   selections enabled.
4. Optionally add one custom API Catalog entry and enable WooCommerce-specific
   options when WooCommerce is active.
5. Publish at least one public post or page for frontend validation.

## Validation Scenarios

### 1. API Catalog positive path

1. Request `/.well-known/api-catalog`.
2. Confirm HTTP success with `Content-Type: application/linkset+json`.
3. Confirm the response contains the default WordPress REST API entry.
4. If a custom entry was saved, confirm it appears with the configured label and
   URLs.

### 2. WooCommerce API Catalog conditional behavior

1. Enable WooCommerce discovery inclusion while WooCommerce is active.
2. Request `/.well-known/api-catalog`.
3. Confirm the WooCommerce entry is present.
4. Deactivate WooCommerce or disable the Woo toggle and request again.
5. Confirm the WooCommerce entry is omitted while the WordPress entry remains.

### 3. API Catalog disabled behavior

1. Disable API Catalog in settings.
2. Request `/.well-known/api-catalog`.
3. Confirm the plugin does not emit generated catalog output and default
   WordPress or host behavior remains authoritative.

### 4. API Catalog physical-file conflict fallback

1. Simulate or detect a physical `ABSPATH/.well-known/api-catalog` conflict.
2. Request `/.well-known/api-catalog`.
3. Confirm the plugin does not override the physical-file/host behavior and no
   direct file mutation occurs.

### 5. WebMCP positive path

1. Load any public frontend page with WebMCP enabled.
2. Confirm the plugin enqueues or prints the WebMCP runtime asset.
3. Confirm the payload exposes `search`, `get_posts`, and `get_page`.
4. If WooCommerce is active and enabled, confirm `get_products` is also present.

### 6. WebMCP disabled behavior

1. Disable WebMCP in settings.
2. Load the same public page.
3. Confirm no WebMCP runtime asset or registration payload is emitted.

### 7. Unsupported-browser no-op behavior

1. Simulate execution in a browser environment without the required WebMCP
   capability.
2. Load a public page with WebMCP enabled.
3. Confirm the runtime asset loads but performs no tool registration and does
   not break page rendering.

## Expected Outcome

The site publishes a stable machine-readable API Catalog only when enabled,
emits WebMCP discovery only on public frontend pages when enabled, limits
tooling to saved compatible selections, and preserves default routing/rendering
whenever the feature is disabled or compatibility constraints block emission.

## Validation Outcomes (2026-04-24)

- Executed PHP smoke checks through `tests/bootstrap.php` and confirmed:
  - enabled API Catalog responses emit `application/linkset+json` with the
    default WordPress entry,
  - physical `/.well-known/api-catalog` conflicts short-circuit to fallback
    behavior with no generated response,
  - enabled WebMCP runtime requests enqueue the frontend asset and expose the
    default tool set,
  - empty tool selections short-circuit without frontend output.
- Executed `php -l` across the new discovery runtime source and test files with
  no syntax errors.
- PHPUnit execution is still pending in this environment because neither
  `phpunit` nor `vendor/bin/phpunit` is available locally.
