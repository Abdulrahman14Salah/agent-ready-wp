<?php

declare(strict_types=1);

namespace AgentReadyWP\Infrastructure\WordPress;

final class Http
{
    public function postJson(string $url, array $payload, int $timeout = 30): array|\WP_Error
    {
        return wp_remote_post(
            $url,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode($payload),
                'timeout' => $timeout,
            ]
        );
    }
}
