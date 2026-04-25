<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Compatibility;

use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;

final class EnvironmentDetector
{
    public function __construct(private readonly WooCommerceDetector $wooCommerceDetector)
    {
    }

    public function detect(): array
    {
        $publicCpts = [];
        $objects    = get_post_types(['public' => true, '_builtin' => false], 'objects');
        $exclude    = ['product_variation', 'shop_order', 'shop_coupon'];

        foreach ($objects as $object) {
            if (! isset($object->name)) {
                continue;
            }

            if (in_array($object->name, $exclude, true)) {
                continue;
            }

            $publicCpts[] = $object->name;
        }

        $physicalRobots          = file_exists(ABSPATH . 'robots.txt');
        $apiCatalogFile          = file_exists(ABSPATH . '.well-known/api-catalog');
        $mcpServerCardFile       = file_exists(ABSPATH . '.well-known/mcp/server-card.json');
        $openidConfigurationFile = file_exists(ABSPATH . '.well-known/openid-configuration');
        $protectedResourceFile   = file_exists(ABSPATH . '.well-known/oauth-protected-resource');

        return [
            'woocommerce_active'                    => $this->wooCommerceDetector->isActive(),
            'public_cpts'                           => array_values($publicCpts),
            'physical_robots_txt_present'           => $physicalRobots,
            'api_catalog_file_conflict'             => $apiCatalogFile,
            'api_catalog_runtime_available'         => ! $apiCatalogFile,
            'mcp_server_card_file_conflict'         => $mcpServerCardFile,
            'mcp_server_card_runtime_available'     => ! $mcpServerCardFile,
            'openid_configuration_file_conflict'    => $openidConfigurationFile,
            'openid_configuration_runtime_available' => ! $openidConfigurationFile,
            'oauth_protected_resource_file_conflict' => $protectedResourceFile,
            'oauth_protected_resource_runtime_available' => ! $protectedResourceFile,
            'webmcp_runtime_available'              => true,
            'wp_head_supported'                     => true,
            'warnings'                              => $this->buildWarnings(
                $physicalRobots,
                $apiCatalogFile,
                $mcpServerCardFile,
                $openidConfigurationFile,
                $protectedResourceFile
            ),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function buildWarnings(
        bool $physicalRobots,
        bool $apiCatalogFile,
        bool $mcpServerCardFile,
        bool $openidConfigurationFile,
        bool $protectedResourceFile
    ): array {
        $warnings = [];

        if ($physicalRobots) {
            $warnings[] = [
                'warning_key'   => 'physical_robots_txt',
                'severity'      => 'warning',
                'message'       => __('A physical robots.txt file exists. Content Signals cannot be applied automatically.', 'agent-ready-wp'),
                'affected_panel' => 'content_signals',
                'manual_action' => __('Update the physical robots.txt file manually.', 'agent-ready-wp'),
            ];
        }

        if ($apiCatalogFile) {
            $warnings[] = [
                'warning_key'   => 'api_catalog_file_conflict',
                'severity'      => 'warning',
                'message'       => __('A physical /.well-known/api-catalog file exists and may block the generated endpoint.', 'agent-ready-wp'),
                'affected_panel' => 'api_catalog',
                'manual_action' => __('Remove or update the physical file to allow the generated endpoint.', 'agent-ready-wp'),
            ];
        }

        if ($mcpServerCardFile) {
            $warnings[] = [
                'warning_key'   => 'mcp_server_card_file_conflict',
                'severity'      => 'warning',
                'message'       => __('A physical /.well-known/mcp/server-card.json file exists and may block the generated endpoint.', 'agent-ready-wp'),
                'affected_panel' => 'mcp_server_card',
                'manual_action' => __('Remove or update the physical file to allow the generated endpoint.', 'agent-ready-wp'),
            ];
        }

        if ($openidConfigurationFile) {
            $warnings[] = [
                'warning_key'   => 'openid_configuration_file_conflict',
                'severity'      => 'warning',
                'message'       => __('A physical /.well-known/openid-configuration file exists and may block the generated endpoint.', 'agent-ready-wp'),
                'affected_panel' => 'oauth',
                'manual_action' => __('Remove or update the physical file to allow the generated endpoint.', 'agent-ready-wp'),
            ];
        }

        if ($protectedResourceFile) {
            $warnings[] = [
                'warning_key'   => 'oauth_protected_resource_file_conflict',
                'severity'      => 'warning',
                'message'       => __('A physical /.well-known/oauth-protected-resource file exists and may block the generated endpoint.', 'agent-ready-wp'),
                'affected_panel' => 'protected_resource',
                'manual_action' => __('Remove or update the physical file to allow the generated endpoint.', 'agent-ready-wp'),
            ];
        }

        if (! $this->wooCommerceDetector->isActive()) {
            $warnings[] = [
                'warning_key'   => 'woocommerce_inactive',
                'severity'      => 'info',
                'message'       => __('WooCommerce-specific controls are unavailable because WooCommerce is not active.', 'agent-ready-wp'),
                'affected_panel' => null,
                'manual_action' => null,
            ];
        }

        return $warnings;
    }
}
