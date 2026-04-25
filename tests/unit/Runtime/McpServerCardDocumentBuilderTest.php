<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardDocumentBuilder;
use PHPUnit\Framework\TestCase;

final class McpServerCardDocumentBuilderTest extends TestCase
{
    public function test_builder_shapes_server_card_document_with_capabilities(): void
    {
        $builder  = new McpServerCardDocumentBuilder();
        $document = $builder->build([
            'name' => 'Agent Ready WP',
            'version' => '1.0.0',
            'transport' => 'https://example.com/wp-json/agent-ready/v1/mcp',
            'capabilities' => [
                'api_catalog' => true,
                'webmcp' => true,
                'oauth_discovery' => true,
                'protected_resource' => true,
            ],
        ]);

        $this->assertSame(arwp_tests_fixture_json('mcp-server-card-response.json'), $document);
    }
}
