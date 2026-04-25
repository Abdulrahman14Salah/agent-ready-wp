<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\WebMcp;

final class WebMcpPayloadBuilder
{
    /**
     * @param array<int,array<string,mixed>> $tools
     * @return array<string,mixed>
     */
    public function build(array $tools): array
    {
        return [
            'tools'            => $tools,
            'siteUrl'          => function_exists('home_url') ? home_url('/') : '',
            'restRoot'         => function_exists('rest_url') ? rest_url() : '',
            'capabilityCheck'  => 'navigator.mcp.registerTool',
        ];
    }
}
