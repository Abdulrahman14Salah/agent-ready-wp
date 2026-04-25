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

final class ApiCatalogFallbackIntegrationTest extends TestCase
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

    public function test_physical_catalog_conflict_preserves_default_behavior(): void
    {
        $catalogDirectory = ABSPATH . '.well-known';
        if (! is_dir($catalogDirectory)) {
            mkdir($catalogDirectory, 0777, true);
        }
        file_put_contents($catalogDirectory . '/api-catalog', 'static');

        [$handler, $collector] = $this->createHandler();
        $result                = $handler->handleCatalogRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('physical_file_conflict', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    public function test_disabled_catalog_feature_falls_back_without_output(): void
    {
        $settings = Defaults::all();
        $settings['api_catalog']['enabled'] = false;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $result                = $handler->handleCatalogRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('feature_disabled', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
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
