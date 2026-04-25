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

final class CapabilityPreviewTest extends TestCase
{
    public function test_view_model_exposes_content_signal_preview(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $factory = new SettingsPageViewModelFactory(
            $repository,
            new EnvironmentDetector(new WooCommerceDetector()),
            new AdminPagePlaceholders(),
            new ScanSummaryMapper()
        );

        $viewModel = $factory->create(null);

        $contentSignals = array_values(array_filter(
            $viewModel['capability_panels'],
            static fn (array $panel): bool => $panel['panel_key'] === 'content_signals'
        ));

        $this->assertStringContainsString('Content-Signal:', $contentSignals[0]['preview']['display_value']);
    }
}
