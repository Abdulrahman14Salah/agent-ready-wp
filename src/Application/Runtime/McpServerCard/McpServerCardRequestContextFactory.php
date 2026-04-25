<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\McpServerCard;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class McpServerCardRequestContextFactory
{
    public const QUERY_VAR = 'arwp_mcp_server_card';

    public function __construct(
        private readonly RuntimeFeatureSettingsGateway $settingsGateway,
        private readonly RuntimeCompatibilityGateway $compatibilityGateway,
        private readonly McpServerCardCapabilityResolver $capabilityResolver
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(): array
    {
        $settings      = $this->settingsGateway->getMcpServerCardSettings();
        $compatibility = $this->compatibilityGateway->getMcpServerCardCompatibility();

        return [
            'is_server_card_route'   => (bool) get_query_var(self::QUERY_VAR, false),
            'feature_enabled'        => (bool) ($settings['enabled'] ?? false),
            'name'                   => (string) ($settings['name'] ?? ''),
            'version'                => (string) ($settings['version'] ?? ''),
            'transport'              => (string) ($settings['transport'] ?? ''),
            'physical_file_conflict' => (bool) ($compatibility['mcp_server_card_file_conflict'] ?? false),
            'runtime_available'      => (bool) ($compatibility['mcp_server_card_runtime_available'] ?? true),
            'capabilities'           => $this->capabilityResolver->resolve(),
        ];
    }
}
