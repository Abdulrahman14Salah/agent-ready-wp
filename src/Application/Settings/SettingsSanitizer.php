<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Settings;

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;

final class SettingsSanitizer
{
    public function __construct(private readonly EnvironmentDetector $environmentDetector)
    {
    }

    public function sanitize(array $input, array $existing = []): array
    {
        $defaults      = Defaults::all();
        $compatibility = $this->environmentDetector->detect();
        $sanitized     = array_replace_recursive($defaults, $existing);

        $sanitized['enabled'] = ! empty($input['enabled'] ?? null);

        $sanitized['markdown']['enabled'] = ! empty($input['markdown']['enabled'] ?? null);
        $sanitized['markdown']['post_types'] = $this->sanitizePostTypes(
            $input['markdown']['post_types'] ?? [],
            $compatibility['public_cpts']
        );
        $sanitized['markdown']['include_woo'] = ! empty($input['markdown']['include_woo'] ?? null) && $compatibility['woocommerce_active'];

        $sanitized['content_signals']['enabled'] = ! empty($input['content_signals']['enabled'] ?? null);
        $sanitized['content_signals']['ai_train'] = $this->sanitizeTriState($input['content_signals']['ai_train'] ?? '');
        $sanitized['content_signals']['search'] = $this->sanitizeTriState($input['content_signals']['search'] ?? '');
        $sanitized['content_signals']['ai_input'] = $this->sanitizeTriState($input['content_signals']['ai_input'] ?? '');

        $sanitized['api_catalog']['enabled'] = ! empty($input['api_catalog']['enabled'] ?? null);
        $sanitized['api_catalog']['include_wp_rest'] = ! empty($input['api_catalog']['include_wp_rest'] ?? null);
        $sanitized['api_catalog']['include_woo_rest'] = ! empty($input['api_catalog']['include_woo_rest'] ?? null) && $compatibility['woocommerce_active'];
        $sanitized['api_catalog']['custom_entries'] = $this->sanitizeCustomEntries($input['api_catalog']['custom_entries'] ?? []);

        $sanitized['webmcp']['enabled'] = ! empty($input['webmcp']['enabled'] ?? null);
        $sanitized['webmcp']['tools'] = [
            'search'       => ! empty($input['webmcp']['tools']['search'] ?? null),
            'get_posts'    => ! empty($input['webmcp']['tools']['get_posts'] ?? null),
            'get_page'     => ! empty($input['webmcp']['tools']['get_page'] ?? null),
            'get_products' => ! empty($input['webmcp']['tools']['get_products'] ?? null) && $compatibility['woocommerce_active'],
        ];

        $mcpInput = $this->normalizeInputSection($input['mcp_server_card'] ?? []);
        $sanitized['mcp_server_card']['enabled'] = $this->sanitizeBoolField(
            $mcpInput,
            'enabled',
            (bool) ($sanitized['mcp_server_card']['enabled'] ?? false)
        );
        $sanitized['mcp_server_card']['name'] = $this->sanitizeTextFieldOrExisting(
            $mcpInput,
            'name',
            (string) ($sanitized['mcp_server_card']['name'] ?? '')
        );
        $sanitized['mcp_server_card']['version'] = $this->sanitizeTextFieldOrExisting(
            $mcpInput,
            'version',
            (string) ($sanitized['mcp_server_card']['version'] ?? '')
        );
        $sanitized['mcp_server_card']['transport'] = $this->sanitizeUrlFieldOrExisting(
            $mcpInput,
            'transport',
            (string) ($sanitized['mcp_server_card']['transport'] ?? '')
        );

        $protectedApisInput = $this->normalizeInputSection($input['protected_apis'] ?? []);
        $sanitized['protected_apis']['enabled'] = $this->sanitizeBoolField(
            $protectedApisInput,
            'enabled',
            (bool) ($sanitized['protected_apis']['enabled'] ?? false)
        );

        $oauthInput = $this->normalizeInputSection($input['oauth'] ?? []);
        $sanitized['oauth']['enabled'] = $this->sanitizeBoolField(
            $oauthInput,
            'enabled',
            (bool) ($sanitized['oauth']['enabled'] ?? false)
        );
        $sanitized['oauth']['issuer'] = $this->sanitizeTextFieldOrExisting(
            $oauthInput,
            'issuer',
            (string) ($sanitized['oauth']['issuer'] ?? '')
        );
        $sanitized['oauth']['authorization_endpoint'] = $this->sanitizeUrlFieldOrExisting(
            $oauthInput,
            'authorization_endpoint',
            (string) ($sanitized['oauth']['authorization_endpoint'] ?? '')
        );
        $sanitized['oauth']['token_endpoint'] = $this->sanitizeUrlFieldOrExisting(
            $oauthInput,
            'token_endpoint',
            (string) ($sanitized['oauth']['token_endpoint'] ?? '')
        );
        $sanitized['oauth']['jwks_uri'] = $this->sanitizeUrlFieldOrExisting(
            $oauthInput,
            'jwks_uri',
            (string) ($sanitized['oauth']['jwks_uri'] ?? '')
        );

        $protectedResourceInput = $this->normalizeInputSection($input['protected_resource'] ?? []);
        $sanitized['protected_resource']['enabled'] = $this->sanitizeBoolField(
            $protectedResourceInput,
            'enabled',
            (bool) ($sanitized['protected_resource']['enabled'] ?? false)
        );
        $sanitized['protected_resource']['resource'] = $this->sanitizeUrlFieldOrExisting(
            $protectedResourceInput,
            'resource',
            (string) ($sanitized['protected_resource']['resource'] ?? '')
        );
        $sanitized['protected_resource']['authorization_servers'] = $this->sanitizeUrlListFieldOrExisting(
            $protectedResourceInput,
            'authorization_servers',
            (array) ($sanitized['protected_resource']['authorization_servers'] ?? [])
        );

        return $sanitized;
    }

    /**
     * @return array{
     *   settings: array<string,mixed>,
     *   errors: array<string,string>,
     *   messages: array<int,array<string,mixed>>
     * }
     */
    public function sanitizeAndValidate(array $input, array $existing = []): array
    {
        $sanitized = $this->sanitize($input, $existing);
        $errors    = $this->validatePhaseTwo($sanitized, $input);

        return [
            'settings' => $sanitized,
            'errors'   => $errors,
            'messages' => $this->buildPhaseTwoMessages($errors),
        ];
    }

    private function sanitizeTriState(mixed $value): string
    {
        $value = sanitize_text_field((string) $value);

        return in_array($value, ['', 'yes', 'no'], true) ? $value : '';
    }

    /**
     * @param mixed $postTypes
     * @param array<int,string> $publicCpts
     * @return array<int,string>
     */
    private function sanitizePostTypes(mixed $postTypes, array $publicCpts): array
    {
        $allowed = array_values(array_unique(array_merge(['post', 'page'], $publicCpts)));
        $values  = is_array($postTypes) ? $postTypes : [];

        $sanitized = [];
        foreach ($values as $value) {
            $value = sanitize_key((string) $value);
            if (in_array($value, $allowed, true)) {
                $sanitized[] = $value;
            }
        }

        return array_values(array_unique($sanitized));
    }

    /**
     * @param mixed $entries
     * @return array<int,array<string,string>>
     */
    private function sanitizeCustomEntries(mixed $entries): array
    {
        if (! is_array($entries)) {
            return [];
        }

        $sanitized = [];
        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $name        = sanitize_text_field((string) ($entry['name'] ?? ''));
            $anchor      = esc_url_raw((string) ($entry['anchor'] ?? ''));
            $serviceDesc = esc_url_raw((string) ($entry['service_desc'] ?? ''));

            if ($name === '' || $anchor === '' || $serviceDesc === '') {
                continue;
            }

            $sanitized[] = [
                'name'         => $name,
                'anchor'       => $anchor,
                'service_desc' => $serviceDesc,
            ];
        }

        return $sanitized;
    }

    /**
     * @return array<string,mixed>
     */
    private function normalizeInputSection(mixed $section): array
    {
        return is_array($section) ? $section : [];
    }

    /**
     * @param array<string,mixed> $input
     */
    private function sanitizeBoolField(array $input, string $key, bool $existing): bool
    {
        if (! array_key_exists($key, $input)) {
            return $existing;
        }

        $value = $input[$key];

        return ! empty($value) && (string) $value !== '0';
    }

    /**
     * @param array<string,mixed> $input
     */
    private function sanitizeTextFieldOrExisting(array $input, string $key, string $existing): string
    {
        if (! array_key_exists($key, $input)) {
            return $existing;
        }

        return sanitize_text_field((string) $input[$key]);
    }

    /**
     * @param array<string,mixed> $input
     */
    private function sanitizeUrlFieldOrExisting(array $input, string $key, string $existing): string
    {
        if (! array_key_exists($key, $input)) {
            return $existing;
        }

        return esc_url_raw((string) $input[$key]);
    }

    /**
     * @param array<string,mixed> $input
     * @param array<int,string>   $existing
     * @return array<int,string>
     */
    private function sanitizeUrlListFieldOrExisting(array $input, string $key, array $existing): array
    {
        if (! array_key_exists($key, $input)) {
            return $existing;
        }

        return $this->sanitizeUrlList($input[$key]);
    }

    /**
     * @return array<int,string>
     */
    private function sanitizeUrlList(mixed $value): array
    {
        $rawList = [];

        if (is_array($value)) {
            $rawList = $value;
        } elseif (is_string($value)) {
            $rawList = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        $sanitized = [];
        foreach ($rawList as $item) {
            $url = esc_url_raw(trim((string) $item));
            if ($url === '') {
                continue;
            }
            $sanitized[] = $url;
        }

        return array_values(array_unique($sanitized));
    }

    /**
     * @param array<string,mixed> $settings
     * @param array<string,mixed> $input
     * @return array<string,string>
     */
    private function validatePhaseTwo(array $settings, array $input): array
    {
        $errors   = [];
        $mcp      = (array) ($settings['mcp_server_card'] ?? []);
        $oauth    = (array) ($settings['oauth'] ?? []);
        $resource = (array) ($settings['protected_resource'] ?? []);

        if (! empty($mcp['enabled'])) {
            if ((string) ($mcp['name'] ?? '') === '') {
                $errors['mcp_server_card.name'] = __('Server name is required.', 'agent-ready-wp');
            }
            if ((string) ($mcp['version'] ?? '') === '') {
                $errors['mcp_server_card.version'] = __('Server version is required.', 'agent-ready-wp');
            }
            if (! $this->isValidUrl((string) ($mcp['transport'] ?? ''))) {
                $errors['mcp_server_card.transport'] = __('Transport must be a valid URL.', 'agent-ready-wp');
            }
        }

        if (! empty($settings['protected_apis']['enabled'])) {
            if (! empty($oauth['enabled'])) {
                if (! $this->isValidUrl((string) ($oauth['issuer'] ?? ''))) {
                    $errors['oauth.issuer'] = __('Issuer must be a valid URL.', 'agent-ready-wp');
                }
                if (! $this->isValidUrl((string) ($oauth['authorization_endpoint'] ?? ''))) {
                    $errors['oauth.authorization_endpoint'] = __('Authorization endpoint must be a valid URL.', 'agent-ready-wp');
                }
                if (! $this->isValidUrl((string) ($oauth['token_endpoint'] ?? ''))) {
                    $errors['oauth.token_endpoint'] = __('Token endpoint must be a valid URL.', 'agent-ready-wp');
                }
                if (! $this->isValidUrl((string) ($oauth['jwks_uri'] ?? ''))) {
                    $errors['oauth.jwks_uri'] = __('JWKS URI must be a valid URL.', 'agent-ready-wp');
                }
            }

            if (! empty($resource['enabled'])) {
                if (! $this->isValidUrl((string) ($resource['resource'] ?? ''))) {
                    $errors['protected_resource.resource'] = __('Resource must be a valid URL.', 'agent-ready-wp');
                }

                $servers = (array) ($resource['authorization_servers'] ?? []);
                if ($servers === []) {
                    $errors['protected_resource.authorization_servers'] = __('At least one authorization server URL is required.', 'agent-ready-wp');
                } elseif ($this->hasInvalidAuthorizationServersInput($input, $servers)) {
                    $errors['protected_resource.authorization_servers'] = __('Authorization server entries must be valid URLs.', 'agent-ready-wp');
                }
            }
        }

        return $errors;
    }

    private function isValidUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param array<string,mixed> $input
     * @param array<int,string>   $sanitizedServers
     */
    private function hasInvalidAuthorizationServersInput(array $input, array $sanitizedServers): bool
    {
        $resourceInput = $this->normalizeInputSection($input['protected_resource'] ?? []);
        if (! array_key_exists('authorization_servers', $resourceInput)) {
            return false;
        }

        $rawServers = $resourceInput['authorization_servers'];
        $rawList    = [];

        if (is_array($rawServers)) {
            $rawList = $rawServers;
        } elseif (is_string($rawServers)) {
            $rawList = preg_split('/[\r\n,]+/', $rawServers) ?: [];
        }

        $nonEmptyRaw = [];
        foreach ($rawList as $entry) {
            $trimmed = trim((string) $entry);
            if ($trimmed !== '') {
                $nonEmptyRaw[] = $trimmed;
            }
        }

        return count($sanitizedServers) < count($nonEmptyRaw);
    }

    /**
     * @param array<string,string> $errors
     * @return array<int,array<string,mixed>>
     */
    private function buildPhaseTwoMessages(array $errors): array
    {
        $messages = [];
        foreach ($errors as $key => $message) {
            $targetSection = null;
            $targetControl = null;
            $parts         = explode('.', $key, 2);

            if (isset($parts[0])) {
                $targetSection = $parts[0];
            }
            if (isset($parts[1])) {
                $targetControl = $parts[1];
            }

            $messages[] = [
                'message_key'    => 'phase2_validation_' . $key,
                'severity'       => 'error',
                'message'        => $message,
                'target_section' => $targetSection,
                'target_control' => $targetControl,
            ];
        }

        return $messages;
    }
}
