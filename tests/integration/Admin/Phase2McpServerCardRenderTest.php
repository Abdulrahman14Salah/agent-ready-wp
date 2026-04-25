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

final class Phase2McpServerCardRenderTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
        $GLOBALS['arwp_test_update_option_calls'] = 0;
        arwp_tests_reset_request();
        arwp_tests_set_current_user_can(true);
    }

    public function test_page_renders_live_mcp_server_card_section_and_draft_preview(): void
    {
        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('MCP Server Card', $html);
        $this->assertStringContainsString('Phase 2 Draft Preview', $html);
        $this->assertStringContainsString('name="agent_ready_wp_settings[mcp_server_card][name]"', $html);
    }

    public function test_page_renders_missing_guidance_when_mcp_enabled_with_missing_fields(): void
    {
        $_POST = [
            'arwp_save_settings' => '1',
            'arwp_settings_nonce' => 'nonce',
            'agent_ready_wp_settings' => [
                'mcp_server_card' => [
                    'enabled' => '1',
                    'name' => '',
                    'version' => '',
                    'transport' => '',
                ],
            ],
        ];

        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('Missing:', $html);
        $this->assertStringContainsString('Server name is required.', $html);
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
