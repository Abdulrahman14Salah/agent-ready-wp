<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ApiCatalog;

final class ApiCatalogEntryFactory
{
    /**
     * @return array<string,string>
     */
    public function createWordPressEntry(string $siteUrl, string $restRoot): array
    {
        return [
            'anchor' => $siteUrl,
            'href'   => $restRoot,
            'rel'    => 'service-desc',
            'title'  => __('WordPress REST API', 'agent-ready-wp'),
            'source' => 'wordpress',
        ];
    }

    /**
     * @return array<string,string>
     */
    public function createWooCommerceEntry(string $siteUrl, string $restRoot): array
    {
        return [
            'anchor' => $siteUrl,
            'href'   => rtrim($restRoot, '/') . '/wc/v3',
            'rel'    => 'service-desc',
            'title'  => __('WooCommerce REST API', 'agent-ready-wp'),
            'source' => 'woocommerce',
        ];
    }

    /**
     * @param array<string,mixed> $entry
     * @return array<string,string>|null
     */
    public function createCustomEntry(array $entry): ?array
    {
        $name        = (string) ($entry['name'] ?? '');
        $anchor      = (string) ($entry['anchor'] ?? '');
        $serviceDesc = (string) ($entry['service_desc'] ?? '');

        if ($name === '' || $anchor === '' || $serviceDesc === '') {
            return null;
        }

        return [
            'anchor' => $anchor,
            'href'   => $serviceDesc,
            'rel'    => 'service-desc',
            'title'  => $name,
            'source' => 'custom',
        ];
    }
}
