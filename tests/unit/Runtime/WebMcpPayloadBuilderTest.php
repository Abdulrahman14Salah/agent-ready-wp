<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\WebMcp\WebMcpPayloadBuilder;
use PHPUnit\Framework\TestCase;

final class WebMcpPayloadBuilderTest extends TestCase
{
    public function test_builder_returns_runtime_payload_shape(): void
    {
        $builder = new WebMcpPayloadBuilder();
        $payload = $builder->build([
            [
                'name'                 => 'search',
                'label'                => 'search',
                'route'                => 'https://example.com/wp-json/wp/v2/search',
                'enabled'              => true,
                'requires_woocommerce' => false,
            ],
        ]);

        $this->assertArrayHasKey('tools', $payload);
        $this->assertArrayHasKey('siteUrl', $payload);
        $this->assertArrayHasKey('restRoot', $payload);
        $this->assertArrayHasKey('capabilityCheck', $payload);
        $this->assertSame('navigator.mcp.registerTool', $payload['capabilityCheck']);
    }
}
