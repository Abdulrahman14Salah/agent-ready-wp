<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryDocumentBuilder;
use PHPUnit\Framework\TestCase;

final class OAuthDiscoveryDocumentBuilderTest extends TestCase
{
    public function test_builder_shapes_oauth_discovery_document(): void
    {
        $builder  = new OAuthDiscoveryDocumentBuilder();
        $document = $builder->build([
            'issuer' => 'https://example.com',
            'authorization_endpoint' => 'https://auth.example.com/authorize',
            'token_endpoint' => 'https://auth.example.com/token',
            'jwks_uri' => 'https://auth.example.com/.well-known/jwks.json',
        ]);

        $this->assertSame(arwp_tests_fixture_json('oauth-discovery-response.json'), $document);
    }
}
