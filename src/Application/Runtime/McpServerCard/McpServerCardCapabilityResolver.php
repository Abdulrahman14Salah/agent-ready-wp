<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\McpServerCard;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpToolResolver;

final class McpServerCardCapabilityResolver
{
    private readonly WebMcpToolResolver $webMcpToolResolver;

    public function __construct(
        private readonly RuntimeFeatureSettingsGateway $settingsGateway,
        private readonly RuntimeCompatibilityGateway $compatibilityGateway,
        ?WebMcpToolResolver $webMcpToolResolver = null
    ) {
        $this->webMcpToolResolver = $webMcpToolResolver ?? new WebMcpToolResolver();
    }

    /**
     * @return array{
     *   api_catalog: bool,
     *   webmcp: bool,
     *   oauth_discovery: bool,
     *   protected_resource: bool
     * }
     */
    public function resolve(): array
    {
        $apiCatalogSettings            = $this->settingsGateway->getApiCatalogSettings();
        $apiCatalogCompatibility       = $this->compatibilityGateway->getApiCatalogCompatibility();
        $webMcpSettings                = $this->settingsGateway->getWebMcpSettings();
        $webMcpCompatibility           = $this->compatibilityGateway->getWebMcpCompatibility();
        $protectedApisSettings         = $this->settingsGateway->getProtectedApisSettings();
        $oauthSettings                 = $this->settingsGateway->getOAuthSettings();
        $oauthCompatibility            = $this->compatibilityGateway->getOAuthDiscoveryCompatibility();
        $protectedResourceSettings     = $this->settingsGateway->getProtectedResourceSettings();
        $protectedResourceCompatibility = $this->compatibilityGateway->getProtectedResourceCompatibility();

        return [
            'api_catalog' => ! empty($apiCatalogSettings['enabled'])
                && ! empty($apiCatalogCompatibility['api_catalog_runtime_available']),
            'webmcp' => $this->webMcpToolResolver->resolve([
                'feature_enabled'    => (bool) ($webMcpSettings['enabled'] ?? false),
                'is_public_frontend' => true,
                'wp_head_supported'  => (bool) ($webMcpCompatibility['wp_head_supported'] ?? false),
                'runtime_available'  => (bool) ($webMcpCompatibility['webmcp_runtime_available'] ?? true),
                'woocommerce_active' => (bool) ($webMcpCompatibility['woocommerce_active'] ?? false),
                'selected_tools'     => (array) ($webMcpSettings['tools'] ?? []),
            ])->toArray()['applies'],
            'oauth_discovery' => ! empty($protectedApisSettings['enabled'])
                && ! empty($oauthSettings['enabled'])
                && $this->hasRequiredOAuthSettings($oauthSettings)
                && ! empty($oauthCompatibility['openid_configuration_runtime_available']),
            'protected_resource' => ! empty($protectedApisSettings['enabled'])
                && ! empty($protectedResourceSettings['enabled'])
                && $this->hasRequiredProtectedResourceSettings($protectedResourceSettings)
                && ! empty($protectedResourceCompatibility['oauth_protected_resource_runtime_available']),
        ];
    }

    /**
     * @param array<string,mixed> $settings
     */
    private function hasRequiredOAuthSettings(array $settings): bool
    {
        return (string) ($settings['issuer'] ?? '') !== ''
            && (string) ($settings['authorization_endpoint'] ?? '') !== ''
            && (string) ($settings['token_endpoint'] ?? '') !== ''
            && (string) ($settings['jwks_uri'] ?? '') !== '';
    }

    /**
     * @param array<string,mixed> $settings
     */
    private function hasRequiredProtectedResourceSettings(array $settings): bool
    {
        return (string) ($settings['resource'] ?? '') !== ''
            && (array) ($settings['authorization_servers'] ?? []) !== [];
    }
}
