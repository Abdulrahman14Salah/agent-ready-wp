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

final class CompatibilityStateRenderTest extends TestCase
{
    public function test_view_model_marks_woocommerce_controls_unavailable_when_plugin_missing(): void
    {
        $factory = new SettingsPageViewModelFactory(
            new SettingsRepository(new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))),
            new EnvironmentDetector(new WooCommerceDetector()),
            new AdminPagePlaceholders(),
            new ScanSummaryMapper()
        );

        $viewModel = $factory->create(null);
        $panels = array_values(array_filter($viewModel['capability_panels'], static fn (array $panel): bool => $panel['panel_key'] === 'webmcp'));

        $controls = $panels[0]['controls'];
        $productControl = array_values(array_filter($controls, static fn (array $control): bool => $control['control_key'] === 'webmcp_get_products'));

        $this->assertTrue($productControl[0]['disabled']);
    }
}
