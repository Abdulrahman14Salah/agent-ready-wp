<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class SettingsRepositoryTest extends TestCase
{
    public function test_repository_returns_default_shape_when_option_missing(): void
    {
        $GLOBALS['arwp_test_options'] = [];

        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $settings = $repository->get();

        $this->assertArrayHasKey('api_catalog', $settings);
        $this->assertArrayHasKey('webmcp', $settings);
        $this->assertArrayHasKey('protected_apis', $settings);
        $this->assertArrayHasKey('protected_resource', $settings);
    }

    public function test_repository_compatibility_keeps_phase_one_and_phase_two_keys_in_single_option(): void
    {
        $GLOBALS['arwp_test_options'] = [];

        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $repository->update(array_merge(
            arwp_tests_phase2_valid_payload(),
            [
                'markdown' => [
                    'enabled' => '1',
                    'post_types' => ['post'],
                ],
            ]
        ));

        $saved = get_option('agent_ready_wp_settings', []);

        $this->assertTrue($saved['markdown']['enabled']);
        $this->assertTrue($saved['protected_apis']['enabled']);
        $this->assertSame('https://example.com/resource', $saved['protected_resource']['resource']);
    }
}
