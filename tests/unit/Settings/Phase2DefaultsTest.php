<?php

declare(strict_types=1);

use AgentReadyWP\Application\Settings\Defaults;
use PHPUnit\Framework\TestCase;

final class Phase2DefaultsTest extends TestCase
{
    public function test_defaults_include_phase_two_keys_and_nested_shape(): void
    {
        $defaults = Defaults::all();

        $this->assertArrayHasKey('mcp_server_card', $defaults);
        $this->assertArrayHasKey('oauth', $defaults);
        $this->assertArrayHasKey('protected_apis', $defaults);
        $this->assertArrayHasKey('protected_resource', $defaults);

        $this->assertArrayHasKey('enabled', $defaults['protected_apis']);
        $this->assertArrayHasKey('authorization_servers', $defaults['protected_resource']);
        $this->assertSame([], $defaults['protected_resource']['authorization_servers']);
    }
}
