<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class SettingsRepositoryRoundTripTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
    }

    public function test_repository_updates_and_returns_saved_settings(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $repository->update([
            'markdown' => [
                'enabled' => '1',
                'post_types' => ['post'],
            ],
        ]);

        $settings = $repository->get();

        $this->assertTrue($settings['markdown']['enabled']);
        $this->assertSame(['post'], $settings['markdown']['post_types']);
    }

    public function test_repository_round_trips_phase_two_settings_without_dropping_values(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $repository->update(arwp_tests_phase2_valid_payload());
        $settings = $repository->get();

        $this->assertTrue($settings['protected_apis']['enabled']);
        $this->assertSame('Agent Ready WP', $settings['mcp_server_card']['name']);
        $this->assertSame('https://example.com/.well-known/jwks.json', $settings['oauth']['jwks_uri']);
        $this->assertSame(
            ['https://auth.example.com', 'https://backup.example.com'],
            $settings['protected_resource']['authorization_servers']
        );
    }
}
