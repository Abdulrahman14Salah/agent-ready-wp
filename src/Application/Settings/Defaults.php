<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Settings;

final class Defaults
{
    public static function all(): array
    {
        return [
            'enabled' => true,
            'markdown' => [
                'enabled'    => true,
                'post_types' => ['post', 'page'],
                'include_woo' => true,
            ],
            'content_signals' => [
                'enabled'  => true,
                'ai_train' => 'no',
                'search'   => 'yes',
                'ai_input' => 'no',
            ],
            'api_catalog' => [
                'enabled'          => true,
                'include_wp_rest'  => true,
                'include_woo_rest' => true,
                'custom_entries'   => [],
            ],
            'webmcp' => [
                'enabled' => true,
                'tools'   => [
                    'search'       => true,
                    'get_posts'    => true,
                    'get_page'     => true,
                    'get_products' => true,
                ],
            ],
            'mcp_server_card' => [
                'enabled'   => false,
                'name'      => '',
                'version'   => '1.0.0',
                'transport' => '',
            ],
            'oauth' => [
                'enabled'                => false,
                'issuer'                 => '',
                'authorization_endpoint' => '',
                'token_endpoint'         => '',
                'jwks_uri'               => '',
            ],
            'protected_apis' => [
                'enabled' => false,
            ],
            'protected_resource' => [
                'enabled'               => false,
                'resource'              => '',
                'authorization_servers' => [],
            ],
        ];
    }
}
