<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalDirectiveBuilder;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalLineNormalizer;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalsRobotsFilter;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\Defaults;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class ContentSignalsFallbackIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];

        if (! is_dir(ABSPATH)) {
            mkdir(ABSPATH, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $robots = ABSPATH . 'robots.txt';
        if (file_exists($robots)) {
            unlink($robots);
        }
    }

    public function test_physical_robots_conflict_preserves_default_output(): void
    {
        file_put_contents(ABSPATH . 'robots.txt', "User-agent: *\nDisallow: /\n");

        $filter = $this->createFilter();
        $input  = "User-agent: *\nDisallow: /wp-admin/\n";
        $result = $filter->filterRobots($input, true);

        $this->assertSame($input, $result);
    }

    private function createFilter(): ContentSignalsRobotsFilter
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        return new ContentSignalsRobotsFilter(
            new RuntimeFeatureSettingsGateway($repository),
            new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector())),
            new ContentSignalDirectiveBuilder(),
            new ContentSignalLineNormalizer()
        );
    }
}
