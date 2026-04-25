# Agent Ready WP — Consolidated Plan

## Runtime Status (2026-04-24)

Phase 1 runtime foundation for F2/F3 is now implemented in plugin runtime code:
markdown negotiation is served from `template_redirect` only for qualifying
requests, and canonical `Content-Signal` emission is handled via `robots_txt`
with safe compatibility fallbacks.

This document consolidates the project specification from the files in `files/`
into a single plan that can be used with Speckit.

Source files:
- `files/01-overview.md`
- `files/02-features.md`
- `files/03-data-model.md`
- `files/04-ui-screens.md`
- `files/05-technical-implementation.md`
- `files/06-compatibility.md`

---

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

---

# Agent Ready WP — Features

## MoSCoW Prioritization

### MUST HAVE (MVP)

#### F1 — Dashboard Settings Page
- Settings page under `Settings > Agent Ready`
- Shows current scan score (via isitagentready.com API)
- Per-feature toggle (enable/disable each fix independently)
- Detects WooCommerce presence and shows relevant options
- Detects registered Custom Post Types and lists them
- "Run Scan" button that calls the API and shows live results

#### F2 — Markdown Negotiation
- Intercepts requests with `Accept: text/markdown` header
- Converts the WordPress page/post content to Markdown
- Returns response with `Content-Type: text/markdown`
- Adds `x-markdown-tokens` header with approximate token count
- HTML remains default for normal browser requests
- Supports: pages, posts, custom post types
- WooCommerce: supports product pages (title, description, price, SKU)

#### F3 — Content Signals (robots.txt)
- Appends `Content-Signal` directive to WordPress robots.txt
- Three configurable preferences via settings:
  - `ai-train` → yes / no / unset
  - `search` → yes / no / unset
  - `ai-input` → yes / no / unset
- Uses `robots_txt` filter hook (no file modification)
- Preview of final robots.txt shown in settings

#### F4 — API Catalog (`/.well-known/api-catalog`)
- Registers `/.well-known/api-catalog` as a WordPress rewrite endpoint
- Returns `application/linkset+json` with HTTP 200
- Auto-populates with WordPress REST API as the default entry:
  - `anchor`: site REST API root
  - `service-desc`: WP REST API description endpoint
  - `service-doc`: WordPress REST API Handbook URL
- WooCommerce: adds WC REST API entry if WooCommerce is active
- Admin can add custom API entries via settings UI (name + URL pairs)

#### F5 — WebMCP
- Injects a `<script>` in the frontend `<head>` via `wp_head`
- Calls `navigator.modelContext.registerTool()` for each tool
- Default tools exposed (auto-detected):
  - `search` — if WP search is enabled
  - `get_posts` — lists recent posts
  - `get_page` — retrieves a page by slug
  - `get_products` — if WooCommerce active
- Each tool has: `name`, `description`, `inputSchema`, `execute` callback
- Tools are unregistered via `AbortController` on page unload
- Toggle to enable/disable WebMCP entirely from settings

### SHOULD HAVE (Phase 2)

#### F6 — MCP Server Card (`/.well-known/mcp/server-card.json`)
- JSON endpoint at `/.well-known/mcp/server-card.json`
- Admin fills in: server name, version, transport endpoint URL
- Capabilities auto-detected from active features

#### F7 — OAuth/OIDC Discovery
- Static `/.well-known/openid-configuration` endpoint
- Admin fills in issuer, auth endpoint, token endpoint, JWKS URI
- Only shown if site has protected APIs

#### F8 — OAuth Protected Resource
- Static `/.well-known/oauth-protected-resource` endpoint
- Links to configured OAuth server

### WON'T HAVE (MVP)

- Full MCP server with real tool execution server-side
- Actual OAuth server/issuer
- Paid plan gating
- Multisite network support
- Integration with non-WP frameworks

---

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
    'enabled'          => true,
    'include_wp_rest'  => true,
    'include_woo_rest' => true, // auto-set if WC active
    'custom_entries'   => [
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
    'enabled'   => false,
    'name'      => '',
    'version'   => '1.0.0',
    'transport' => '',
  ],

  // Phase 2 — OAuth
  'oauth' => [
    'enabled'                => false,
    'issuer'                 => '',
    'authorization_endpoint' => '',
    'token_endpoint'         => '',
    'jwks_uri'               => '',
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

---

# Agent Ready WP — UI Screens

## Screen 1: Settings Page (`Settings > Agent Ready`)

### Layout

```
┌─────────────────────────────────────────────────────────┐
│  🤖 Agent Ready WP                          [Run Scan ▶] │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  LAST SCAN RESULT  ──────────────────────────────────   │
│  Score: 33  |  Level 1: Basic Web Presence              │
│  Scanned: April 23, 2026 at 5:41 PM   [Rescan]         │
│                                                         │
│  ✅ Discoverability        3/3                          │
│  ❌ Content               0/1  ← Markdown               │
│  ⚠️  Bot Access Control   1/2  ← Content Signals        │
│  ❌ API, Auth, MCP & Skill 0/6                          │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  FIXES  ────────────────────────────────────────────────│
│                                                         │
│  [✓] Markdown Negotiation              [Configure ▼]   │
│      Post types: [✓] Posts [✓] Pages [✓] Products      │
│                                                         │
│  [✓] Content Signals                  [Configure ▼]   │
│      ai-train: [no ▾]  search: [yes ▾]  ai-input: [no ▾]│
│      Preview: Content-Signal: ai-train=no, search=yes..  │
│                                                         │
│  [✓] API Catalog                      [Configure ▼]   │
│      [✓] WordPress REST API (auto)                      │
│      [✓] WooCommerce REST API (detected)                │
│      [+ Add custom API entry]                           │
│                                                         │
│  [✓] WebMCP Tools                     [Configure ▼]   │
│      [✓] search  [✓] get_posts  [✓] get_page           │
│      [✓] get_products (WooCommerce detected)            │
│                                                         │
│  [─] MCP Server Card         Phase 2 — Coming Soon      │
│  [─] OAuth Discovery         Phase 2 — Coming Soon      │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                              [Save Settings]            │
└─────────────────────────────────────────────────────────┘
```

---

## UX Notes

### Scan Result Panel
- Pulled from `agent_ready_wp_scan_cache` transient
- If no scan yet → shows "No scan yet. Click Run Scan."
- "Run Scan" button → AJAX call → hits `isitagentready.com/api/scan`
  with current site URL → updates transient → refreshes panel
- Each check maps to a feature toggle: clicking the check label
  scrolls to the relevant Fix section

### Feature Toggles
- Each feature has a master on/off toggle
- Expanding [Configure] reveals sub-options
- Settings save via standard WP `options.php` form (no AJAX needed)
- On save: flush rewrite rules if API Catalog was toggled

### WooCommerce Detection
- If `class_exists('WooCommerce')` → show WC-specific options
- If WooCommerce deactivated after saving → WC options remain
  but are grayed out with notice: "WooCommerce not active"

### CPT Detection
- Post type selector shows all public CPTs registered on the site
- Built-in types (post, page) always shown
- WC `product` shown separately under WooCommerce section

---

## Screen 2: No Separate Screens

All configuration happens in one settings page.
No metaboxes, no post-level settings, no frontend UI.

---

# Agent Ready WP — Technical Implementation

## F2: Markdown Negotiation

### Hook
`template_redirect` (priority 1 — before WP sends headers)

### Logic
```php
// pseudo-code
if ( str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'text/markdown') ) {
    $post = get_queried_object();
    if ( is_singular() && $post instanceof WP_Post ) {
        $markdown = arwp_convert_to_markdown( $post );
        $tokens   = arwp_count_tokens( $markdown );
        header('Content-Type: text/markdown; charset=utf-8');
        header('x-markdown-tokens: ' . $tokens);
        echo $markdown;
        exit;
    }
}
```

### Markdown Conversion Strategy
No external library needed. Convert using WP filters:
1. `apply_filters('the_content', $post->post_content)` → get rendered HTML
2. Strip HTML tags with a simple regex/DOMDocument conversion:
   - `<h1>–<h6>` → `# title`
   - `<p>` → blank line separation
   - `<a href="...">text</a>` → `[text](href)`
   - `<img>` → `![alt](src)`
   - `<ul>/<li>` → `- item`
   - `<ol>/<li>` → `1. item`
   - `<strong>` → `**text**`
   - `<em>` → `*text*`
   - Everything else → strip tags
3. Prepend: `# {post_title}\n\n`
4. For WooCommerce products: append price, SKU, stock status

### Token Count
Approximate: `ceil(str_word_count($markdown) * 1.33)`
(rough GPT tokenization estimate — sufficient for the header)

---

## F3: Content Signals

### Hook
`robots_txt` filter

```php
add_filter('robots_txt', function( $output ) {
    $settings = get_option('agent_ready_wp_settings');
    $signals  = $settings['content_signals'];
    if ( ! $signals['enabled'] ) return $output;

    $parts = [];
    foreach (['ai_train', 'search', 'ai_input'] as $key) {
        $label = str_replace('_', '-', $key);
        if ( $signals[$key] !== '' ) {
            $parts[] = "{$label}={$signals[$key]}";
        }
    }
    if ( $parts ) {
        $output .= "\nContent-Signal: " . implode(', ', $parts) . "\n";
    }
    return $output;
}, 10, 1);
```

### Important
WordPress's built-in `robots_txt` is only served if:
- No physical `robots.txt` file exists in the root
- OR the file exists and WP is not configured to generate it

**If a physical file exists:** show admin notice warning
"A physical robots.txt file was found. Content Signals cannot be added
automatically. Please add the directive manually."

---

## F4: API Catalog

### Endpoint Registration
```php
// register_activation_hook → flush_rewrite_rules()
add_action('init', function() {
    add_rewrite_rule(
        '^\\.well-known/api-catalog$',
        'index.php?arwp_api_catalog=1',
        'top'
    );
});
add_filter('query_vars', fn($v) => [...$v, 'arwp_api_catalog']);
add_action('template_redirect', 'arwp_serve_api_catalog');
```

### Response Format (RFC 9727)
```json
{
  "linkset": [
    {
      "anchor": "https://example.com/wp-json/",
      "service-desc": [{ "href": "https://example.com/wp-json/" }],
      "service-doc": [{
        "href": "https://developer.wordpress.org/rest-api/",
        "type": "text/html"
      }]
    }
  ]
}
```
Headers: `Content-Type: application/linkset+json`, `HTTP 200`

### WooCommerce Entry (auto-added if WC active)
```json
{
  "anchor": "https://example.com/wp-json/wc/v3/",
  "service-desc": [{ "href": "https://example.com/wp-json/wc/v3/" }],
  "service-doc": [{
    "href": "https://woocommerce.github.io/woocommerce-rest-api-docs/",
    "type": "text/html"
  }]
}
```

---

## F5: WebMCP

### Hook
`wp_head` (priority 20)

### Output
```html
<script>
(function() {
  if (!navigator.modelContext) return;
  const ac = new AbortController();
  const { signal } = ac;

  navigator.modelContext.registerTool({
    name: 'search',
    description: 'Search the website for posts and pages',
    inputSchema: {
      type: 'object',
      properties: {
        query: { type: 'string', description: 'Search query' }
      },
      required: ['query']
    },
    execute: async ({ query }) => {
      const r = await fetch(`<?php echo rest_url('wp/v2/search'); ?>?search=${encodeURIComponent(query)}`);
      return r.json();
    },
    signal
  });

  // get_posts tool
  navigator.modelContext.registerTool({
    name: 'get_posts',
    description: 'Get recent posts from the site',
    inputSchema: {
      type: 'object',
      properties: {
        per_page: { type: 'number', default: 5 }
      }
    },
    execute: async ({ per_page = 5 }) => {
      const r = await fetch(`<?php echo rest_url('wp/v2/posts'); ?>?per_page=${per_page}&_fields=id,title,link,excerpt,date`);
      return r.json();
    },
    signal
  });

  // get_products (only if WooCommerce active — PHP conditional)
  <?php if ( arwp_woocommerce_active() && $settings['webmcp']['tools']['get_products'] ) : ?>
  navigator.modelContext.registerTool({
    name: 'get_products',
    description: 'Get products from the WooCommerce store',
    inputSchema: {
      type: 'object',
      properties: {
        per_page: { type: 'number', default: 5 },
        search: { type: 'string' }
      }
    },
    execute: async ({ per_page = 5, search = '' }) => {
      const url = new URL(`<?php echo rest_url('wc/v3/products'); ?>`);
      url.searchParams.set('per_page', per_page);
      if (search) url.searchParams.set('search', search);
      const r = await fetch(url);
      return r.json();
    },
    signal
  });
  <?php endif; ?>

  window.addEventListener('unload', () => ac.abort());
})();
</script>
```

### Security Note
WC REST API requires auth for most endpoints in production.
WebMCP tool description should note: "Public product listing only."
Consider using `wc/store/v1/products` (no auth needed) instead.

---

## API Scan Integration

### Endpoint
`POST https://isitagentready.com/api/scan`
Body: `{"url": "https://example.com"}`

### WordPress Integration
```php
$response = wp_remote_post('https://isitagentready.com/api/scan', [
    'headers' => ['Content-Type' => 'application/json'],
    'body'    => wp_json_encode(['url' => get_site_url()]),
    'timeout' => 30,
]);
```

### AJAX Handler
`wp_ajax_arwp_run_scan` → calls API → stores transient → returns JSON to JS

Nonce protected. Admin-only (`manage_options` capability).

---

## Activation / Deactivation

### On Activation
```php
register_activation_hook(__FILE__, function() {
    // Set default settings if not exist
    if ( ! get_option('agent_ready_wp_settings') ) {
        update_option('agent_ready_wp_settings', arwp_default_settings());
    }
    flush_rewrite_rules();
});
```

### On Deactivation
```php
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
    // Do NOT delete settings on deactivate
});
```

### On Uninstall (`uninstall.php`)
```php
delete_option('agent_ready_wp_settings');
delete_transient('agent_ready_wp_scan_cache');
```

---

# Agent Ready WP — Compatibility

## WordPress Compatibility Matrix

| Scenario | Markdown | Content Signals | API Catalog | WebMCP |
|---|---|---|---|---|
| Plain WordPress (posts/pages) | ✅ | ✅ | ✅ WP REST | ✅ search, get_posts, get_page |
| WordPress + Custom Post Types | ✅ (user selects CPTs) | ✅ | ✅ WP REST | ✅ + get_{cpt} (generic) |
| WordPress + WooCommerce | ✅ + products | ✅ | ✅ WP + WC REST | ✅ + get_products |

---

## Detection Helpers

```php
// WooCommerce
function arwp_woocommerce_active(): bool {
    return class_exists('WooCommerce');
}

// Get user-relevant public CPTs (excluding built-ins and WC internals)
function arwp_get_public_cpts(): array {
    $all = get_post_types(['public' => true, '_builtin' => false], 'objects');
    $exclude = ['product_variation', 'shop_order', 'shop_coupon'];
    return array_filter($all, fn($pt) => !in_array($pt->name, $exclude));
}
```

---

## Physical robots.txt Conflict

If `file_exists(ABSPATH . 'robots.txt')`:
- Content Signals toggle still shows as enabled in UI
- Admin notice displayed: yellow warning
- Notice text: "A physical robots.txt file exists on your server.
  WordPress cannot modify it automatically. To fix this, add the
  following line to your robots.txt manually: [preview line]"
- "Content Signals" check in scan results will remain ❌ until fixed manually

---

## `.well-known/` Conflicts

**Potential conflict:** Some hosts pre-configure `.well-known/` for SSL certs
(Let's Encrypt), 2FA, or other tools.

**Our approach:** Use WordPress rewrite rules, not physical files.
The rewrite only fires for `/.well-known/api-catalog` specifically.
Let's Encrypt uses `/.well-known/acme-challenge/` — no conflict.

If a physical file at `/.well-known/api-catalog` exists on the server,
the rewrite rule won't fire. Show admin notice.

---

## Theme Compatibility

- `wp_head` hook: required for WebMCP script injection
  → All themes using `wp_head()` in `<head>` are compatible
  → Block themes (FSE) use `wp_head` too — compatible
- `robots_txt` filter: works regardless of theme
- `template_redirect`: works regardless of theme

---

## Caching Plugin Compatibility

| Plugin | Concern | Notes |
|---|---|---|
| WP Rocket | May cache Markdown responses | Not an issue: Markdown served via `template_redirect` with custom headers. WP Rocket doesn't cache requests with custom `Accept` headers by default. |
| W3 Total Cache | Same | Same behavior — custom Accept headers bypass page cache. |
| LiteSpeed Cache | Same | LSCACHE respects Vary headers. No issue. |
| Cloudflare Page Cache | Could cache incorrectly | Recommend adding `Vary: Accept` header in plugin output. ✅ Already included in implementation. |

**Add to Markdown response:**
```php
header('Vary: Accept');
```

---

## WordPress.org Plugin Review Checklist

- [ ] No `eval()`
- [ ] All DB queries use `$wpdb->prepare()`
- [ ] All user inputs sanitized (`sanitize_text_field`, etc.)
- [ ] All outputs escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_json_encode`)
- [ ] Nonces on all forms and AJAX
- [ ] Capability checks (`current_user_can('manage_options')`) on all admin actions
- [ ] `uninstall.php` cleans up all options/transients
- [ ] No hardcoded URLs (use `get_site_url()`, `rest_url()`, etc.)
- [ ] i18n: all strings wrapped in `__()` or `_e()` with `'agent-ready-wp'` text domain
- [ ] Prefix all functions/classes/globals with `arwp_` or `ARWP_`
- [ ] `readme.txt` in WordPress.org format with changelog
- [ ] No external files loaded from CDNs not on WP approved list
- [ ] `wp_enqueue_scripts` / `wp_enqueue_style` for all assets (no direct `<script>` in PHP except WebMCP inline script via `wp_add_inline_script`)

---

## Speckit Continuation Order

The existing numbered specs cover the admin page only:

- `001-foundation-architecture-page`
- `002-phase2-foundation-page`

Continue the missing implementation work with runtime-focused specs starting at
`003`. Do not pre-create numbered spec directories manually; let
`/speckit.specify` allocate the next sequential number.

### Ordered runtime sequence

1. **`003` — Phase 1 Runtime Foundation**
   - Scope: `F2` Markdown Negotiation and `F3` Content Signals runtime behavior
   - Excludes: admin-page behavior already covered in `001`, plus `F4` through
     `F8`
2. **`004` — Phase 1 Discovery Runtime**
   - Scope: `F4` API Catalog and `F5` WebMCP runtime behavior
   - Excludes: admin-page behavior already covered in `001`, plus `F2`, `F3`,
     and `F6` through `F8`
3. **`005` — Phase 2 Public Discovery Endpoints**
   - Scope: `F6` MCP Server Card, `F7` OAuth/OIDC Discovery, and `F8` OAuth
     Protected Resource runtime behavior
   - Excludes: admin-page behavior already covered in `002`

Use the exact prompts and workflow in
[`SPECKIT_SEQUENCE.md`](./SPECKIT_SEQUENCE.md).
