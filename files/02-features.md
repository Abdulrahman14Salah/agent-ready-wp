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
