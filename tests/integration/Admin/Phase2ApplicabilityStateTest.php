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

final class Phase2ApplicabilityStateTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
        arwp_tests_reset_request();
        arwp_tests_set_current_user_can(true);
    }

    public function test_oauth_and_resource_sections_are_disabled_with_explanation_when_not_applicable(): void
    {
        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('Enable protected APIs to edit OAuth and protected-resource metadata.', $html);
    }

    public function test_oauth_and_resource_sections_render_active_when_protected_apis_enabled(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $repository->update(arwp_tests_phase2_valid_payload());

        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('id="arwp-panel-oauth-discovery"', $html);
        $this->assertStringContainsString('aria-disabled="false"', $html);
    }

    private function createPage(): SettingsPage
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

        return new SettingsPage(
            $repository,
            new ScanCache(),
            $factory,
            new CompatibilityNoticeRenderer()
        );
    }
}
