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
