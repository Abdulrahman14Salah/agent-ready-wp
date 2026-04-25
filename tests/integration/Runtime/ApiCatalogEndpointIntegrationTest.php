<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogDocumentBuilder;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogEntryFactory;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRequestContextFactory;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRequestMatcher;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogResponseWriter;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRuntimeHandler;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\Defaults;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class ApiCatalogEndpointIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];
        arwp_tests_reset_request();
        arwp_tests_set_query_var(ApiCatalogRequestContextFactory::QUERY_VAR, true);

        $catalogPath = ABSPATH . '.well-known/api-catalog';
        if (file_exists($catalogPath)) {
            unlink($catalogPath);
        }
    }

    public function test_enabled_catalog_request_returns_linkset_response(): void
    {
        [$handler, $collector] = $this->createHandler();
        $result                = $handler->handleCatalogRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertContains('Content-Type: application/linkset+json; charset=utf-8', $collector->headers);
        $this->assertStringContainsString('"title":"WordPress REST API"', $collector->body);
    }

    public function test_woocommerce_active_catalog_includes_woocommerce_entry(): void
    {
        arwp_tests_set_woocommerce_active(true);

        $settings = Defaults::all();
        $settings['api_catalog']['include_woo_rest'] = true;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $handler->handleCatalogRequest(false);

        $this->assertStringContainsString('"title":"WooCommerce REST API"', $collector->body);
    }

    public function test_custom_catalog_entries_are_included_in_response(): void
    {
        $settings = Defaults::all();
        $settings['api_catalog']['custom_entries'] = [
            [
                'name'         => 'Custom API',
                'anchor'       => 'https://example.com/',
                'service_desc' => 'https://example.com/custom.json',
            ],
        ];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $handler->handleCatalogRequest(false);

        $this->assertStringContainsString('"title":"Custom API"', $collector->body);
        $this->assertStringContainsString('"href":"https:\/\/example.com\/custom.json"', $collector->body);
    }

    /**
     * @return array{0: ApiCatalogRuntimeHandler, 1: object}
     */
    private function createHandler(): array
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $collector = (object) [
            'headers' => [],
            'body'    => '',
        ];

        $handler = new ApiCatalogRuntimeHandler(
            new ApiCatalogRequestContextFactory(
                new RuntimeFeatureSettingsGateway($repository),
                new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
            ),
            new ApiCatalogRequestMatcher(),
            new ApiCatalogDocumentBuilder(new ApiCatalogEntryFactory()),
            new ApiCatalogResponseWriter(
                static function (string $header) use ($collector): void {
                    $collector->headers[] = $header;
                },
                static function (string $body) use ($collector): void {
                    $collector->body .= $body;
                },
                static function (): void {
                }
            )
        );

        return [$handler, $collector];
    }
}
