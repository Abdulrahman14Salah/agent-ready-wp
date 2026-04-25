<?php

declare(strict_types=1);

use AgentReadyWP\Admin\Notices\CompatibilityNoticeRenderer;
use AgentReadyWP\Admin\Page\SettingsPage;
use AgentReadyWP\Admin\ViewModel\SettingsPageViewModelFactory;
use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Scan\ScanCache;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use AgentReadyWP\Public\AdminPagePlaceholders;
use PHPUnit\Framework\TestCase;

final class ReadinessSummaryRenderTest extends TestCase
{
    public function test_render_outputs_summary_markup(): void
    {
        $cache = new ScanCache();
        $cache->set((new ScanSummaryMapper())->map([
            'url' => 'https://example.com',
            'score' => 20,
            'level' => 1,
            'level_name' => 'Basic',
            'checks' => [],
            'scanned_at' => '2026-04-23T17:41:26Z',
        ]));

        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $factory = new SettingsPageViewModelFactory(
            $repository,
            new EnvironmentDetector(new WooCommerceDetector()),
            new AdminPagePlaceholders(),
            new ScanSummaryMapper()
        );
        $page = new SettingsPage($repository, $cache, $factory, new CompatibilityNoticeRenderer());

        ob_start();
        $page->render();
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('Last Scan Result', $output);
        $this->assertStringContainsString('Run Scan', $output);
    }
}
