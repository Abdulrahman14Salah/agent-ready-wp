<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogDocumentBuilder;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogEntryFactory;
use PHPUnit\Framework\TestCase;

final class ApiCatalogDocumentBuilderTest extends TestCase
{
    public function test_builder_includes_wordpress_and_custom_entries(): void
    {
        $builder = new ApiCatalogDocumentBuilder(new ApiCatalogEntryFactory());

        $document = $builder->build([
            'include_wp_rest'    => true,
            'include_woo_rest'   => false,
            'woocommerce_active' => false,
            'site_url'           => 'https://example.com/',
            'rest_root'          => 'https://example.com/wp-json/',
            'custom_entries'     => [
                [
                    'name'         => 'Custom API',
                    'anchor'       => 'https://example.com/',
                    'service_desc' => 'https://example.com/custom.json',
                ],
            ],
        ]);

        $this->assertCount(2, $document['links']);
        $this->assertSame('WordPress REST API', $document['links'][0]['title']);
        $this->assertSame('Custom API', $document['links'][1]['title']);
        $this->assertSame('https://example.com/custom.json', $document['links'][1]['href']);
    }

    public function test_builder_omits_incomplete_custom_entries_and_woo_when_inactive(): void
    {
        $builder = new ApiCatalogDocumentBuilder(new ApiCatalogEntryFactory());

        $document = $builder->build([
            'include_wp_rest'    => false,
            'include_woo_rest'   => true,
            'woocommerce_active' => false,
            'site_url'           => 'https://example.com/',
            'rest_root'          => 'https://example.com/wp-json/',
            'custom_entries'     => [
                [
                    'name'         => '',
                    'anchor'       => 'https://example.com/',
                    'service_desc' => 'https://example.com/custom.json',
                ],
            ],
        ]);

        $this->assertSame([], $document['links']);
    }
}
