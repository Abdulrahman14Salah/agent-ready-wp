<?php

declare(strict_types=1);

namespace AgentReadyWP\Admin\Assets;

use AgentReadyWP\Admin\Page\SettingsPage;

final class SettingsPageAssets
{
    public function __construct(private readonly SettingsPage $settingsPage)
    {
    }

    public function enqueue(string $hook): void
    {
        if ($hook !== $this->settingsPage->getPageHook()) {
            return;
        }

        wp_register_script('arwp-admin-settings', ARWP_PLUGIN_URL . 'assets/js/admin-settings.js', [], ARWP_VERSION, true);
        wp_register_style('arwp-admin-settings', ARWP_PLUGIN_URL . 'assets/css/admin-settings.css', [], ARWP_VERSION);

        wp_localize_script(
            'arwp-admin-settings',
            'arwpSettingsPage',
            [
                'ajaxUrl'  => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('arwp_run_scan'),
                'messages' => [
                    'running' => __('Running scan...', 'agent-ready-wp'),
                    'failed'  => __('Scan failed.', 'agent-ready-wp'),
                    'notChecked' => __('Not checked', 'agent-ready-wp'),
                ],
                'phase2' => [
                    'mcpLabel' => __('MCP Server Card', 'agent-ready-wp'),
                    'oauthLabel' => __('OAuth Discovery', 'agent-ready-wp'),
                    'protectedResourceLabel' => __('Protected Resource', 'agent-ready-wp'),
                    'disabledLabel' => __('Disabled', 'agent-ready-wp'),
                    'notApplicableLabel' => __('Not applicable while protected APIs are disabled.', 'agent-ready-wp'),
                    'missingLabel' => __('(missing)', 'agent-ready-wp'),
                    'serverLabel' => __('Server', 'agent-ready-wp'),
                    'versionLabel' => __('Version', 'agent-ready-wp'),
                    'transportLabel' => __('Transport', 'agent-ready-wp'),
                    'issuerLabel' => __('Issuer', 'agent-ready-wp'),
                    'authorizationEndpointLabel' => __('Authorization endpoint', 'agent-ready-wp'),
                    'tokenEndpointLabel' => __('Token endpoint', 'agent-ready-wp'),
                    'jwksLabel' => __('JWKS URI', 'agent-ready-wp'),
                    'resourceLabel' => __('Resource', 'agent-ready-wp'),
                    'authorizationServerLabel' => __('Authorization server', 'agent-ready-wp'),
                ],
                'applicability' => [
                    'enabled' => __('Protected APIs enabled.', 'agent-ready-wp'),
                    'disabled' => __('Protected APIs disabled. OAuth and protected-resource sections are read-only.', 'agent-ready-wp'),
                ],
            ]
        );

        wp_enqueue_script('arwp-admin-settings');
        wp_enqueue_style('arwp-admin-settings');
    }
}
