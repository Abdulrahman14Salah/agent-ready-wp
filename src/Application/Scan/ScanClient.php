<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Scan;

final class ScanClient
{
    public function run(string $siteUrl): array|\WP_Error
    {
        $response = wp_remote_post(
            'https://isitagentready.com/api/scan',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode(['url' => $siteUrl]),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code < 200 || $code >= 300 || ! is_array($json)) {
            return new \WP_Error(
                'arwp_scan_failed',
                __('The remote scan did not return a valid response.', 'agent-ready-wp')
            );
        }

        return $json;
    }
}
