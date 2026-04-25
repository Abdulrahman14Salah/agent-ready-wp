<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpPayloadBuilder;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpRuntimeContextFactory;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpRuntimeEmitter;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpToolResolver;
use AgentReadyWP\Application\Settings\Defaults;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class WebMcpFallbackIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];
        arwp_tests_reset_request();
        arwp_tests_set_frontend_context(true, false, false);
    }

    public function test_runtime_asset_contains_browser_capability_guard(): void
    {
        $emitter = $this->createEmitter();
        $result  = $emitter->enqueueRuntime();
        $asset   = (string) file_get_contents(__DIR__ . '/../../../assets/js/webmcp-runtime.js');

        $this->assertTrue($result['decision']['applies']);
        $this->assertStringContainsString('navigator.mcp', $asset);
        $this->assertStringContainsString('registerTool', $asset);
        $this->assertStringContainsString('return;', $asset);
    }

    public function test_empty_tool_selection_prevents_runtime_emission(): void
    {
        $settings = Defaults::all();
        $settings['webmcp']['tools'] = [
            'search'       => false,
            'get_posts'    => false,
            'get_page'     => false,
            'get_products' => false,
        ];
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $emitter = $this->createEmitter();
        $result  = $emitter->enqueueRuntime();

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('no_tools_enabled', $result['decision']['reason']);
        $this->assertSame([], $GLOBALS['arwp_test_enqueued_scripts']);
    }

    private function createEmitter(): WebMcpRuntimeEmitter
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        return new WebMcpRuntimeEmitter(
            new WebMcpRuntimeContextFactory(
                new RuntimeFeatureSettingsGateway($repository),
                new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
            ),
            new WebMcpToolResolver(),
            new WebMcpPayloadBuilder()
        );
    }
}
