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
        '^\.well-known/api-catalog$',
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
```
wp_ajax_arwp_run_scan → calls API → stores transient → returns JSON to JS
```
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
