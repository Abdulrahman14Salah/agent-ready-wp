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

final class ContentSignalsRobotsIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];

        if (file_exists(ABSPATH . 'robots.txt')) {
            unlink(ABSPATH . 'robots.txt');
        }
    }

    public function test_enabled_configured_signals_emit_single_canonical_line(): void
    {
        $settings = Defaults::all();
        $settings['content_signals'] = [
            'enabled' => true,
            'ai_train' => 'no',
            'search' => 'yes',
            'ai_input' => '',
        ];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $input = (string) file_get_contents(__DIR__ . '/../../fixtures/content-signals-robots.txt');
        $input .= "Content-Signal: stale=yes\n";

        $filter = $this->createFilter();
        $result = $filter->filterRobots($input, true);

        $this->assertSame(1, substr_count($result, 'Content-Signal:'));
        $this->assertStringContainsString('Content-Signal: ai-train=no, search=yes', $result);
    }

    public function test_disabled_or_unset_content_signals_preserve_default_output(): void
    {
        $settings = Defaults::all();
        $settings['content_signals'] = [
            'enabled' => false,
            'ai_train' => 'no',
            'search' => 'yes',
            'ai_input' => 'no',
        ];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $filter = $this->createFilter();
        $input  = (string) file_get_contents(__DIR__ . '/../../fixtures/content-signals-robots.txt');
        $result = $filter->filterRobots($input, true);

        $this->assertSame($input, $result);

        $settings['content_signals'] = [
            'enabled' => true,
            'ai_train' => '',
            'search' => '',
            'ai_input' => '',
        ];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $resultUnset = $filter->filterRobots($input, true);
        $this->assertSame($input, $resultUnset);
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
