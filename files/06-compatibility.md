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
