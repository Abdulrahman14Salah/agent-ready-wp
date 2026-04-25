<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ProtectedResource;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class ProtectedResourceRequestContextFactory
{
    public const QUERY_VAR = 'arwp_protected_resource';

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
        $settings              = $this->settingsGateway->getProtectedResourceSettings();
        $compatibility         = $this->compatibilityGateway->getProtectedResourceCompatibility();

        return [
            'is_protected_resource_route' => (bool) get_query_var(self::QUERY_VAR, false),
            'protected_apis_enabled' => (bool) ($protectedApisSettings['enabled'] ?? false),
            'feature_enabled' => (bool) ($settings['enabled'] ?? false),
            'resource' => (string) ($settings['resource'] ?? ''),
            'authorization_servers' => (array) ($settings['authorization_servers'] ?? []),
            'physical_file_conflict' => (bool) ($compatibility['oauth_protected_resource_file_conflict'] ?? false),
            'runtime_available' => (bool) ($compatibility['oauth_protected_resource_runtime_available'] ?? true),
        ];
    }
}
