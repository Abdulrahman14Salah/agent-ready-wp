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

final class Phase2SharedSaveFlowTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
        $GLOBALS['arwp_test_update_option_calls'] = 0;
        arwp_tests_reset_request();
        arwp_tests_set_current_user_can(true);
    }

    public function test_shared_page_save_persists_phase_one_and_phase_two_values_together(): void
    {
        $_POST = [
            'arwp_save_settings' => '1',
            'arwp_settings_nonce' => 'nonce',
            'agent_ready_wp_settings' => array_merge(
                arwp_tests_phase2_valid_payload(),
                [
                    'markdown' => [
                        'enabled' => '1',
                        'post_types' => ['post'],
                    ],
                ]
            ),
        ];

        $page = $this->createPage();

        ob_start();
        $page->render();
        ob_end_clean();

        $settings = get_option('agent_ready_wp_settings', []);

        $this->assertTrue($settings['markdown']['enabled']);
        $this->assertTrue($settings['protected_apis']['enabled']);
        $this->assertSame('Agent Ready WP', $settings['mcp_server_card']['name']);
        $this->assertSame('https://example.com/oauth/token', $settings['oauth']['token_endpoint']);
    }

    public function test_invalid_phase_two_save_does_not_persist_partial_values(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );
        $repository->update(arwp_tests_phase2_valid_payload());

        $baseline = get_option('agent_ready_wp_settings', []);
        $beforeCalls = (int) $GLOBALS['arwp_test_update_option_calls'];

        $_POST = [
            'arwp_save_settings' => '1',
            'arwp_settings_nonce' => 'nonce',
            'agent_ready_wp_settings' => arwp_tests_phase2_invalid_payload(),
        ];

        $page = $this->createPage();

        ob_start();
        $page->render();
        $html = (string) ob_get_clean();

        $after = get_option('agent_ready_wp_settings', []);

        $this->assertSame($beforeCalls, (int) $GLOBALS['arwp_test_update_option_calls']);
        $this->assertSame($baseline['mcp_server_card']['name'], $after['mcp_server_card']['name']);
        $this->assertStringContainsString('Phase 2 settings could not be saved', $html);
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
