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

final class Phase2ApplicabilityViewModelTest extends TestCase
{
    public function test_view_model_marks_oauth_and_resource_sections_disabled_when_protected_apis_off(): void
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

        $this->assertFalse($viewModel['phase_two_sections']['oauth_discovery']['active']);
        $this->assertFalse($viewModel['phase_two_sections']['protected_resource']['active']);
        $this->assertNotEmpty($viewModel['phase_two_sections']['oauth_discovery']['disabled_reason']);
    }

    public function test_view_model_marks_oauth_and_resource_sections_active_when_protected_apis_on(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $repository->update(arwp_tests_phase2_valid_payload());

        $factory = new SettingsPageViewModelFactory(
            $repository,
            new EnvironmentDetector(new WooCommerceDetector()),
            new AdminPagePlaceholders(),
            new ScanSummaryMapper()
        );

        $viewModel = $factory->create(null);

        $this->assertTrue($viewModel['phase_two_sections']['oauth_discovery']['active']);
        $this->assertTrue($viewModel['phase_two_sections']['protected_resource']['active']);
        $this->assertNull($viewModel['phase_two_sections']['oauth_discovery']['disabled_reason']);
    }
}
