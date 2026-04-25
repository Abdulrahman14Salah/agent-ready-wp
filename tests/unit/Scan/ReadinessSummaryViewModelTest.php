<?php

declare(strict_types=1);

use AgentReadyWP\Admin\ViewModel\SettingsPageViewModelFactory;
use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use AgentReadyWP\Public\AdminPagePlaceholders;
use PHPUnit\Framework\TestCase;

final class ReadinessSummaryViewModelTest extends TestCase
{
    public function test_view_model_uses_empty_summary_when_no_cache_exists(): void
    {
        $factory = new SettingsPageViewModelFactory(
            new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))),
            new EnvironmentDetector(new WooCommerceDetector()),
            new AdminPagePlaceholders(),
            new ScanSummaryMapper()
        );

        $viewModel = $factory->create(null);

        $this->assertSame('empty', $viewModel['readiness_summary']['status']);
    }
}
