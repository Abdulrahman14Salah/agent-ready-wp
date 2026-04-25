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

final class SettingsPageRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
        arwp_tests_reset_request();
        arwp_tests_set_current_user_can(true);
    }

    public function test_notice_renderer_outputs_inline_notice_markup(): void
    {
        $renderer = new CompatibilityNoticeRenderer();
        $html = $renderer->render([
            [
                'severity' => 'warning',
                'message' => 'Warning text',
                'manual_action' => 'Do something.',
            ],
        ]);

        $this->assertStringContainsString('notice-warning', $html);
        $this->assertStringContainsString('Warning text', $html);
    }

    public function test_settings_page_renders_single_save_button_for_phase_one_and_phase_two_sections(): void
    {
        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertSame(1, substr_count($html, '<button type="submit">Save Settings</button>'));
        $this->assertStringContainsString('Phase 2 Foundation', $html);
        $this->assertStringContainsString('Markdown Negotiation', $html);
    }

    public function test_settings_page_save_form_uses_custom_save_flow_only(): void
    {
        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('name="arwp_save_settings" value="1"', $html);
        $this->assertStringContainsString('name="arwp_settings_nonce"', $html);
        $this->assertStringNotContainsString('name="option_page"', $html);
        $this->assertStringNotContainsString('name="action" value="update"', $html);
    }

    public function test_settings_sanitize_callback_tolerates_non_array_input(): void
    {
        $page = $this->createPage();

        $settings = $page->sanitizeSettings('');

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('markdown', $settings);
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
