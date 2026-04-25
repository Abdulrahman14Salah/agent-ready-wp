<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestMatcher;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class McpServerCardRequestMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
        arwp_tests_remove_well_known_file('mcp/server-card.json');
    }

    public function test_matcher_reports_phase_two_gateway_shapes(): void
    {
        $repository           = new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector())));
        $settingsGateway      = new RuntimeFeatureSettingsGateway($repository);
        $compatibilityGateway = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));

        $this->assertTrue($settingsGateway->getMcpServerCardSettings()['enabled']);
        $this->assertArrayHasKey('mcp_server_card_runtime_available', $compatibilityGateway->getMcpServerCardCompatibility());
    }

    public function test_matcher_returns_expected_reason_codes(): void
    {
        $matcher = new McpServerCardRequestMatcher();

        $this->assertSame('not_request_target', $matcher->evaluate([
            'is_server_card_route' => false,
            'feature_enabled' => true,
            'name' => 'Agent Ready WP',
            'version' => '1.0.0',
            'transport' => 'https://example.com',
        ])->toArray()['reason']);

        $this->assertSame('feature_disabled', $matcher->evaluate([
            'is_server_card_route' => true,
            'feature_enabled' => false,
            'name' => 'Agent Ready WP',
            'version' => '1.0.0',
            'transport' => 'https://example.com',
        ])->toArray()['reason']);

        $this->assertSame('settings_incomplete', $matcher->evaluate([
            'is_server_card_route' => true,
            'feature_enabled' => true,
            'name' => '',
            'version' => '1.0.0',
            'transport' => 'https://example.com',
        ])->toArray()['reason']);

        $this->assertSame('physical_file_conflict', $matcher->evaluate([
            'is_server_card_route' => true,
            'feature_enabled' => true,
            'name' => 'Agent Ready WP',
            'version' => '1.0.0',
            'transport' => 'https://example.com',
            'physical_file_conflict' => true,
        ])->toArray()['reason']);

        $this->assertSame('physical_file_conflict', $matcher->evaluate([
            'is_server_card_route' => true,
            'feature_enabled' => true,
            'name' => 'Agent Ready WP',
            'version' => '1.0.0',
            'transport' => 'https://example.com',
            'runtime_available' => false,
        ])->toArray()['reason']);

        $this->assertSame('eligible', $matcher->evaluate([
            'is_server_card_route' => true,
            'feature_enabled' => true,
            'name' => 'Agent Ready WP',
            'version' => '1.0.0',
            'transport' => 'https://example.com',
            'physical_file_conflict' => false,
        ])->toArray()['reason']);
    }
}
