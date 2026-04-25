<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime;

use AgentReadyWP\Application\Settings\SettingsRepository;

final class RuntimeFeatureSettingsGateway
{
    public function __construct(private readonly SettingsRepository $settingsRepository)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function get(): array
    {
        return $this->settingsRepository->get();
    }

    /**
     * @return array{enabled: bool, post_types: array<int,string>, include_woo: bool}
     */
    public function getMarkdownSettings(): array
    {
        $settings = $this->get();
        $markdown = (array) ($settings['markdown'] ?? []);

        $postTypes = is_array($markdown['post_types'] ?? null) ? $markdown['post_types'] : [];

        return [
            'enabled' => (bool) ($markdown['enabled'] ?? false),
            'post_types' => array_values(array_unique(array_map('strval', $postTypes))),
            'include_woo' => (bool) ($markdown['include_woo'] ?? false),
        ];
    }

    /**
     * @return array{enabled: bool, ai_train: string, search: string, ai_input: string}
     */
    public function getContentSignalsSettings(): array
    {
        $settings = $this->get();
        $signals  = (array) ($settings['content_signals'] ?? []);

        return [
            'enabled' => (bool) ($signals['enabled'] ?? false),
            'ai_train' => (string) ($signals['ai_train'] ?? ''),
            'search' => (string) ($signals['search'] ?? ''),
            'ai_input' => (string) ($signals['ai_input'] ?? ''),
        ];
    }

    /**
     * @return array{
     *   enabled: bool,
     *   include_wp_rest: bool,
     *   include_woo_rest: bool,
     *   custom_entries: array<int,array<string,string>>
     * }
     */
    public function getApiCatalogSettings(): array
    {
        $settings   = $this->get();
        $apiCatalog = (array) ($settings['api_catalog'] ?? []);
        $entries    = is_array($apiCatalog['custom_entries'] ?? null) ? $apiCatalog['custom_entries'] : [];

        return [
            'enabled'          => (bool) ($apiCatalog['enabled'] ?? false),
            'include_wp_rest'  => (bool) ($apiCatalog['include_wp_rest'] ?? false),
            'include_woo_rest' => (bool) ($apiCatalog['include_woo_rest'] ?? false),
            'custom_entries'   => array_values(array_filter($entries, 'is_array')),
        ];
    }

    /**
     * @return array{
     *   enabled: bool,
     *   tools: array{
     *     search: bool,
     *     get_posts: bool,
     *     get_page: bool,
     *     get_products: bool
     *   }
     * }
     */
    public function getWebMcpSettings(): array
    {
        $settings = $this->get();
        $webMcp   = (array) ($settings['webmcp'] ?? []);
        $tools    = (array) ($webMcp['tools'] ?? []);

        return [
            'enabled' => (bool) ($webMcp['enabled'] ?? false),
            'tools'   => [
                'search'       => (bool) ($tools['search'] ?? false),
                'get_posts'    => (bool) ($tools['get_posts'] ?? false),
                'get_page'     => (bool) ($tools['get_page'] ?? false),
                'get_products' => (bool) ($tools['get_products'] ?? false),
            ],
        ];
    }

    /**
     * @return array{
     *   enabled: bool,
     *   name: string,
     *   version: string,
     *   transport: string
     * }
     */
    public function getMcpServerCardSettings(): array
    {
        $settings      = $this->get();
        $mcpServerCard = (array) ($settings['mcp_server_card'] ?? []);

        return [
            'enabled'   => (bool) ($mcpServerCard['enabled'] ?? false),
            'name'      => (string) ($mcpServerCard['name'] ?? ''),
            'version'   => (string) ($mcpServerCard['version'] ?? ''),
            'transport' => (string) ($mcpServerCard['transport'] ?? ''),
        ];
    }

    /**
     * @return array{enabled: bool}
     */
    public function getProtectedApisSettings(): array
    {
        $settings      = $this->get();
        $protectedApis = (array) ($settings['protected_apis'] ?? []);

        return [
            'enabled' => (bool) ($protectedApis['enabled'] ?? false),
        ];
    }

    /**
     * @return array{
     *   enabled: bool,
     *   issuer: string,
     *   authorization_endpoint: string,
     *   token_endpoint: string,
     *   jwks_uri: string
     * }
     */
    public function getOAuthSettings(): array
    {
        $settings = $this->get();
        $oauth    = (array) ($settings['oauth'] ?? []);

        return [
            'enabled'                => (bool) ($oauth['enabled'] ?? false),
            'issuer'                 => (string) ($oauth['issuer'] ?? ''),
            'authorization_endpoint' => (string) ($oauth['authorization_endpoint'] ?? ''),
            'token_endpoint'         => (string) ($oauth['token_endpoint'] ?? ''),
            'jwks_uri'               => (string) ($oauth['jwks_uri'] ?? ''),
        ];
    }

    /**
     * @return array{
     *   enabled: bool,
     *   resource: string,
     *   authorization_servers: array<int,string>
     * }
     */
    public function getProtectedResourceSettings(): array
    {
        $settings          = $this->get();
        $protectedResource = (array) ($settings['protected_resource'] ?? []);
        $servers           = is_array($protectedResource['authorization_servers'] ?? null)
            ? $protectedResource['authorization_servers']
            : [];

        return [
            'enabled'               => (bool) ($protectedResource['enabled'] ?? false),
            'resource'              => (string) ($protectedResource['resource'] ?? ''),
            'authorization_servers' => array_values(array_unique(array_map('strval', $servers))),
        ];
    }
}
