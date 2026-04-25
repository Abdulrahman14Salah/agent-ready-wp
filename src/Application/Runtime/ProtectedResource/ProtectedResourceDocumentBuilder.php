<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ProtectedResource;

final class ProtectedResourceDocumentBuilder
{
    /**
     * @param array<string,mixed> $context
     * @return array{resource: string, authorization_servers: array<int,string>}
     */
    public function build(array $context): array
    {
        return [
            'resource' => (string) ($context['resource'] ?? ''),
            'authorization_servers' => array_values(array_unique(array_map('strval', (array) ($context['authorization_servers'] ?? [])))),
        ];
    }
}
