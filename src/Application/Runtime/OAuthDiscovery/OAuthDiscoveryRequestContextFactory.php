<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\OAuthDiscovery;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class OAuthDiscoveryRequestContextFactory
{
    public const QUERY_VAR = 'arwp_oauth_discovery';

    public function __construct(
        private readonly RuntimeFeatureSettingsGateway $settingsGateway,
        private readonly RuntimeCompatibilityGateway $compatibilityGateway
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(): array
    {
        $protectedApisSettings = $this->settingsGateway->getProtectedApisSettings();
        $settings              = $this->settingsGateway->getOAuthSettings();
        $compatibility         = $this->compatibilityGateway->getOAuthDiscoveryCompatibility();

        return [
            'is_openid_configuration_route' => (bool) get_query_var(self::QUERY_VAR, false),
            'protected_apis_enabled' => (bool) ($protectedApisSettings['enabled'] ?? false),
            'feature_enabled' => (bool) ($settings['enabled'] ?? false),
            'issuer' => (string) ($settings['issuer'] ?? ''),
            'authorization_endpoint' => (string) ($settings['authorization_endpoint'] ?? ''),
            'token_endpoint' => (string) ($settings['token_endpoint'] ?? ''),
            'jwks_uri' => (string) ($settings['jwks_uri'] ?? ''),
            'physical_file_conflict' => (bool) ($compatibility['openid_configuration_file_conflict'] ?? false),
            'runtime_available' => (bool) ($compatibility['openid_configuration_runtime_available'] ?? true),
        ];
    }
}
