# Agent Ready WP — Data Model

## Storage Strategy

No custom database tables. Everything stored in `wp_options` using a single
serialized option key for settings, plus individual transients for scan cache.

---

## Options

### `agent_ready_wp_settings`
Type: `array` (serialized)

```php
[
  // Global
  'enabled' => true,

  // F2 — Markdown Negotiation
  'markdown' => [
    'enabled'          => true,
    'post_types'       => ['post', 'page'], // user-selected CPTs
    'include_woo'      => true,             // auto-set if WC active
  ],

  // F3 — Content Signals
  'content_signals' => [
    'enabled'  => true,
    'ai_train' => 'no',     // 'yes' | 'no' | ''
    'search'   => 'yes',    // 'yes' | 'no' | ''
    'ai_input' => 'no',     // 'yes' | 'no' | ''
  ],

  // F4 — API Catalog
  'api_catalog' => [
    'enabled'        => true,
    'include_wp_rest'  => true,
    'include_woo_rest' => true, // auto-set if WC active
    'custom_entries' => [
      // [ 'name' => 'My API', 'anchor' => 'https://...', 'service_desc' => '...' ]
    ],
  ],

  // F5 — WebMCP
  'webmcp' => [
    'enabled' => true,
    'tools'   => [
      'search'       => true,
      'get_posts'    => true,
      'get_page'     => true,
      'get_products' => true, // auto-set if WC active
    ],
  ],

  // Phase 2 — MCP Server Card
  'mcp_server_card' => [
    'enabled'    => false,
    'name'       => '',
    'version'    => '1.0.0',
    'transport'  => '',
  ],

  // Phase 2 — OAuth
  'oauth' => [
    'enabled'              => false,
    'issuer'               => '',
    'authorization_endpoint' => '',
    'token_endpoint'       => '',
    'jwks_uri'             => '',
  ],
]
```

---

### `agent_ready_wp_scan_cache`
Type: `transient` — expires every 6 hours

Stores the last JSON response from `https://isitagentready.com/api/scan`
keyed by site URL. Used to display score in dashboard without hitting
the API on every page load.

```php
[
  'url'        => 'https://example.com',
  'score'      => 33,
  'level'      => 1,
  'level_name' => 'Basic Web Presence',
  'checks'     => [ /* full API response */ ],
  'scanned_at' => '2026-04-23T17:41:26Z',
]
```

---

## No DB Tables

Reasons:
- Settings are flat and non-relational
- No per-user or per-post data needed in MVP
- Simpler activation/deactivation/uninstall
- WordPress.org review-friendly
