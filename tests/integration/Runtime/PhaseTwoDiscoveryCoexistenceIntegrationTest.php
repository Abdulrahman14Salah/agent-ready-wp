<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardCapabilityResolver;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardDocumentBuilder;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestContextFactory;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestMatcher;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardResponseWriter;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRuntimeHandler;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryDocumentBuilder;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestContextFactory;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestMatcher;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryResponseWriter;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRuntimeHandler;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceDocumentBuilder;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRequestContextFactory;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRequestMatcher;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceResponseWriter;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRuntimeHandler;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class PhaseTwoDiscoveryCoexistenceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
        arwp_tests_remove_well_known_file('mcp/server-card.json');
        arwp_tests_remove_well_known_file('openid-configuration');
        arwp_tests_remove_well_known_file('oauth-protected-resource');
    }

    public function test_one_endpoint_conflict_does_not_disable_the_others(): void
    {
        arwp_tests_create_well_known_file('openid-configuration');

        $repository           = new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector())));
        $settingsGateway      = new RuntimeFeatureSettingsGateway($repository);
        $compatibilityGateway = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));

        $mcpCollector = (object) ['headers' => [], 'body' => ''];
        $mcpHandler   = new McpServerCardRuntimeHandler(
            new McpServerCardRequestContextFactory(
                $settingsGateway,
                $compatibilityGateway,
                new McpServerCardCapabilityResolver($settingsGateway, $compatibilityGateway)
            ),
            new McpServerCardRequestMatcher(),
            new McpServerCardDocumentBuilder(),
            new McpServerCardResponseWriter(
                static function (string $header) use ($mcpCollector): void {
                    $mcpCollector->headers[] = $header;
                },
                static function (string $body) use ($mcpCollector): void {
                    $mcpCollector->body .= $body;
                },
                static function (): void {
                }
            )
        );
        $mcpHandler->registerRewriteRule();
        $this->assertContains(McpServerCardRequestContextFactory::QUERY_VAR, $mcpHandler->registerQueryVars([]));
        $this->assertTrue(arwp_tests_apply_rewrite_match('/.well-known/mcp/server-card.json'));
        $mcpResult = $mcpHandler->handleServerCardRequest(false);

        arwp_tests_reset_request();
        $oauthCollector = (object) ['headers' => [], 'body' => ''];
        $oauthHandler   = new OAuthDiscoveryRuntimeHandler(
            new OAuthDiscoveryRequestContextFactory($settingsGateway, $compatibilityGateway),
            new OAuthDiscoveryRequestMatcher(),
            new OAuthDiscoveryDocumentBuilder(),
            new OAuthDiscoveryResponseWriter(
                static function (string $header) use ($oauthCollector): void {
                    $oauthCollector->headers[] = $header;
                },
                static function (string $body) use ($oauthCollector): void {
                    $oauthCollector->body .= $body;
                },
                static function (): void {
                }
            )
        );
        $oauthHandler->registerRewriteRule();
        $this->assertContains(OAuthDiscoveryRequestContextFactory::QUERY_VAR, $oauthHandler->registerQueryVars([]));
        $this->assertTrue(arwp_tests_apply_rewrite_match('/.well-known/openid-configuration'));
        $oauthResult = $oauthHandler->handleOAuthDiscoveryRequest(false);

        arwp_tests_reset_request();
        $resourceCollector = (object) ['headers' => [], 'body' => ''];
        $resourceHandler   = new ProtectedResourceRuntimeHandler(
            new ProtectedResourceRequestContextFactory($settingsGateway, $compatibilityGateway),
            new ProtectedResourceRequestMatcher(),
            new ProtectedResourceDocumentBuilder(),
            new ProtectedResourceResponseWriter(
                static function (string $header) use ($resourceCollector): void {
                    $resourceCollector->headers[] = $header;
                },
                static function (string $body) use ($resourceCollector): void {
                    $resourceCollector->body .= $body;
                },
                static function (): void {
                }
            )
        );
        $resourceHandler->registerRewriteRule();
        $this->assertContains(ProtectedResourceRequestContextFactory::QUERY_VAR, $resourceHandler->registerQueryVars([]));
        $this->assertTrue(arwp_tests_apply_rewrite_match('/.well-known/oauth-protected-resource'));
        $resourceResult = $resourceHandler->handleProtectedResourceRequest(false);

        $this->assertTrue($mcpResult['decision']['applies']);
        $this->assertSame('physical_file_conflict', $oauthResult['decision']['reason']);
        $this->assertTrue($resourceResult['decision']['applies']);
    }
}
