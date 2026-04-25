<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestMatcher;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class OAuthDiscoveryRequestMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
        arwp_tests_remove_well_known_file('openid-configuration');
    }

    public function test_matcher_reports_oauth_gateway_shapes(): void
    {
        $repository           = new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector())));
        $settingsGateway      = new RuntimeFeatureSettingsGateway($repository);
        $compatibilityGateway = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));

        $this->assertTrue($settingsGateway->getProtectedApisSettings()['enabled']);
        $this->assertTrue($settingsGateway->getOAuthSettings()['enabled']);
        $this->assertArrayHasKey('openid_configuration_runtime_available', $compatibilityGateway->getOAuthDiscoveryCompatibility());
    }

    public function test_matcher_returns_expected_reason_codes(): void
    {
        $matcher = new OAuthDiscoveryRequestMatcher();

        $this->assertSame('not_request_target', $matcher->evaluate([
            'is_openid_configuration_route' => false,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/jwks.json',
        ])->toArray()['reason']);

        $this->assertSame('protected_apis_disabled', $matcher->evaluate([
            'is_openid_configuration_route' => true,
            'protected_apis_enabled' => false,
            'feature_enabled' => true,
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/jwks.json',
        ])->toArray()['reason']);

        $this->assertSame('settings_incomplete', $matcher->evaluate([
            'is_openid_configuration_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'issuer' => '',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/jwks.json',
        ])->toArray()['reason']);

        $this->assertSame('physical_file_conflict', $matcher->evaluate([
            'is_openid_configuration_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/jwks.json',
            'physical_file_conflict' => true,
        ])->toArray()['reason']);

        $this->assertSame('physical_file_conflict', $matcher->evaluate([
            'is_openid_configuration_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/jwks.json',
            'runtime_available' => false,
        ])->toArray()['reason']);

        $this->assertSame('eligible', $matcher->evaluate([
            'is_openid_configuration_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/jwks.json',
            'physical_file_conflict' => false,
        ])->toArray()['reason']);
    }
}
