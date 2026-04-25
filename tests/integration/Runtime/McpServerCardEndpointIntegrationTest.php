<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardCapabilityResolver;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardDocumentBuilder;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestContextFactory;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestMatcher;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardResponseWriter;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRuntimeHandler;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class McpServerCardEndpointIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
        arwp_tests_remove_well_known_file('mcp/server-card.json');
    }

    public function test_successful_server_card_publication_returns_expected_json(): void
    {
        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        $this->assertContains(McpServerCardRequestContextFactory::QUERY_VAR, $handler->registerQueryVars([]));
        $this->assertTrue(arwp_tests_apply_rewrite_match('/.well-known/mcp/server-card.json'));
        $result                = $handler->handleServerCardRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertContains('Content-Type: application/json; charset=utf-8', $collector->headers);
        $this->assertSame(arwp_tests_fixture_json('mcp-server-card-response.json'), json_decode($collector->body, true));
    }

    public function test_disabled_or_incomplete_server_card_falls_back_without_output(): void
    {
        $settings = arwp_tests_phase2_runtime_settings();
        $settings['mcp_server_card']['enabled'] = false;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        arwp_tests_apply_rewrite_match('/.well-known/mcp/server-card.json');
        $result                = $handler->handleServerCardRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('feature_disabled', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);

        $settings['mcp_server_card']['enabled'] = true;
        $settings['mcp_server_card']['transport'] = '';
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        arwp_tests_apply_rewrite_match('/.well-known/mcp/server-card.json');
        $result                = $handler->handleServerCardRequest(false);

        $this->assertSame('settings_incomplete', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    public function test_physical_file_conflict_preserves_default_behavior(): void
    {
        arwp_tests_create_well_known_file('mcp/server-card.json');

        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        arwp_tests_apply_rewrite_match('/.well-known/mcp/server-card.json');
        $result                = $handler->handleServerCardRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('physical_file_conflict', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    /**
     * @return array{0: McpServerCardRuntimeHandler, 1: object}
     */
    private function createHandler(): array
    {
        $repository           = new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector())));
        $settingsGateway      = new RuntimeFeatureSettingsGateway($repository);
        $compatibilityGateway = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));
        $collector            = (object) ['headers' => [], 'body' => ''];

        $handler = new McpServerCardRuntimeHandler(
            new McpServerCardRequestContextFactory(
                $settingsGateway,
                $compatibilityGateway,
                new McpServerCardCapabilityResolver($settingsGateway, $compatibilityGateway)
            ),
            new McpServerCardRequestMatcher(),
            new McpServerCardDocumentBuilder(),
            new McpServerCardResponseWriter(
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
