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

final class Phase2ValidationFeedbackTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
        $GLOBALS['arwp_test_update_option_calls'] = 0;
        arwp_tests_reset_request();
        arwp_tests_set_current_user_can(true);
    }

    public function test_invalid_phase_two_submission_shows_targeted_feedback(): void
    {
        $_POST = [
            'arwp_save_settings' => '1',
            'arwp_settings_nonce' => 'nonce',
            'agent_ready_wp_settings' => arwp_tests_phase2_invalid_payload(),
        ];

        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('Server name is required.', $html);
        $this->assertStringContainsString('Issuer must be a valid URL.', $html);
        $this->assertStringContainsString('At least one authorization server URL is required.', $html);
    }

    public function test_invalid_submission_preserves_entered_values_in_form_and_draft_preview_context(): void
    {
        $_POST = [
            'arwp_save_settings' => '1',
            'arwp_settings_nonce' => 'nonce',
            'agent_ready_wp_settings' => arwp_tests_phase2_invalid_payload(),
        ];

        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('value="bad-url"', $html);
        $this->assertStringContainsString('Draft preview only. Published metadata changes after a successful save.', $html);
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
