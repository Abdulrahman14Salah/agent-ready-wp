<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class SettingsSaveFlowTest extends TestCase
{
    public function test_page_level_save_updates_multiple_sections(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $settings = $repository->update([
            'markdown' => [
                'enabled' => '1',
                'post_types' => ['post'],
            ],
            'webmcp' => [
                'enabled' => '1',
                'tools' => [
                    'search' => '1',
                    'get_posts' => '1',
                ],
            ],
        ]);

        $this->assertTrue($settings['markdown']['enabled']);
        $this->assertTrue($settings['webmcp']['tools']['search']);
    }
}
