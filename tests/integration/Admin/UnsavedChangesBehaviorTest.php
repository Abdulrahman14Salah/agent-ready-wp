<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Scan\ScanCache;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class UnsavedChangesBehaviorTest extends TestCase
{
    public function test_scan_cache_changes_do_not_modify_settings_option(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $original = $repository->get();

        $cache = new ScanCache();
        $cache->set((new ScanSummaryMapper())->map([
            'url' => 'https://example.com',
            'score' => 12,
            'level' => 1,
            'level_name' => 'Basic',
            'checks' => [],
            'scanned_at' => '2026-04-23T17:41:26Z',
        ]));

        $this->assertSame($original, $repository->get());
    }
}
