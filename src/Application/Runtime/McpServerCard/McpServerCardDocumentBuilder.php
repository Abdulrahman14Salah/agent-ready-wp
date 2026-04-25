<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\McpServerCard;

final class McpServerCardDocumentBuilder
{
    /**
     * @param array<string,mixed> $context
     * @return array{name: string, version: string, transport: array{url: string}, capabilities: array<string,bool>}
     */
    public function build(array $context): array
    {
        $capabilities = (array) ($context['capabilities'] ?? []);

        return [
            'name' => (string) ($context['name'] ?? ''),
            'version' => (string) ($context['version'] ?? ''),
            'transport' => [
                'url' => (string) ($context['transport'] ?? ''),
            ],
            'capabilities' => [
                'api_catalog' => (bool) ($capabilities['api_catalog'] ?? false),
                'webmcp' => (bool) ($capabilities['webmcp'] ?? false),
                'oauth_discovery' => (bool) ($capabilities['oauth_discovery'] ?? false),
                'protected_resource' => (bool) ($capabilities['protected_resource'] ?? false),
            ],
        ];
    }
}
