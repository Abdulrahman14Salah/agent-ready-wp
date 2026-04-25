<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardCapabilityResolver;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class McpServerCardCapabilityResolverTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => arwp_tests_phase2_runtime_settings(),
        ];
        arwp_tests_reset_request();
    }

    public function test_resolver_reports_webmcp_false_when_no_runtime_tool_can_be_exposed(): void
    {
        $settings = arwp_tests_phase2_runtime_settings();
        $settings['webmcp']['tools'] = [
            'search'       => false,
            'get_posts'    => false,
            'get_page'     => false,
            'get_products' => true,
        ];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;
        arwp_tests_set_woocommerce_active(false);

        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $resolver = new McpServerCardCapabilityResolver(
            new RuntimeFeatureSettingsGateway($repository),
            new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $capabilities = $resolver->resolve();

        $this->assertFalse($capabilities['webmcp']);
    }

    public function test_resolver_reports_webmcp_true_when_public_runtime_is_eligible(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $resolver = new McpServerCardCapabilityResolver(
            new RuntimeFeatureSettingsGateway($repository),
            new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $capabilities = $resolver->resolve();

        $this->assertTrue($capabilities['webmcp']);
    }

    public function test_resolver_suppresses_other_capabilities_for_conflicts_and_incomplete_settings(): void
    {
        $settings = arwp_tests_phase2_runtime_settings();
        $settings['oauth']['jwks_uri'] = '';
        $settings['protected_resource']['authorization_servers'] = [];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;
        arwp_tests_create_well_known_file('api-catalog');

        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $resolver = new McpServerCardCapabilityResolver(
            new RuntimeFeatureSettingsGateway($repository),
            new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $capabilities = $resolver->resolve();

        $this->assertFalse($capabilities['api_catalog']);
        $this->assertFalse($capabilities['oauth_discovery']);
        $this->assertFalse($capabilities['protected_resource']);

        arwp_tests_remove_well_known_file('api-catalog');
    }

    public function test_resolver_suppresses_oauth_and_protected_resource_when_protected_apis_are_disabled(): void
    {
        $settings = arwp_tests_phase2_runtime_settings();
        $settings['protected_apis']['enabled'] = false;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $resolver = new McpServerCardCapabilityResolver(
            new RuntimeFeatureSettingsGateway($repository),
            new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $capabilities = $resolver->resolve();

        $this->assertFalse($capabilities['oauth_discovery']);
        $this->assertFalse($capabilities['protected_resource']);
    }
}
