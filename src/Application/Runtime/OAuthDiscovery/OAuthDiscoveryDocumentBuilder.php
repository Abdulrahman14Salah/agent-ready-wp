<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\OAuthDiscovery;

final class OAuthDiscoveryDocumentBuilder
{
    /**
     * @param array<string,mixed> $context
     * @return array{issuer: string, authorization_endpoint: string, token_endpoint: string, jwks_uri: string}
     */
    public function build(array $context): array
    {
        return [
            'issuer' => (string) ($context['issuer'] ?? ''),
            'authorization_endpoint' => (string) ($context['authorization_endpoint'] ?? ''),
            'token_endpoint' => (string) ($context['token_endpoint'] ?? ''),
            'jwks_uri' => (string) ($context['jwks_uri'] ?? ''),
        ];
    }
}
