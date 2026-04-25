<?php

declare(strict_types=1);

use AgentReadyWP\Application\Settings\Defaults;
use PHPUnit\Framework\TestCase;

final class DefaultsTest extends TestCase
{
    public function test_defaults_include_expected_phase_one_keys(): void
    {
        $defaults = Defaults::all();

        $this->assertArrayHasKey('markdown', $defaults);
        $this->assertArrayHasKey('content_signals', $defaults);
        $this->assertArrayHasKey('api_catalog', $defaults);
        $this->assertArrayHasKey('webmcp', $defaults);
    }
}
