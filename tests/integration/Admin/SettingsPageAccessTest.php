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

final class SettingsPageAccessTest extends TestCase
{
    public function test_register_menu_sets_page_hook(): void
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
        $page = new SettingsPage($repository, new ScanCache(), $factory, new CompatibilityNoticeRenderer());

        $page->registerMenu();

        $this->assertSame('settings_page_agent-ready-wp', $page->getPageHook());
    }
}
