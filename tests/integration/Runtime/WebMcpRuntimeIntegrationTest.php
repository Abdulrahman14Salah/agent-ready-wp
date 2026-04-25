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

final class WebMcpRuntimeIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];
        arwp_tests_reset_request();
        arwp_tests_set_frontend_context(true, false, false);
    }

    public function test_public_frontend_requests_enqueue_runtime_asset_and_payload(): void
    {
        $emitter = $this->createEmitter();
        $result  = $emitter->enqueueRuntime();

        $this->assertTrue($result['decision']['applies']);
        $this->assertContains(WebMcpRuntimeEmitter::SCRIPT_HANDLE, $GLOBALS['arwp_test_enqueued_scripts']);
        $payload = arwp_tests_get_localized_script(WebMcpRuntimeEmitter::SCRIPT_HANDLE, 'arwpWebMcpRuntime');
        $this->assertSame(['search', 'get_posts', 'get_page'], array_column($payload['tools'], 'name'));
    }

    public function test_woocommerce_tool_is_included_only_when_woocommerce_is_active(): void
    {
        $settings = Defaults::all();
        $settings['webmcp']['tools']['get_products'] = true;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $emitter = $this->createEmitter();
        $result  = $emitter->enqueueRuntime();
        $payload = arwp_tests_get_localized_script(WebMcpRuntimeEmitter::SCRIPT_HANDLE, 'arwpWebMcpRuntime');

        $this->assertSame(['search', 'get_posts', 'get_page'], array_column($payload['tools'], 'name'));
        $this->assertTrue($result['decision']['applies']);

        arwp_tests_reset_request();
        arwp_tests_set_frontend_context(true, false, false);
        arwp_tests_set_woocommerce_active(true);
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $emitter = $this->createEmitter();
        $emitter->enqueueRuntime();
        $payload = arwp_tests_get_localized_script(WebMcpRuntimeEmitter::SCRIPT_HANDLE, 'arwpWebMcpRuntime');

        $this->assertContains('get_products', array_column($payload['tools'], 'name'));
    }

    public function test_disabled_webmcp_feature_omits_runtime_output(): void
    {
        $settings = Defaults::all();
        $settings['webmcp']['enabled'] = false;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $emitter = $this->createEmitter();
        $result  = $emitter->enqueueRuntime();

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('feature_disabled', $result['decision']['reason']);
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
