<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRequestMatcher;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class ProtectedResourceRequestMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
        arwp_tests_remove_well_known_file('oauth-protected-resource');
    }

    public function test_matcher_reports_protected_resource_gateway_shapes(): void
    {
        $repository           = new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector())));
        $settingsGateway      = new RuntimeFeatureSettingsGateway($repository);
        $compatibilityGateway = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));

        $this->assertTrue($settingsGateway->getProtectedResourceSettings()['enabled']);
        $this->assertArrayHasKey('oauth_protected_resource_runtime_available', $compatibilityGateway->getProtectedResourceCompatibility());
    }

    public function test_matcher_returns_expected_reason_codes(): void
    {
        $matcher = new ProtectedResourceRequestMatcher();

        $this->assertSame('not_request_target', $matcher->evaluate([
            'is_protected_resource_route' => false,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'resource' => 'https://example.com/resource',
            'authorization_servers' => ['https://auth.example.com'],
        ])->toArray()['reason']);

        $this->assertSame('protected_apis_disabled', $matcher->evaluate([
            'is_protected_resource_route' => true,
            'protected_apis_enabled' => false,
            'feature_enabled' => true,
            'resource' => 'https://example.com/resource',
            'authorization_servers' => ['https://auth.example.com'],
        ])->toArray()['reason']);

        $this->assertSame('settings_incomplete', $matcher->evaluate([
            'is_protected_resource_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'resource' => '',
            'authorization_servers' => ['https://auth.example.com'],
        ])->toArray()['reason']);

        $this->assertSame('physical_file_conflict', $matcher->evaluate([
            'is_protected_resource_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'resource' => 'https://example.com/resource',
            'authorization_servers' => ['https://auth.example.com'],
            'physical_file_conflict' => true,
        ])->toArray()['reason']);

        $this->assertSame('physical_file_conflict', $matcher->evaluate([
            'is_protected_resource_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'resource' => 'https://example.com/resource',
            'authorization_servers' => ['https://auth.example.com'],
            'runtime_available' => false,
        ])->toArray()['reason']);

        $this->assertSame('eligible', $matcher->evaluate([
            'is_protected_resource_route' => true,
            'protected_apis_enabled' => true,
            'feature_enabled' => true,
            'resource' => 'https://example.com/resource',
            'authorization_servers' => ['https://auth.example.com'],
            'physical_file_conflict' => false,
        ])->toArray()['reason']);
    }
}
