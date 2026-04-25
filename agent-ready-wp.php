<?php
/**
 * Plugin Name: Agent Ready WP
 * Description: Adds an admin settings page for configuring and reviewing agent-readiness signals.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: ArqamWeb
 * Author URI: https://arqamweb.com
 * License: GPL-2.0+
 * Text Domain: agent-ready-wp
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('ARWP_PLUGIN_FILE', __FILE__);
define('ARWP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('ARWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ARWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ARWP_VERSION', '0.1.0');
define('ARWP_REWRITE_SCHEMA_VERSION', '2');

spl_autoload_register(
    static function (string $class): void {
        $prefix = 'AgentReadyWP\\';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path     = ARWP_PLUGIN_PATH . 'src/' . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($path)) {
            require_once $path;
        }
    }
);

function arwp_default_settings(): array
{
    return AgentReadyWP\Application\Settings\Defaults::all();
}

function arwp_activate_plugin(): void
{
    if (! get_option('agent_ready_wp_settings')) {
        update_option('agent_ready_wp_settings', arwp_default_settings());
    }

    update_option('arwp_rewrite_schema_version', ARWP_REWRITE_SCHEMA_VERSION);
    flush_rewrite_rules();
}

function arwp_deactivate_plugin(): void
{
    flush_rewrite_rules();
}

function arwp_maybe_flush_rewrite_rules_on_upgrade(): void
{
    $storedVersion = (string) get_option('arwp_rewrite_schema_version', '');

    if ($storedVersion === ARWP_REWRITE_SCHEMA_VERSION) {
        return;
    }

    flush_rewrite_rules();
    update_option('arwp_rewrite_schema_version', ARWP_REWRITE_SCHEMA_VERSION);
}

register_activation_hook(__FILE__, 'arwp_activate_plugin');
register_deactivation_hook(__FILE__, 'arwp_deactivate_plugin');

add_action(
    'plugins_loaded',
    static function (): void {
        load_plugin_textdomain('agent-ready-wp', false, dirname(ARWP_PLUGIN_BASENAME) . '/languages');

        $hooks = new AgentReadyWP\Infrastructure\WordPress\Hooks();
        $hooks->register();
        add_action('init', 'arwp_maybe_flush_rewrite_rules_on_upgrade', 99);
    }
);
