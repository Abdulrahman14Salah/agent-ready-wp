<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class EnvironmentDetectorTest extends TestCase
{
    public function test_detector_returns_expected_shape(): void
    {
        $detector = new EnvironmentDetector(new WooCommerceDetector());

        $state = $detector->detect();

        $this->assertArrayHasKey('woocommerce_active', $state);
        $this->assertArrayHasKey('public_cpts', $state);
        $this->assertArrayHasKey('api_catalog_runtime_available', $state);
        $this->assertArrayHasKey('mcp_server_card_runtime_available', $state);
        $this->assertArrayHasKey('openid_configuration_runtime_available', $state);
        $this->assertArrayHasKey('oauth_protected_resource_runtime_available', $state);
        $this->assertArrayHasKey('webmcp_runtime_available', $state);
        $this->assertArrayHasKey('warnings', $state);
    }

    public function test_detector_reports_phase_two_physical_file_conflicts(): void
    {
        arwp_tests_create_well_known_file('mcp/server-card.json');
        arwp_tests_create_well_known_file('openid-configuration');
        arwp_tests_create_well_known_file('oauth-protected-resource');

        $detector = new EnvironmentDetector(new WooCommerceDetector());
        $state    = $detector->detect();

        $this->assertTrue($state['mcp_server_card_file_conflict']);
        $this->assertTrue($state['openid_configuration_file_conflict']);
        $this->assertTrue($state['oauth_protected_resource_file_conflict']);
        $this->assertFalse($state['mcp_server_card_runtime_available']);
        $this->assertFalse($state['openid_configuration_runtime_available']);
        $this->assertFalse($state['oauth_protected_resource_runtime_available']);

        arwp_tests_remove_well_known_file('mcp/server-card.json');
        arwp_tests_remove_well_known_file('openid-configuration');
        arwp_tests_remove_well_known_file('oauth-protected-resource');
    }
}
