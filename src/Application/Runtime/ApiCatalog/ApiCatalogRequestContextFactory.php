<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ApiCatalog;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class ApiCatalogRequestContextFactory
{
    public const QUERY_VAR = 'arwp_api_catalog';

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
        $settings      = $this->settingsGateway->getApiCatalogSettings();
        $compatibility = $this->compatibilityGateway->getApiCatalogCompatibility();

        return [
            'is_catalog_route'       => (bool) get_query_var(self::QUERY_VAR, false),
            'feature_enabled'        => (bool) ($settings['enabled'] ?? false),
            'include_wp_rest'        => (bool) ($settings['include_wp_rest'] ?? false),
            'include_woo_rest'       => (bool) ($settings['include_woo_rest'] ?? false),
            'custom_entries'         => (array) ($settings['custom_entries'] ?? []),
            'site_url'               => function_exists('home_url') ? home_url('/') : '',
            'rest_root'              => function_exists('rest_url') ? rest_url() : '',
            'woocommerce_active'     => (bool) ($compatibility['woocommerce_active'] ?? false),
            'physical_file_conflict' => (bool) ($compatibility['api_catalog_file_conflict'] ?? false),
            'runtime_available'      => (bool) ($compatibility['api_catalog_runtime_available'] ?? true),
        ];
    }
}
