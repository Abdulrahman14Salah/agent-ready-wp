<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Settings;

final class SettingsRepository
{
    private bool $apiCatalogChanged = false;
    /** @var array<string,string> */
    private array $lastValidationErrors = [];
    /** @var array<int,array<string,mixed>> */
    private array $lastValidationMessages = [];

    public function __construct(private readonly SettingsSanitizer $sanitizer)
    {
    }

    public function get(): array
    {
        $saved = get_option('agent_ready_wp_settings', []);
        if (! is_array($saved)) {
            $saved = [];
        }

        return $this->normalize($saved);
    }

    public function update(array $input): array
    {
        $result = $this->validateAndUpdate($input);

        return (array) $result['settings'];
    }

    /**
     * @return array{
     *   success: bool,
     *   settings: array<string,mixed>,
     *   draft: array<string,mixed>,
     *   errors: array<string,string>,
     *   messages: array<int,array<string,mixed>>
     * }
     */
    public function validateAndUpdate(array $input): array
    {
        $existing  = $this->get();
        $validated = $this->sanitizer->sanitizeAndValidate($input, $existing);
        $sanitized = (array) ($validated['settings'] ?? []);
        $errors    = (array) ($validated['errors'] ?? []);
        $messages  = (array) ($validated['messages'] ?? []);

        $this->lastValidationErrors   = $errors;
        $this->lastValidationMessages = $messages;

        if ($errors !== []) {
            $this->apiCatalogChanged = false;

            return [
                'success'  => false,
                'settings' => $existing,
                'draft'    => $this->normalize($sanitized),
                'errors'   => $errors,
                'messages' => $messages,
            ];
        }

        $this->apiCatalogChanged = ($existing['api_catalog'] ?? []) !== ($sanitized['api_catalog'] ?? []);
        update_option('agent_ready_wp_settings', $this->normalize($sanitized));

        return [
            'success'  => true,
            'settings' => $this->normalize($sanitized),
            'draft'    => $this->normalize($sanitized),
            'errors'   => [],
            'messages' => [],
        ];
    }

    public function apiCatalogChanged(): bool
    {
        return $this->apiCatalogChanged;
    }

    /**
     * @return array<string,string>
     */
    public function getLastValidationErrors(): array
    {
        return $this->lastValidationErrors;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getLastValidationMessages(): array
    {
        return $this->lastValidationMessages;
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<string,mixed>
     */
    private function normalize(array $settings): array
    {
        $normalized = array_replace_recursive(Defaults::all(), $settings);
        $markdown   = (array) ($settings['markdown'] ?? []);

        $normalized['enabled'] = (bool) ($normalized['enabled'] ?? false);
        $normalized['markdown']['enabled'] = (bool) ($normalized['markdown']['enabled'] ?? false);
        $normalized['markdown']['post_types'] = array_values(
            array_unique(
                array_map(
                    'strval',
                    is_array($markdown['post_types'] ?? null)
                        ? $markdown['post_types']
                        : (array) ($normalized['markdown']['post_types'] ?? [])
                )
            )
        );
        $normalized['markdown']['include_woo'] = (bool) ($normalized['markdown']['include_woo'] ?? false);
        $normalized['content_signals']['enabled'] = (bool) ($normalized['content_signals']['enabled'] ?? false);
        $normalized['api_catalog']['enabled'] = (bool) ($normalized['api_catalog']['enabled'] ?? false);
        $normalized['api_catalog']['include_wp_rest'] = (bool) ($normalized['api_catalog']['include_wp_rest'] ?? false);
        $normalized['api_catalog']['include_woo_rest'] = (bool) ($normalized['api_catalog']['include_woo_rest'] ?? false);
        $normalized['webmcp']['enabled'] = (bool) ($normalized['webmcp']['enabled'] ?? false);
        $normalized['webmcp']['tools'] = [
            'search'       => (bool) ($normalized['webmcp']['tools']['search'] ?? false),
            'get_posts'    => (bool) ($normalized['webmcp']['tools']['get_posts'] ?? false),
            'get_page'     => (bool) ($normalized['webmcp']['tools']['get_page'] ?? false),
            'get_products' => (bool) ($normalized['webmcp']['tools']['get_products'] ?? false),
        ];

        $normalized['mcp_server_card']['enabled'] = (bool) ($normalized['mcp_server_card']['enabled'] ?? false);
        $normalized['mcp_server_card']['name'] = sanitize_text_field((string) ($normalized['mcp_server_card']['name'] ?? ''));
        $normalized['mcp_server_card']['version'] = sanitize_text_field((string) ($normalized['mcp_server_card']['version'] ?? ''));
        $normalized['mcp_server_card']['transport'] = esc_url_raw((string) ($normalized['mcp_server_card']['transport'] ?? ''));

        $normalized['protected_apis']['enabled'] = (bool) ($normalized['protected_apis']['enabled'] ?? false);

        $normalized['oauth']['enabled'] = (bool) ($normalized['oauth']['enabled'] ?? false);
        $normalized['oauth']['issuer'] = sanitize_text_field((string) ($normalized['oauth']['issuer'] ?? ''));
        $normalized['oauth']['authorization_endpoint'] = esc_url_raw((string) ($normalized['oauth']['authorization_endpoint'] ?? ''));
        $normalized['oauth']['token_endpoint'] = esc_url_raw((string) ($normalized['oauth']['token_endpoint'] ?? ''));
        $normalized['oauth']['jwks_uri'] = esc_url_raw((string) ($normalized['oauth']['jwks_uri'] ?? ''));

        $normalized['protected_resource']['enabled'] = (bool) ($normalized['protected_resource']['enabled'] ?? false);
        $normalized['protected_resource']['resource'] = esc_url_raw((string) ($normalized['protected_resource']['resource'] ?? ''));
        $normalized['protected_resource']['authorization_servers'] = $this->normalizeAuthorizationServers(
            $normalized['protected_resource']['authorization_servers'] ?? []
        );

        return $normalized;
    }

    /**
     * @param mixed $value
     * @return array<int,string>
     */
    private function normalizeAuthorizationServers(mixed $value): array
    {
        $servers = is_array($value) ? $value : [];
        $clean   = [];

        foreach ($servers as $server) {
            $url = esc_url_raw((string) $server);
            if ($url === '') {
                continue;
            }
            $clean[] = $url;
        }

        return array_values(array_unique($clean));
    }
}
