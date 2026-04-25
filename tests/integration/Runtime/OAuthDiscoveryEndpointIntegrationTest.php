<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryDocumentBuilder;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestContextFactory;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestMatcher;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryResponseWriter;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRuntimeHandler;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class OAuthDiscoveryEndpointIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
        arwp_tests_remove_well_known_file('openid-configuration');
    }

    public function test_successful_oauth_discovery_publication_returns_expected_json(): void
    {
        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        $this->assertContains(OAuthDiscoveryRequestContextFactory::QUERY_VAR, $handler->registerQueryVars([]));
        $this->assertTrue(arwp_tests_apply_rewrite_match('/.well-known/openid-configuration'));
        $result                = $handler->handleOAuthDiscoveryRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertContains('Content-Type: application/json; charset=utf-8', $collector->headers);
        $this->assertSame(arwp_tests_fixture_json('oauth-discovery-response.json'), json_decode($collector->body, true));
    }

    public function test_disabled_or_incomplete_oauth_discovery_falls_back_without_output(): void
    {
        $settings = arwp_tests_phase2_runtime_settings();
        $settings['protected_apis']['enabled'] = false;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        arwp_tests_apply_rewrite_match('/.well-known/openid-configuration');
        $result                = $handler->handleOAuthDiscoveryRequest(false);

        $this->assertSame('protected_apis_disabled', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);

        $settings = arwp_tests_phase2_runtime_settings();
        $settings['oauth']['jwks_uri'] = '';
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        arwp_tests_apply_rewrite_match('/.well-known/openid-configuration');
        $result                = $handler->handleOAuthDiscoveryRequest(false);

        $this->assertSame('settings_incomplete', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    public function test_physical_file_conflict_preserves_default_behavior(): void
    {
        arwp_tests_create_well_known_file('openid-configuration');

        [$handler, $collector] = $this->createHandler();
        $handler->registerRewriteRule();
        arwp_tests_apply_rewrite_match('/.well-known/openid-configuration');
        $result                = $handler->handleOAuthDiscoveryRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('physical_file_conflict', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    /**
     * @return array{0: OAuthDiscoveryRuntimeHandler, 1: object}
     */
    private function createHandler(): array
    {
        $repository = new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector())));
        $collector  = (object) ['headers' => [], 'body' => ''];

        $handler = new OAuthDiscoveryRuntimeHandler(
            new OAuthDiscoveryRequestContextFactory(
                new RuntimeFeatureSettingsGateway($repository),
                new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
            ),
            new OAuthDiscoveryRequestMatcher(),
            new OAuthDiscoveryDocumentBuilder(),
            new OAuthDiscoveryResponseWriter(
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
