<?php

declare(strict_types=1);

use AgentReadyWP\Infrastructure\WordPress\Hooks;
use PHPUnit\Framework\TestCase;

final class RuntimeHookRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_actions'] = [];
        $GLOBALS['arwp_test_filters'] = [];
    }

    public function test_hooks_register_runtime_action_and_filter_callbacks(): void
    {
        $hooks = new Hooks();
        $hooks->register();

        $this->assertArrayHasKey('init', $GLOBALS['arwp_test_actions']);
        $this->assertArrayHasKey('template_redirect', $GLOBALS['arwp_test_actions']);
        $this->assertArrayHasKey('wp_enqueue_scripts', $GLOBALS['arwp_test_actions']);
        $this->assertArrayHasKey('query_vars', $GLOBALS['arwp_test_filters']);
        $this->assertArrayHasKey('robots_txt', $GLOBALS['arwp_test_filters']);

        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['init'], 'registerRewriteRule'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['template_redirect'], 'handleCurrentRequest'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['template_redirect'], 'handleCatalogRequest'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['template_redirect'], 'handleServerCardRequest'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['template_redirect'], 'handleOAuthDiscoveryRequest'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['template_redirect'], 'handleProtectedResourceRequest'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['wp_enqueue_scripts'], 'enqueueRuntime'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_filters']['query_vars'], 'registerQueryVars'));
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_filters']['robots_txt'], 'filterRobots'));
    }

    public function test_hooks_register_phase_two_rewrite_rules_and_query_vars(): void
    {
        $hooks = new Hooks();
        $hooks->register();

        do_action('init');
        $queryVars = apply_filters('query_vars', []);

        $regexes = array_column($GLOBALS['arwp_test_rewrite_rules'], 'regex');

        $this->assertContains('^\.well-known/mcp/server-card\.json/?$', $regexes);
        $this->assertContains('^\.well-known/openid-configuration/?$', $regexes);
        $this->assertContains('^\.well-known/oauth-protected-resource/?$', $regexes);
        $this->assertContains('arwp_mcp_server_card', $queryVars);
        $this->assertContains('arwp_oauth_discovery', $queryVars);
        $this->assertContains('arwp_protected_resource', $queryVars);
    }

    /**
     * @param array<int,mixed> $callbacks
     */
    private function callbackExists(array $callbacks, string $method): bool
    {
        foreach ($callbacks as $items) {
            foreach ($items as $item) {
                $callback = $item['callback'] ?? null;
                if (is_array($callback) && isset($callback[1]) && $callback[1] === $method) {
                    return true;
                }
            }
        }

        return false;
    }
}
