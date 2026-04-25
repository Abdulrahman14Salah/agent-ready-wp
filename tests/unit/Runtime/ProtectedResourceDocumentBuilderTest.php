<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceDocumentBuilder;
use PHPUnit\Framework\TestCase;

final class ProtectedResourceDocumentBuilderTest extends TestCase
{
    public function test_builder_shapes_protected_resource_document(): void
    {
        $builder  = new ProtectedResourceDocumentBuilder();
        $document = $builder->build([
            'resource' => 'https://example.com/wp-json/agent-ready/v1',
            'authorization_servers' => [
                'https://auth.example.com',
            ],
        ]);

        $this->assertSame(arwp_tests_fixture_json('protected-resource-response.json'), $document);
    }
}
