<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', sys_get_temp_dir() . '/wordpress/');
}

if (! defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

if (! defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

$GLOBALS['arwp_test_options']    = [];
$GLOBALS['arwp_test_transients'] = [];
$GLOBALS['arwp_test_actions']    = [];
$GLOBALS['arwp_test_filters']    = [];
$GLOBALS['arwp_test_settings']   = [];
$GLOBALS['arwp_test_update_option_calls'] = 0;
$GLOBALS['arwp_test_flush_rewrite_rules_calls'] = 0;
$GLOBALS['arwp_test_current_user_can'] = true;
$GLOBALS['arwp_test_current_user_caps'] = [];
$GLOBALS['arwp_test_is_singular'] = false;
$GLOBALS['arwp_test_query_object'] = null;
$GLOBALS['arwp_test_post_password_required'] = false;
$GLOBALS['arwp_test_registered_scripts'] = [];
$GLOBALS['arwp_test_registered_styles'] = [];
$GLOBALS['arwp_test_localized_scripts'] = [];
$GLOBALS['arwp_test_enqueued_scripts'] = [];
$GLOBALS['arwp_test_enqueued_styles'] = [];
$GLOBALS['arwp_test_rewrite_rules'] = [];
$GLOBALS['arwp_test_query_vars'] = [];
$GLOBALS['arwp_test_request_query_vars'] = [];
$GLOBALS['arwp_test_is_admin'] = false;
$GLOBALS['arwp_test_is_feed'] = false;
$GLOBALS['arwp_test_is_frontend'] = true;
$GLOBALS['arwp_test_actions_fired'] = [];
$GLOBALS['arwp_test_woocommerce_active'] = false;

if (! function_exists('__')) {
    function __(string $text, ?string $domain = null): string
    {
        return $text;
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__(string $text, ?string $domain = null): string
    {
        return $text;
    }
}

if (! function_exists('esc_attr__')) {
    function esc_attr__(string $text, ?string $domain = null): string
    {
        return $text;
    }
}

if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $text): string
    {
        return trim(strip_tags($text));
    }
}

if (! function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        return preg_replace('/[^a-z0-9_\\-]/', '', strtolower($key)) ?: '';
    }
}

if (! function_exists('esc_url_raw')) {
    function esc_url_raw(string $url): string
    {
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }
}

if (! function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_textarea')) {
    function esc_textarea(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('wp_json_encode')) {
    function wp_json_encode($value): string|false
    {
        return json_encode($value);
    }
}

if (! function_exists('wp_kses_post')) {
    function wp_kses_post(string $html): string
    {
        return $html;
    }
}

if (! function_exists('checked')) {
    function checked($checked, $current = true, bool $display = true): string
    {
        return $checked === $current ? 'checked="checked"' : '';
    }
}

if (! function_exists('selected')) {
    function selected($selected, $current = true, bool $display = true): string
    {
        return $selected === $current ? 'selected="selected"' : '';
    }
}

if (! function_exists('get_option')) {
    function get_option(string $name, $default = false)
    {
        return $GLOBALS['arwp_test_options'][$name] ?? $default;
    }
}

if (! function_exists('update_option')) {
    function update_option(string $name, $value): bool
    {
        $value = apply_filters('sanitize_option_' . $name, $value, $name, null);
        $GLOBALS['arwp_test_options'][$name] = $value;
        $GLOBALS['arwp_test_update_option_calls'] = (int) ($GLOBALS['arwp_test_update_option_calls'] ?? 0) + 1;
        return true;
    }
}

if (! function_exists('get_transient')) {
    function get_transient(string $name)
    {
        return $GLOBALS['arwp_test_transients'][$name] ?? false;
    }
}

if (! function_exists('set_transient')) {
    function set_transient(string $name, $value, int $expiration = 0): bool
    {
        $GLOBALS['arwp_test_transients'][$name] = $value;
        return true;
    }
}

if (! function_exists('delete_transient')) {
    function delete_transient(string $name): bool
    {
        unset($GLOBALS['arwp_test_transients'][$name]);
        return true;
    }
}

if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $GLOBALS['arwp_test_actions'][$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args,
        ];
    }
}

if (! function_exists('do_action')) {
    function do_action(string $hook, ...$args): void
    {
        $GLOBALS['arwp_test_actions_fired'][] = $hook;
        $actions = $GLOBALS['arwp_test_actions'][$hook] ?? [];
        if (! is_array($actions) || $actions === []) {
            return;
        }

        ksort($actions);

        foreach ($actions as $items) {
            foreach ($items as $item) {
                $acceptedArgs = (int) ($item['accepted_args'] ?? 1);
                $callArgs     = array_slice($args, 0, $acceptedArgs);
                ($item['callback'])(...$callArgs);
            }
        }
    }
}

if (! function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $GLOBALS['arwp_test_filters'][$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args,
        ];
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook, $value, ...$args)
    {
        $filters = $GLOBALS['arwp_test_filters'][$hook] ?? [];
        if (! is_array($filters) || $filters === []) {
            return $value;
        }

        ksort($filters);

        foreach ($filters as $items) {
            foreach ($items as $item) {
                $acceptedArgs = (int) ($item['accepted_args'] ?? 1);
                $callArgs     = array_slice(array_merge([$value], $args), 0, $acceptedArgs);
                $value        = ($item['callback'])(...$callArgs);
            }
        }

        return $value;
    }
}

if (! function_exists('register_setting')) {
    function register_setting(string $group, string $name, array $args = []): void
    {
        $GLOBALS['arwp_test_settings'][$group][$name] = $args;
    }
}

if (! function_exists('add_options_page')) {
    function add_options_page(string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback): string
    {
        $GLOBALS['arwp_test_menu'][$menu_slug] = $callback;
        return 'settings_page_' . $menu_slug;
    }
}

if (! function_exists('settings_fields')) {
    function settings_fields(string $group): void
    {
        echo '<input type="hidden" name="option_page" value="' . esc_attr($group) . '" />';
    }
}

if (! function_exists('submit_button')) {
    function submit_button(string $text): void
    {
        echo '<button type="submit">' . esc_html($text) . '</button>';
    }
}

if (! function_exists('current_user_can')) {
    function current_user_can(string $capability): bool
    {
        if (isset($GLOBALS['arwp_test_current_user_caps'][$capability])) {
            return (bool) $GLOBALS['arwp_test_current_user_caps'][$capability];
        }

        return (bool) ($GLOBALS['arwp_test_current_user_can'] ?? true);
    }
}

if (! function_exists('is_singular')) {
    function is_singular($post_types = null): bool
    {
        return (bool) ($GLOBALS['arwp_test_is_singular'] ?? false);
    }
}

if (! function_exists('get_queried_object')) {
    function get_queried_object()
    {
        return $GLOBALS['arwp_test_query_object'] ?? null;
    }
}

if (! function_exists('post_password_required')) {
    function post_password_required($post = null): bool
    {
        return (bool) ($GLOBALS['arwp_test_post_password_required'] ?? false);
    }
}

if (! function_exists('wp_die')) {
    function wp_die(string $message): void
    {
        throw new RuntimeException($message);
    }
}

if (! function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        return 'https://example.com/wp-admin/' . ltrim($path, '/');
    }
}

if (! function_exists('get_site_url')) {
    function get_site_url(): string
    {
        return 'https://example.com';
    }
}

if (! function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        return rtrim(get_site_url(), '/') . '/' . ltrim($path, '/');
    }
}

if (! function_exists('rest_url')) {
    function rest_url(string $path = ''): string
    {
        return rtrim(get_site_url(), '/') . '/wp-json/' . ltrim($path, '/');
    }
}

if (! function_exists('current_time')) {
    function current_time(string $type): string
    {
        return '2026-04-23 17:41:26';
    }
}

if (! function_exists('get_post_types')) {
    function get_post_types(array $args = [], string $output = 'names'): array
    {
        return [
            (object) ['name' => 'book'],
        ];
    }
}

if (! function_exists('plugin_basename')) {
    function plugin_basename(string $file): string
    {
        return basename($file);
    }
}

if (! function_exists('plugin_dir_path')) {
    function plugin_dir_path(string $file): string
    {
        return dirname($file) . '/';
    }
}

if (! function_exists('plugin_dir_url')) {
    function plugin_dir_url(string $file): string
    {
        return 'https://example.com/wp-content/plugins/agent-ready-wp/';
    }
}

if (! function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain(string $domain, bool $deprecated = false, string $path = ''): bool
    {
        return true;
    }
}

if (! function_exists('register_activation_hook')) {
    function register_activation_hook(string $file, callable $callback): void
    {
    }
}

if (! function_exists('register_deactivation_hook')) {
    function register_deactivation_hook(string $file, callable $callback): void
    {
    }
}

if (! function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules(): void
    {
        $GLOBALS['arwp_test_flush_rewrite_rules_calls'] = (int) ($GLOBALS['arwp_test_flush_rewrite_rules_calls'] ?? 0) + 1;
    }
}

if (! function_exists('add_rewrite_rule')) {
    function add_rewrite_rule(string $regex, string $query, string $after = 'bottom'): void
    {
        $GLOBALS['arwp_test_rewrite_rules'][] = [
            'regex' => $regex,
            'query' => $query,
            'after' => $after,
        ];
    }
}

if (! function_exists('get_query_var')) {
    function get_query_var(string $key, $default = '')
    {
        return $GLOBALS['arwp_test_request_query_vars'][$key] ?? $default;
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return (bool) ($GLOBALS['arwp_test_is_admin'] ?? false);
    }
}

if (! function_exists('is_feed')) {
    function is_feed(): bool
    {
        return (bool) ($GLOBALS['arwp_test_is_feed'] ?? false);
    }
}

if (! function_exists('wp_doing_ajax')) {
    function wp_doing_ajax(): bool
    {
        return (bool) ($GLOBALS['arwp_test_doing_ajax'] ?? false);
    }
}

if (! function_exists('wp_doing_cron')) {
    function wp_doing_cron(): bool
    {
        return (bool) ($GLOBALS['arwp_test_doing_cron'] ?? false);
    }
}

if (! function_exists('wp_register_script')) {
    function wp_register_script(string $handle, string $src, array $deps = [], $ver = false, bool $in_footer = false): void
    {
        $GLOBALS['arwp_test_registered_scripts'][$handle] = [
            'src'       => $src,
            'deps'      => $deps,
            'ver'       => $ver,
            'in_footer' => $in_footer,
        ];
    }
}

if (! function_exists('wp_register_style')) {
    function wp_register_style(string $handle, string $src, array $deps = [], $ver = false): void
    {
        $GLOBALS['arwp_test_registered_styles'][$handle] = [
            'src'  => $src,
            'deps' => $deps,
            'ver'  => $ver,
        ];
    }
}

if (! function_exists('wp_localize_script')) {
    function wp_localize_script(string $handle, string $object_name, array $l10n): void
    {
        $GLOBALS['arwp_test_localized_scripts'][$handle][$object_name] = $l10n;
    }
}

if (! function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle): void
    {
        $GLOBALS['arwp_test_enqueued_scripts'][] = $handle;
    }
}

if (! function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle): void
    {
        $GLOBALS['arwp_test_enqueued_styles'][] = $handle;
    }
}

if (! function_exists('wp_remote_post')) {
    function wp_remote_post(string $url, array $args = []): array
    {
        return [
            'response' => ['code' => 200],
            'body'     => file_get_contents(__DIR__ . '/fixtures/scan-response-success.json'),
        ];
    }
}

if (! function_exists('is_wp_error')) {
    function is_wp_error($thing): bool
    {
        return $thing instanceof WP_Error;
    }
}

if (! function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code(array $response): int
    {
        return (int) ($response['response']['code'] ?? 0);
    }
}

if (! function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body(array $response): string
    {
        return (string) ($response['body'] ?? '');
    }
}

if (! class_exists('WP_Error')) {
    class WP_Error
    {
        public function __construct(private string $code = '', private string $message = '')
        {
        }

        public function get_error_message(): string
        {
            return $this->message;
        }

        public function get_error_messages(): array
        {
            return [$this->message];
        }
    }
}

if (! function_exists('check_ajax_referer')) {
    function check_ajax_referer(string $action, $query_arg = false): bool
    {
        return true;
    }
}

if (! function_exists('wp_send_json_success')) {
    function wp_send_json_success(array $data = [], int $status_code = 200): void
    {
        throw new RuntimeException(json_encode(['success' => true, 'data' => $data]));
    }
}

if (! function_exists('wp_send_json_error')) {
    function wp_send_json_error(array $data = [], int $status_code = 400): void
    {
        throw new RuntimeException(json_encode(['success' => false, 'data' => $data]));
    }
}

if (! function_exists('wp_create_nonce')) {
    function wp_create_nonce(string $action): string
    {
        return 'nonce';
    }
}

if (! function_exists('wp_nonce_field')) {
    function wp_nonce_field(string $action, string $name = '_wpnonce'): void
    {
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="nonce" />';
    }
}

if (! function_exists('wp_verify_nonce')) {
    function wp_verify_nonce(string $nonce, string $action): bool
    {
        return $nonce === 'nonce';
    }
}

if (! function_exists('arwp_tests_set_current_user_can')) {
    function arwp_tests_set_current_user_can(bool $allowed): void
    {
        $GLOBALS['arwp_test_current_user_can'] = $allowed;
        $GLOBALS['arwp_test_current_user_caps'] = [];
    }
}

if (! function_exists('arwp_tests_set_capability')) {
    function arwp_tests_set_capability(string $capability, bool $allowed): void
    {
        $GLOBALS['arwp_test_current_user_caps'][$capability] = $allowed;
    }
}

if (! function_exists('arwp_tests_set_runtime_request')) {
    function arwp_tests_set_runtime_request(string $acceptHeader, bool $isSingular, ?object $queryObject): void
    {
        $_SERVER['HTTP_ACCEPT'] = $acceptHeader;
        $GLOBALS['arwp_test_is_singular'] = $isSingular;
        $GLOBALS['arwp_test_query_object'] = $queryObject;
    }
}

if (! function_exists('arwp_tests_set_request_uri')) {
    function arwp_tests_set_request_uri(string $requestUri): void
    {
        $_SERVER['REQUEST_URI'] = $requestUri;
    }
}

if (! function_exists('arwp_tests_set_system_request_flags')) {
    function arwp_tests_set_system_request_flags(bool $isAjax = false, bool $isCron = false): void
    {
        $GLOBALS['arwp_test_doing_ajax'] = $isAjax;
        $GLOBALS['arwp_test_doing_cron'] = $isCron;
    }
}

if (! function_exists('arwp_tests_set_query_var')) {
    function arwp_tests_set_query_var(string $key, mixed $value): void
    {
        $GLOBALS['arwp_test_request_query_vars'][$key] = $value;
    }
}

if (! function_exists('arwp_tests_apply_rewrite_match')) {
    function arwp_tests_apply_rewrite_match(string $requestPath): bool
    {
        $normalizedPath = ltrim($requestPath, '/');

        foreach ((array) ($GLOBALS['arwp_test_rewrite_rules'] ?? []) as $rule) {
            $regex = (string) ($rule['regex'] ?? '');
            $query = (string) ($rule['query'] ?? '');

            if ($regex === '' || $query === '') {
                continue;
            }

            if (! preg_match('#' . $regex . '#', $normalizedPath)) {
                continue;
            }

            $queryString = $query;
            if (str_contains($queryString, '?')) {
                $parts = explode('?', $queryString, 2);
                $queryString = $parts[1] ?? '';
            }

            $resolved = [];
            parse_str($queryString, $resolved);

            foreach ($resolved as $key => $value) {
                $GLOBALS['arwp_test_request_query_vars'][(string) $key] = $value;
            }

            return true;
        }

        return false;
    }
}

if (! function_exists('arwp_tests_set_frontend_context')) {
    function arwp_tests_set_frontend_context(bool $isFrontend = true, bool $isAdmin = false, bool $isFeed = false): void
    {
        $GLOBALS['arwp_test_is_frontend'] = $isFrontend;
        $GLOBALS['arwp_test_is_admin']    = $isAdmin;
        $GLOBALS['arwp_test_is_feed']     = $isFeed;
    }
}

if (! function_exists('arwp_tests_set_woocommerce_active')) {
    function arwp_tests_set_woocommerce_active(bool $active): void
    {
        $GLOBALS['arwp_test_woocommerce_active'] = $active;
    }
}

if (! function_exists('arwp_tests_get_localized_script')) {
    /**
     * @return array<string,mixed>
     */
    function arwp_tests_get_localized_script(string $handle, string $objectName): array
    {
        $data = $GLOBALS['arwp_test_localized_scripts'][$handle][$objectName] ?? [];

        return is_array($data) ? $data : [];
    }
}

if (! function_exists('arwp_tests_set_post_password_required')) {
    function arwp_tests_set_post_password_required(bool $required): void
    {
        $GLOBALS['arwp_test_post_password_required'] = $required;
    }
}

if (! function_exists('arwp_tests_phase2_valid_payload')) {
    /**
     * @return array<string,mixed>
     */
    function arwp_tests_phase2_valid_payload(): array
    {
        return [
            'protected_apis' => [
                'enabled' => '1',
            ],
            'mcp_server_card' => [
                'enabled' => '1',
                'name' => 'Agent Ready WP',
                'version' => '1.0.0',
                'transport' => 'https://example.com/mcp',
            ],
            'oauth' => [
                'enabled' => '1',
                'issuer' => 'https://example.com',
                'authorization_endpoint' => 'https://example.com/oauth/authorize',
                'token_endpoint' => 'https://example.com/oauth/token',
                'jwks_uri' => 'https://example.com/.well-known/jwks.json',
            ],
            'protected_resource' => [
                'enabled' => '1',
                'resource' => 'https://example.com/resource',
                'authorization_servers' => "https://auth.example.com\nhttps://backup.example.com",
            ],
        ];
    }
}

if (! function_exists('arwp_tests_phase2_invalid_payload')) {
    /**
     * @return array<string,mixed>
     */
    function arwp_tests_phase2_invalid_payload(): array
    {
        $json = file_get_contents(__DIR__ . '/fixtures/phase2-invalid-settings.json');
        $data = json_decode((string) $json, true);

        return is_array($data) ? $data : [];
    }
}

if (! function_exists('arwp_tests_phase2_preview_fixture')) {
    /**
     * @return array<string,mixed>
     */
    function arwp_tests_phase2_preview_fixture(): array
    {
        $json = file_get_contents(__DIR__ . '/fixtures/phase2-preview-draft.json');
        $data = json_decode((string) $json, true);

        return is_array($data) ? $data : [];
    }
}

if (! function_exists('arwp_tests_fixture_json')) {
    /**
     * @return array<string,mixed>
     */
    function arwp_tests_fixture_json(string $filename): array
    {
        $json = file_get_contents(__DIR__ . '/fixtures/' . ltrim($filename, '/'));
        $data = json_decode((string) $json, true);

        return is_array($data) ? $data : [];
    }
}

if (! function_exists('arwp_tests_phase2_runtime_settings')) {
    /**
     * @return array<string,mixed>
     */
    function arwp_tests_phase2_runtime_settings(): array
    {
        $settings = AgentReadyWP\Application\Settings\Defaults::all();

        $settings['protected_apis']['enabled'] = true;
        $settings['mcp_server_card']['enabled'] = true;
        $settings['mcp_server_card']['name'] = 'Agent Ready WP';
        $settings['mcp_server_card']['version'] = '1.0.0';
        $settings['mcp_server_card']['transport'] = 'https://example.com/wp-json/agent-ready/v1/mcp';
        $settings['oauth']['enabled'] = true;
        $settings['oauth']['issuer'] = 'https://example.com';
        $settings['oauth']['authorization_endpoint'] = 'https://auth.example.com/authorize';
        $settings['oauth']['token_endpoint'] = 'https://auth.example.com/token';
        $settings['oauth']['jwks_uri'] = 'https://auth.example.com/.well-known/jwks.json';
        $settings['protected_resource']['enabled'] = true;
        $settings['protected_resource']['resource'] = 'https://example.com/wp-json/agent-ready/v1';
        $settings['protected_resource']['authorization_servers'] = [
            'https://auth.example.com',
        ];

        return $settings;
    }
}

if (! function_exists('arwp_tests_create_well_known_file')) {
    function arwp_tests_create_well_known_file(string $relativePath, string $contents = 'static'): string
    {
        $path = ABSPATH . '.well-known/' . ltrim($relativePath, '/');
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, $contents);

        return $path;
    }
}

if (! function_exists('arwp_tests_remove_well_known_file')) {
    function arwp_tests_remove_well_known_file(string $relativePath): void
    {
        $path = ABSPATH . '.well-known/' . ltrim($relativePath, '/');

        if (file_exists($path)) {
            unlink($path);
        }
    }
}

if (! function_exists('arwp_tests_reset_request')) {
    function arwp_tests_reset_request(): void
    {
        $_POST = [];
        $_SERVER['HTTP_ACCEPT'] = '';
        $_SERVER['REQUEST_URI'] = '/';
        $GLOBALS['arwp_test_is_singular'] = false;
        $GLOBALS['arwp_test_query_object'] = null;
        $GLOBALS['arwp_test_post_password_required'] = false;
        $GLOBALS['arwp_test_registered_scripts'] = [];
        $GLOBALS['arwp_test_registered_styles'] = [];
        $GLOBALS['arwp_test_localized_scripts'] = [];
        $GLOBALS['arwp_test_enqueued_scripts'] = [];
        $GLOBALS['arwp_test_enqueued_styles'] = [];
        $GLOBALS['arwp_test_rewrite_rules'] = [];
        $GLOBALS['arwp_test_query_vars'] = [];
        $GLOBALS['arwp_test_request_query_vars'] = [];
        $GLOBALS['arwp_test_is_admin'] = false;
        $GLOBALS['arwp_test_is_feed'] = false;
        $GLOBALS['arwp_test_is_frontend'] = true;
        $GLOBALS['arwp_test_doing_ajax'] = false;
        $GLOBALS['arwp_test_doing_cron'] = false;
        $GLOBALS['arwp_test_actions_fired'] = [];
        $GLOBALS['arwp_test_woocommerce_active'] = false;
        $GLOBALS['arwp_test_flush_rewrite_rules_calls'] = 0;
    }
}

require_once dirname(__DIR__) . '/agent-ready-wp.php';
