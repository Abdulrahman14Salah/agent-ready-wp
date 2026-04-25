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

final class Phase2PreviewSummaryTest extends TestCase
{
    public function test_phase_two_preview_uses_draft_shape_and_required_section_keys(): void
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
        $preview   = $viewModel['phase_two_preview'];

        $this->assertTrue($preview['is_draft']);

        $keys = array_map(
            static fn (array $item): string => (string) $item['section_key'],
            $preview['items']
        );

        $this->assertSame(
            ['mcp_server_card', 'oauth_discovery', 'protected_resource'],
            $keys
        );
    }

    public function test_phase_two_preview_matches_fixture_shape_for_expected_labels(): void
    {
        $fixture = arwp_tests_phase2_preview_fixture();

        $this->assertTrue($fixture['is_draft']);
        $this->assertSame('MCP Server Card', $fixture['items'][0]['label']);
        $this->assertSame('OAuth Discovery', $fixture['items'][1]['label']);
    }
}
