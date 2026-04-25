<?php
/**
 * Uninstall routine for Agent Ready WP.
 */

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('agent_ready_wp_settings');
delete_transient('agent_ready_wp_scan_cache');
