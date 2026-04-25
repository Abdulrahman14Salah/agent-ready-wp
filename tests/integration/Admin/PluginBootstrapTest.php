<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginBootstrapTest extends TestCase
{
    protected function setUp(): void
    {
        arwp_tests_reset_request();
        $GLOBALS['arwp_test_options'] = [];
        $pluginsLoaded = $GLOBALS['arwp_test_actions']['plugins_loaded'] ?? [];
        $GLOBALS['arwp_test_actions'] = [];
        if ($pluginsLoaded !== []) {
            $GLOBALS['arwp_test_actions']['plugins_loaded'] = $pluginsLoaded;
        }
    }

    public function test_plugin_constants_are_defined(): void
    {
        $this->assertTrue(defined('ARWP_PLUGIN_FILE'));
        $this->assertTrue(defined('ARWP_PLUGIN_PATH'));
        $this->assertTrue(defined('ARWP_VERSION'));
    }

    public function test_rewrite_schema_upgrade_flushes_once_and_persists_version(): void
    {
        $hooks = new AgentReadyWP\Infrastructure\WordPress\Hooks();
        $hooks->register();
        do_action('init');

        $this->assertSame('', get_option('arwp_rewrite_schema_version', ''));

        arwp_maybe_flush_rewrite_rules_on_upgrade();

        $this->assertSame(1, $GLOBALS['arwp_test_flush_rewrite_rules_calls']);
        $this->assertSame(ARWP_REWRITE_SCHEMA_VERSION, get_option('arwp_rewrite_schema_version', ''));

        arwp_maybe_flush_rewrite_rules_on_upgrade();

        $this->assertSame(1, $GLOBALS['arwp_test_flush_rewrite_rules_calls']);
    }

    public function test_plugins_loaded_bootstrap_registers_upgrade_flush_on_init(): void
    {
        $this->assertArrayHasKey('plugins_loaded', $GLOBALS['arwp_test_actions']);

        do_action('plugins_loaded');

        $this->assertArrayHasKey('init', $GLOBALS['arwp_test_actions']);
        $this->assertTrue($this->callbackExists($GLOBALS['arwp_test_actions']['init'], 'arwp_maybe_flush_rewrite_rules_on_upgrade'));
    }

    /**
     * @param array<int,mixed> $callbacks
     */
    private function callbackExists(array $callbacks, string $functionName): bool
    {
        foreach ($callbacks as $items) {
            foreach ($items as $item) {
                $callback = $item['callback'] ?? null;
                if ($callback === $functionName) {
                    return true;
                }
            }
        }

        return false;
    }
}
