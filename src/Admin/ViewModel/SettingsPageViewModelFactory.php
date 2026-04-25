<?php

declare(strict_types=1);

namespace AgentReadyWP\Admin\ViewModel;

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Public\AdminPagePlaceholders;

final class SettingsPageViewModelFactory
{
    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        private readonly EnvironmentDetector $environmentDetector,
        private readonly AdminPagePlaceholders $placeholders,
        private readonly ScanSummaryMapper $scanSummaryMapper
    ) {
    }

    /**
     * @param array<string,mixed>|null            $draftSettings
     * @param array<string,string>                $validationErrors
     * @param array<int,array<string,mixed>>      $pendingMessages
     * @return array<string,mixed>
     */
    public function create(
        ?array $cachedSummary,
        ?array $draftSettings = null,
        array $validationErrors = [],
        array $pendingMessages = []
    ): array {
        $savedSettings = $this->settingsRepository->get();
        $settings      = $draftSettings === null
            ? $savedSettings
            : array_replace_recursive($savedSettings, $draftSettings);

        $compatibility = $this->environmentDetector->detect();
        $summary       = $cachedSummary ?: $this->scanSummaryMapper->empty();
        $preview       = $this->buildPhaseTwoPreview($settings);
        $messages      = $pendingMessages !== []
            ? $pendingMessages
            : $this->buildPendingMessagesFromErrors($validationErrors);

        return [
            'readiness_summary'         => $summary,
            'capability_panels'         => $this->buildPanels($settings, $compatibility),
            'compatibility_state'       => $compatibility,
            'phase_two_sections'        => $this->buildPhaseTwoSections($settings, $validationErrors, $preview),
            'phase_two_preview'         => $preview,
            'phase_two_continuity'      => $this->placeholders->all(),
            'phase_two_missing_guidance' => $this->buildMissingGuidance($settings),
            'pending_messages'          => $messages,
            'can_run_scan'              => current_user_can('manage_options'),
            'can_save_settings'         => current_user_can('manage_options'),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function buildPanels(array $settings, array $compatibility): array
    {
        $wooActive = (bool) $compatibility['woocommerce_active'];

        return [
            [
                'panel_key'    => 'markdown',
                'title'        => __('Markdown Negotiation', 'agent-ready-wp'),
                'enabled'      => (bool) $settings['markdown']['enabled'],
                'available'    => true,
                'expanded'     => true,
                'status_note'  => null,
                'controls'     => [
                    [
                        'control_key' => 'markdown_post_types',
                        'label'       => __('Post types', 'agent-ready-wp'),
                        'value'       => $settings['markdown']['post_types'],
                        'disabled'    => false,
                        'help_text'   => __('Choose which content types support markdown output.', 'agent-ready-wp'),
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'markdown_include_woo',
                        'label'       => __('Include WooCommerce products', 'agent-ready-wp'),
                        'value'       => (bool) $settings['markdown']['include_woo'],
                        'disabled'    => ! $wooActive,
                        'help_text'   => $wooActive ? '' : __('WooCommerce is not active.', 'agent-ready-wp'),
                        'validation_error' => null,
                    ],
                ],
                'preview'      => [
                    'preview_type'  => 'post_type_selection',
                    'display_value' => $settings['markdown']['post_types'],
                ],
            ],
            [
                'panel_key'   => 'content_signals',
                'title'       => __('Content Signals', 'agent-ready-wp'),
                'enabled'     => (bool) $settings['content_signals']['enabled'],
                'available'   => true,
                'expanded'    => false,
                'status_note' => ! empty($compatibility['physical_robots_txt_present']) ? __('A physical robots.txt file blocks automatic output.', 'agent-ready-wp') : null,
                'controls'    => [
                    [
                        'control_key' => 'content_signals_ai_train',
                        'label'       => __('AI Train', 'agent-ready-wp'),
                        'value'       => $settings['content_signals']['ai_train'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'content_signals_search',
                        'label'       => __('Search', 'agent-ready-wp'),
                        'value'       => $settings['content_signals']['search'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'content_signals_ai_input',
                        'label'       => __('AI Input', 'agent-ready-wp'),
                        'value'       => $settings['content_signals']['ai_input'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                ],
                'preview'     => [
                    'preview_type'  => 'content_signal_line',
                    'display_value' => $this->contentSignalPreview($settings['content_signals']),
                ],
            ],
            [
                'panel_key'   => 'api_catalog',
                'title'       => __('API Catalog', 'agent-ready-wp'),
                'enabled'     => (bool) $settings['api_catalog']['enabled'],
                'available'   => true,
                'expanded'    => false,
                'status_note' => ! empty($compatibility['api_catalog_file_conflict']) ? __('A physical API Catalog file may override the generated endpoint.', 'agent-ready-wp') : null,
                'controls'    => [
                    [
                        'control_key' => 'api_catalog_include_wp_rest',
                        'label'       => __('Include WordPress REST API', 'agent-ready-wp'),
                        'value'       => (bool) $settings['api_catalog']['include_wp_rest'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'api_catalog_include_woo_rest',
                        'label'       => __('Include WooCommerce REST API', 'agent-ready-wp'),
                        'value'       => (bool) $settings['api_catalog']['include_woo_rest'],
                        'disabled'    => ! $wooActive,
                        'help_text'   => $wooActive ? '' : __('WooCommerce is not active.', 'agent-ready-wp'),
                        'validation_error' => null,
                    ],
                ],
                'preview'     => [
                    'preview_type'  => 'api_catalog_entries',
                    'display_value' => $settings['api_catalog']['custom_entries'],
                ],
            ],
            [
                'panel_key'   => 'webmcp',
                'title'       => __('WebMCP Tools', 'agent-ready-wp'),
                'enabled'     => (bool) $settings['webmcp']['enabled'],
                'available'   => true,
                'expanded'    => false,
                'status_note' => null,
                'controls'    => [
                    [
                        'control_key' => 'webmcp_search',
                        'label'       => __('search', 'agent-ready-wp'),
                        'value'       => (bool) $settings['webmcp']['tools']['search'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'webmcp_get_posts',
                        'label'       => __('get_posts', 'agent-ready-wp'),
                        'value'       => (bool) $settings['webmcp']['tools']['get_posts'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'webmcp_get_page',
                        'label'       => __('get_page', 'agent-ready-wp'),
                        'value'       => (bool) $settings['webmcp']['tools']['get_page'],
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'webmcp_get_products',
                        'label'       => __('get_products', 'agent-ready-wp'),
                        'value'       => (bool) $settings['webmcp']['tools']['get_products'],
                        'disabled'    => ! $wooActive,
                        'help_text'   => $wooActive ? '' : __('WooCommerce is not active.', 'agent-ready-wp'),
                        'validation_error' => null,
                    ],
                ],
                'preview'     => [
                    'preview_type'  => 'tool_list',
                    'display_value' => array_keys(array_filter($settings['webmcp']['tools'])),
                ],
            ],
        ];
    }

    /**
     * @param array<string,mixed>         $settings
     * @param array<string,string>        $validationErrors
     * @param array<string,mixed>         $preview
     * @return array<string,array<string,mixed>>
     */
    private function buildPhaseTwoSections(array $settings, array $validationErrors, array $preview): array
    {
        $protectedApisEnabled = ! empty($settings['protected_apis']['enabled']);
        $disabledReason       = $protectedApisEnabled
            ? null
            : __('Enable protected APIs to edit OAuth and protected-resource metadata.', 'agent-ready-wp');

        $previewBySection = [];
        foreach ((array) ($preview['items'] ?? []) as $item) {
            if (! is_array($item) || empty($item['section_key'])) {
                continue;
            }
            $previewBySection[(string) $item['section_key']] = $item;
        }

        $mcpMissing = $this->missingMcpFields($settings);

        return [
            'protected_apis' => [
                'section_key'    => 'protected_apis',
                'title'          => __('Protected API Applicability', 'agent-ready-wp'),
                'description'    => __('Tell Agent Ready WP whether your site exposes protected APIs.', 'agent-ready-wp'),
                'active'         => true,
                'disabled_reason' => null,
                'missing_requirements' => [],
                'controls'       => [
                    [
                        'control_key' => 'protected_apis_enabled',
                        'label'       => __('This site has protected APIs', 'agent-ready-wp'),
                        'value'       => $protectedApisEnabled,
                        'disabled'    => false,
                        'help_text'   => __('When disabled, OAuth and protected-resource fields remain visible but read-only.', 'agent-ready-wp'),
                        'validation_error' => $validationErrors['protected_apis.enabled'] ?? null,
                    ],
                ],
            ],
            'mcp_server_card' => [
                'section_key'    => 'mcp_server_card',
                'title'          => __('MCP Server Card', 'agent-ready-wp'),
                'description'    => __('Configure server identity metadata exposed for discovery.', 'agent-ready-wp'),
                'active'         => true,
                'disabled_reason' => null,
                'missing_requirements' => $mcpMissing,
                'controls'       => [
                    [
                        'control_key' => 'mcp_server_card_enabled',
                        'label'       => __('Enable MCP Server Card metadata', 'agent-ready-wp'),
                        'value'       => (bool) ($settings['mcp_server_card']['enabled'] ?? false),
                        'disabled'    => false,
                        'help_text'   => __('Enable this section to validate and save MCP metadata.', 'agent-ready-wp'),
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'mcp_server_card_name',
                        'label'       => __('Server name', 'agent-ready-wp'),
                        'value'       => (string) ($settings['mcp_server_card']['name'] ?? ''),
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['mcp_server_card.name'] ?? null,
                    ],
                    [
                        'control_key' => 'mcp_server_card_version',
                        'label'       => __('Version', 'agent-ready-wp'),
                        'value'       => (string) ($settings['mcp_server_card']['version'] ?? ''),
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['mcp_server_card.version'] ?? null,
                    ],
                    [
                        'control_key' => 'mcp_server_card_transport',
                        'label'       => __('Transport URL', 'agent-ready-wp'),
                        'value'       => (string) ($settings['mcp_server_card']['transport'] ?? ''),
                        'disabled'    => false,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['mcp_server_card.transport'] ?? null,
                    ],
                ],
                'preview' => $previewBySection['mcp_server_card'] ?? null,
            ],
            'oauth_discovery' => [
                'section_key'    => 'oauth_discovery',
                'title'          => __('OAuth Discovery', 'agent-ready-wp'),
                'description'    => __('Set OAuth discovery values for protected APIs.', 'agent-ready-wp'),
                'active'         => $protectedApisEnabled,
                'disabled_reason' => $disabledReason,
                'missing_requirements' => $this->missingOAuthFields($settings, $protectedApisEnabled),
                'controls'       => [
                    [
                        'control_key' => 'oauth_enabled',
                        'label'       => __('Enable OAuth discovery metadata', 'agent-ready-wp'),
                        'value'       => (bool) ($settings['oauth']['enabled'] ?? false),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'oauth_issuer',
                        'label'       => __('Issuer', 'agent-ready-wp'),
                        'value'       => (string) ($settings['oauth']['issuer'] ?? ''),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['oauth.issuer'] ?? null,
                    ],
                    [
                        'control_key' => 'oauth_authorization_endpoint',
                        'label'       => __('Authorization endpoint', 'agent-ready-wp'),
                        'value'       => (string) ($settings['oauth']['authorization_endpoint'] ?? ''),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['oauth.authorization_endpoint'] ?? null,
                    ],
                    [
                        'control_key' => 'oauth_token_endpoint',
                        'label'       => __('Token endpoint', 'agent-ready-wp'),
                        'value'       => (string) ($settings['oauth']['token_endpoint'] ?? ''),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['oauth.token_endpoint'] ?? null,
                    ],
                    [
                        'control_key' => 'oauth_jwks_uri',
                        'label'       => __('JWKS URI', 'agent-ready-wp'),
                        'value'       => (string) ($settings['oauth']['jwks_uri'] ?? ''),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['oauth.jwks_uri'] ?? null,
                    ],
                ],
                'preview' => $previewBySection['oauth_discovery'] ?? null,
            ],
            'protected_resource' => [
                'section_key'    => 'protected_resource',
                'title'          => __('Protected Resource', 'agent-ready-wp'),
                'description'    => __('Declare protected-resource metadata and authorization servers.', 'agent-ready-wp'),
                'active'         => $protectedApisEnabled,
                'disabled_reason' => $disabledReason,
                'missing_requirements' => $this->missingProtectedResourceFields($settings, $protectedApisEnabled),
                'controls'       => [
                    [
                        'control_key' => 'protected_resource_enabled',
                        'label'       => __('Enable protected-resource metadata', 'agent-ready-wp'),
                        'value'       => (bool) ($settings['protected_resource']['enabled'] ?? false),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => null,
                    ],
                    [
                        'control_key' => 'protected_resource_resource',
                        'label'       => __('Resource URL', 'agent-ready-wp'),
                        'value'       => (string) ($settings['protected_resource']['resource'] ?? ''),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => '',
                        'validation_error' => $validationErrors['protected_resource.resource'] ?? null,
                    ],
                    [
                        'control_key' => 'protected_resource_authorization_servers',
                        'label'       => __('Authorization server URLs', 'agent-ready-wp'),
                        'value'       => (array) ($settings['protected_resource']['authorization_servers'] ?? []),
                        'disabled'    => ! $protectedApisEnabled,
                        'help_text'   => __('Use one URL per line.', 'agent-ready-wp'),
                        'validation_error' => $validationErrors['protected_resource.authorization_servers'] ?? null,
                    ],
                ],
                'preview' => $previewBySection['protected_resource'] ?? null,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<string,mixed>
     */
    private function buildPhaseTwoPreview(array $settings): array
    {
        $protectedApisEnabled = ! empty($settings['protected_apis']['enabled']);
        $mcp                  = (array) ($settings['mcp_server_card'] ?? []);
        $oauth                = (array) ($settings['oauth'] ?? []);
        $resource             = (array) ($settings['protected_resource'] ?? []);

        $mcpDisplay = __('Disabled', 'agent-ready-wp');
        if (! empty($mcp['enabled'])) {
            $mcpDisplay = sprintf(
                'Server: %s, Version: %s, Transport: %s',
                (string) ($mcp['name'] !== '' ? $mcp['name'] : __('(missing)', 'agent-ready-wp')),
                (string) ($mcp['version'] !== '' ? $mcp['version'] : __('(missing)', 'agent-ready-wp')),
                (string) ($mcp['transport'] !== '' ? $mcp['transport'] : __('(missing)', 'agent-ready-wp'))
            );
        }

        $oauthDisplay = __('Not applicable while protected APIs are disabled.', 'agent-ready-wp');
        if ($protectedApisEnabled) {
            if (! empty($oauth['enabled'])) {
                $oauthDisplay = [
                    'Issuer: ' . ((string) ($oauth['issuer'] !== '' ? $oauth['issuer'] : __('(missing)', 'agent-ready-wp'))),
                    'Authorization endpoint: ' . ((string) ($oauth['authorization_endpoint'] !== '' ? $oauth['authorization_endpoint'] : __('(missing)', 'agent-ready-wp'))),
                    'Token endpoint: ' . ((string) ($oauth['token_endpoint'] !== '' ? $oauth['token_endpoint'] : __('(missing)', 'agent-ready-wp'))),
                    'JWKS URI: ' . ((string) ($oauth['jwks_uri'] !== '' ? $oauth['jwks_uri'] : __('(missing)', 'agent-ready-wp'))),
                ];
            } else {
                $oauthDisplay = __('Disabled', 'agent-ready-wp');
            }
        }

        $resourceDisplay = __('Not applicable while protected APIs are disabled.', 'agent-ready-wp');
        if ($protectedApisEnabled) {
            if (! empty($resource['enabled'])) {
                $servers = (array) ($resource['authorization_servers'] ?? []);
                if ($servers === []) {
                    $servers = [__('(missing)', 'agent-ready-wp')];
                }

                $resourceDisplay = array_merge(
                    ['Resource: ' . ((string) ($resource['resource'] !== '' ? $resource['resource'] : __('(missing)', 'agent-ready-wp')))],
                    array_map(
                        static fn (string $server): string => 'Authorization server: ' . $server,
                        $servers
                    )
                );
            } else {
                $resourceDisplay = __('Disabled', 'agent-ready-wp');
            }
        }

        return [
            'is_draft' => true,
            'items'    => [
                [
                    'section_key'  => 'mcp_server_card',
                    'label'        => __('MCP Server Card', 'agent-ready-wp'),
                    'display_value' => $mcpDisplay,
                ],
                [
                    'section_key'  => 'oauth_discovery',
                    'label'        => __('OAuth Discovery', 'agent-ready-wp'),
                    'display_value' => $oauthDisplay,
                ],
                [
                    'section_key'  => 'protected_resource',
                    'label'        => __('Protected Resource', 'agent-ready-wp'),
                    'display_value' => $resourceDisplay,
                ],
            ],
        ];
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<int,array<string,string>>
     */
    private function buildMissingGuidance(array $settings): array
    {
        $guidance = [];

        $missingMcp = $this->missingMcpFields($settings);
        if ($missingMcp !== []) {
            $guidance[] = [
                'section_key' => 'mcp_server_card',
                'message' => __('Complete MCP Server Card fields before save.', 'agent-ready-wp'),
            ];
        }

        $protectedApisEnabled = ! empty($settings['protected_apis']['enabled']);
        $missingOAuth         = $this->missingOAuthFields($settings, $protectedApisEnabled);
        if ($missingOAuth !== []) {
            $guidance[] = [
                'section_key' => 'oauth_discovery',
                'message' => __('Complete OAuth discovery URLs before save.', 'agent-ready-wp'),
            ];
        }

        $missingResource = $this->missingProtectedResourceFields($settings, $protectedApisEnabled);
        if ($missingResource !== []) {
            $guidance[] = [
                'section_key' => 'protected_resource',
                'message' => __('Complete protected-resource values before save.', 'agent-ready-wp'),
            ];
        }

        return $guidance;
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<int,string>
     */
    private function missingMcpFields(array $settings): array
    {
        if (empty($settings['mcp_server_card']['enabled'])) {
            return [];
        }

        $missing = [];
        if ((string) ($settings['mcp_server_card']['name'] ?? '') === '') {
            $missing[] = __('Server name', 'agent-ready-wp');
        }
        if ((string) ($settings['mcp_server_card']['version'] ?? '') === '') {
            $missing[] = __('Version', 'agent-ready-wp');
        }
        if ((string) ($settings['mcp_server_card']['transport'] ?? '') === '') {
            $missing[] = __('Transport URL', 'agent-ready-wp');
        }

        return $missing;
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<int,string>
     */
    private function missingOAuthFields(array $settings, bool $protectedApisEnabled): array
    {
        if (! $protectedApisEnabled || empty($settings['oauth']['enabled'])) {
            return [];
        }

        $missing = [];
        if ((string) ($settings['oauth']['issuer'] ?? '') === '') {
            $missing[] = __('Issuer', 'agent-ready-wp');
        }
        if ((string) ($settings['oauth']['authorization_endpoint'] ?? '') === '') {
            $missing[] = __('Authorization endpoint', 'agent-ready-wp');
        }
        if ((string) ($settings['oauth']['token_endpoint'] ?? '') === '') {
            $missing[] = __('Token endpoint', 'agent-ready-wp');
        }
        if ((string) ($settings['oauth']['jwks_uri'] ?? '') === '') {
            $missing[] = __('JWKS URI', 'agent-ready-wp');
        }

        return $missing;
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<int,string>
     */
    private function missingProtectedResourceFields(array $settings, bool $protectedApisEnabled): array
    {
        if (! $protectedApisEnabled || empty($settings['protected_resource']['enabled'])) {
            return [];
        }

        $missing = [];
        if ((string) ($settings['protected_resource']['resource'] ?? '') === '') {
            $missing[] = __('Resource URL', 'agent-ready-wp');
        }

        $servers = (array) ($settings['protected_resource']['authorization_servers'] ?? []);
        if ($servers === []) {
            $missing[] = __('Authorization server URLs', 'agent-ready-wp');
        }

        return $missing;
    }

    /**
     * @param array<string,string> $errors
     * @return array<int,array<string,mixed>>
     */
    private function buildPendingMessagesFromErrors(array $errors): array
    {
        $messages = [];

        foreach ($errors as $path => $message) {
            $parts = explode('.', $path, 2);
            $messages[] = [
                'message_key' => 'phase2_error_' . $path,
                'severity' => 'error',
                'message' => $message,
                'target_section' => $parts[0] ?? null,
                'target_control' => $parts[1] ?? null,
            ];
        }

        return $messages;
    }

    private function contentSignalPreview(array $signals): string
    {
        $parts = [];
        foreach (['ai_train', 'search', 'ai_input'] as $key) {
            if (($signals[$key] ?? '') === '') {
                continue;
            }

            $parts[] = str_replace('_', '-', $key) . '=' . $signals[$key];
        }

        return 'Content-Signal: ' . implode(', ', $parts);
    }
}
