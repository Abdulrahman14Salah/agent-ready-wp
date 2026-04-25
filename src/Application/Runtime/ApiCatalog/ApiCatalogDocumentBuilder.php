<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ApiCatalog;

final class ApiCatalogDocumentBuilder
{
    public function __construct(private readonly ApiCatalogEntryFactory $entryFactory)
    {
    }

    /**
     * @param array<string,mixed> $context
     * @return array{links: array<int,array<string,string>>}
     */
    public function build(array $context): array
    {
        $links = [];

        $siteUrl  = (string) ($context['site_url'] ?? '');
        $restRoot = (string) ($context['rest_root'] ?? '');

        if (! empty($context['include_wp_rest'])) {
            $links[] = $this->entryFactory->createWordPressEntry($siteUrl, $restRoot);
        }

        if (! empty($context['include_woo_rest']) && ! empty($context['woocommerce_active'])) {
            $links[] = $this->entryFactory->createWooCommerceEntry($siteUrl, $restRoot);
        }

        foreach ((array) ($context['custom_entries'] ?? []) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $normalized = $this->entryFactory->createCustomEntry($entry);
            if ($normalized !== null) {
                $links[] = $normalized;
            }
        }

        return [
            'links' => $links,
        ];
    }
}
