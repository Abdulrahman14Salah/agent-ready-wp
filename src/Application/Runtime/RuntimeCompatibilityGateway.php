<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime;

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;

final class RuntimeCompatibilityGateway
{
    public function __construct(private readonly EnvironmentDetector $environmentDetector)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function get(): array
    {
        return $this->environmentDetector->detect();
    }

    /**
     * @return array{
     *   woocommerce_active: bool,
     *   api_catalog_file_conflict: bool,
     *   api_catalog_runtime_available: bool
     * }
     */
    public function getApiCatalogCompatibility(): array
    {
        $compatibility = $this->get();

        return [
            'woocommerce_active'        => (bool) ($compatibility['woocommerce_active'] ?? false),
            'api_catalog_file_conflict' => (bool) ($compatibility['api_catalog_file_conflict'] ?? false),
            'api_catalog_runtime_available' => (bool) ($compatibility['api_catalog_runtime_available'] ?? true),
        ];
    }

    /**
     * @return array{
     *   woocommerce_active: bool,
     *   wp_head_supported: bool,
     *   webmcp_runtime_available: bool
     * }
     */
    public function getWebMcpCompatibility(): array
    {
        $compatibility = $this->get();

        return [
            'woocommerce_active' => (bool) ($compatibility['woocommerce_active'] ?? false),
            'wp_head_supported'  => (bool) ($compatibility['wp_head_supported'] ?? false),
            'webmcp_runtime_available' => (bool) ($compatibility['webmcp_runtime_available'] ?? true),
        ];
    }

    /**
     * @return array{
     *   mcp_server_card_file_conflict: bool,
     *   mcp_server_card_runtime_available: bool
     * }
     */
    public function getMcpServerCardCompatibility(): array
    {
        $compatibility = $this->get();

        return [
            'mcp_server_card_file_conflict'     => (bool) ($compatibility['mcp_server_card_file_conflict'] ?? false),
            'mcp_server_card_runtime_available' => (bool) ($compatibility['mcp_server_card_runtime_available'] ?? true),
        ];
    }

    /**
     * @return array{
     *   openid_configuration_file_conflict: bool,
     *   openid_configuration_runtime_available: bool
     * }
     */
    public function getOAuthDiscoveryCompatibility(): array
    {
        $compatibility = $this->get();

        return [
            'openid_configuration_file_conflict'     => (bool) ($compatibility['openid_configuration_file_conflict'] ?? false),
            'openid_configuration_runtime_available' => (bool) ($compatibility['openid_configuration_runtime_available'] ?? true),
        ];
    }

    /**
     * @return array{
     *   oauth_protected_resource_file_conflict: bool,
     *   oauth_protected_resource_runtime_available: bool
     * }
     */
    public function getProtectedResourceCompatibility(): array
    {
        $compatibility = $this->get();

        return [
            'oauth_protected_resource_file_conflict'     => (bool) ($compatibility['oauth_protected_resource_file_conflict'] ?? false),
            'oauth_protected_resource_runtime_available' => (bool) ($compatibility['oauth_protected_resource_runtime_available'] ?? true),
        ];
    }
}
