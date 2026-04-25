<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\WebMcp;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class WebMcpRuntimeContextFactory
{
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
        $settings      = $this->settingsGateway->getWebMcpSettings();
        $compatibility = $this->compatibilityGateway->getWebMcpCompatibility();

        return [
            'is_public_frontend' => ! is_admin() && ! is_feed(),
            'feature_enabled'    => (bool) ($settings['enabled'] ?? false),
            'selected_tools'     => (array) ($settings['tools'] ?? []),
            'woocommerce_active' => (bool) ($compatibility['woocommerce_active'] ?? false),
            'wp_head_supported'  => (bool) ($compatibility['wp_head_supported'] ?? false),
            'runtime_available'  => (bool) ($compatibility['webmcp_runtime_available'] ?? true),
        ];
    }
}
