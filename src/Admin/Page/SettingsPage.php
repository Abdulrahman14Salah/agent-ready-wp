<?php

declare(strict_types=1);

namespace AgentReadyWP\Admin\Page;

use AgentReadyWP\Admin\Notices\CompatibilityNoticeRenderer;
use AgentReadyWP\Admin\ViewModel\SettingsPageViewModelFactory;
use AgentReadyWP\Application\Scan\ScanCache;
use AgentReadyWP\Application\Settings\SettingsRepository;

final class SettingsPage
{
    private string $pageHook = '';

    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        private readonly ScanCache $scanCache,
        private readonly SettingsPageViewModelFactory $viewModelFactory,
        private readonly CompatibilityNoticeRenderer $noticeRenderer
    ) {
    }

    public function registerMenu(): void
    {
        $this->pageHook = add_options_page(
            __('Agent Ready', 'agent-ready-wp'),
            __('Agent Ready', 'agent-ready-wp'),
            'manage_options',
            'agent-ready-wp',
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            'arwp_settings_group',
            'agent_ready_wp_settings',
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitizeSettings'],
                'default'           => $this->settingsRepository->get(),
            ]
        );
    }

    public function sanitizeSettings(mixed $input): array
    {
        if (! is_array($input)) {
            $input = [];
        }

        return $this->settingsRepository->sanitizeForStorage($input);
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'agent-ready-wp'));
        }

        $saveState = $this->handleSaveRequest();

        $viewModel = $this->viewModelFactory->create(
            $this->scanCache->get(),
            $saveState['draft'] ?? null,
            $saveState['errors'] ?? [],
            $saveState['messages'] ?? []
        );

        $summary  = $viewModel['readiness_summary'];
        $warnings = array_merge(
            (array) ($viewModel['compatibility_state']['warnings'] ?? []),
            (array) ($viewModel['pending_messages'] ?? [])
        );

        echo '<div class="wrap arwp-settings-page">';
        echo '<h1>' . esc_html__('Agent Ready WP', 'agent-ready-wp') . '</h1>';
        echo '<div class="arwp-page-actions">';
        echo '<button type="button" class="button button-primary" id="arwp-run-scan">' . esc_html__('Run Scan', 'agent-ready-wp') . '</button>';
        echo '</div>';

        echo wp_kses_post($this->noticeRenderer->render($warnings));
        echo '<div id="arwp-scan-status" class="notice inline" style="display:none;"><p></p></div>';
        echo '<section id="arwp-readiness-summary">';
        $this->renderSummary($summary);
        echo '</section>';

        echo '<form method="post" action="' . esc_attr(admin_url('options-general.php?page=agent-ready-wp')) . '">';
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('arwp_save_settings', 'arwp_settings_nonce');
        }
        echo '<input type="hidden" name="arwp_save_settings" value="1" />';

        echo '<div class="arwp-capability-panels">';
        foreach ((array) $viewModel['capability_panels'] as $panel) {
            $this->renderPanel($panel);
        }
        echo '</div>';

        $this->renderPhaseTwo(
            (array) ($viewModel['phase_two_sections'] ?? []),
            (array) ($viewModel['phase_two_preview'] ?? []),
            (array) ($viewModel['phase_two_continuity'] ?? []),
            (array) ($viewModel['phase_two_missing_guidance'] ?? [])
        );

        submit_button(__('Save Settings', 'agent-ready-wp'));
        echo '</form>';
        echo '</div>';
    }

    public function getPageHook(): string
    {
        return $this->pageHook;
    }

    /**
     * @return array<string,mixed>
     */
    private function handleSaveRequest(): array
    {
        if (! isset($_POST['arwp_save_settings'])) {
            return [
                'draft' => null,
                'errors' => [],
                'messages' => [],
            ];
        }

        if (! current_user_can('manage_options')) {
            return [
                'draft' => null,
                'errors' => [],
                'messages' => [
                    [
                        'severity' => 'error',
                        'message' => __('You do not have permission to save these settings.', 'agent-ready-wp'),
                        'target_section' => null,
                        'target_control' => null,
                    ],
                ],
            ];
        }

        if (! $this->hasValidSaveNonce()) {
            return [
                'draft' => null,
                'errors' => [],
                'messages' => [
                    [
                        'severity' => 'error',
                        'message' => __('Security check failed. Please try again.', 'agent-ready-wp'),
                        'target_section' => null,
                        'target_control' => null,
                    ],
                ],
            ];
        }

        $input = $_POST['agent_ready_wp_settings'] ?? [];
        if (! is_array($input)) {
            $input = [];
        }

        $result = $this->settingsRepository->validateAndUpdate($input);

        $messages = (array) ($result['messages'] ?? []);
        if (! empty($result['success'])) {
            if ($this->settingsRepository->apiCatalogChanged()) {
                flush_rewrite_rules();
            }

            $messages[] = [
                'severity' => 'success',
                'message' => __('Settings saved.', 'agent-ready-wp'),
                'target_section' => null,
                'target_control' => null,
            ];
        } else {
            array_unshift(
                $messages,
                [
                    'severity' => 'error',
                    'message' => __('Phase 2 settings could not be saved. Review the highlighted fields and try again.', 'agent-ready-wp'),
                    'target_section' => null,
                    'target_control' => null,
                ]
            );
        }

        return [
            'draft' => $result['draft'] ?? null,
            'errors' => (array) ($result['errors'] ?? []),
            'messages' => $messages,
        ];
    }

    private function hasValidSaveNonce(): bool
    {
        if (! function_exists('wp_verify_nonce')) {
            return true;
        }

        $nonce = isset($_POST['arwp_settings_nonce']) ? (string) $_POST['arwp_settings_nonce'] : '';

        return $nonce !== '' && wp_verify_nonce($nonce, 'arwp_save_settings');
    }

    private function renderSummary(array $summary): void
    {
        echo '<div class="arwp-summary-card">';
        echo '<h2>' . esc_html__('Last Scan Result', 'agent-ready-wp') . '</h2>';
        echo '<p class="arwp-summary-score"><strong>' . esc_html__('Score', 'agent-ready-wp') . ':</strong> <span data-arwp-summary="score">' . esc_html((string) ($summary['score'] ?? 0)) . '</span></p>';
        echo '<p><strong>' . esc_html__('Level', 'agent-ready-wp') . ':</strong> <span data-arwp-summary="level_name">' . esc_html((string) ($summary['level_name'] ?? '')) . '</span></p>';
        echo '<p><strong>' . esc_html__('Scanned', 'agent-ready-wp') . ':</strong> <span data-arwp-summary="scanned_at">' . esc_html((string) ($summary['scanned_at'] ?? '')) . '</span></p>';
        if (! empty($summary['message'])) {
            echo '<p data-arwp-summary="message">' . esc_html((string) $summary['message']) . '</p>';
        }
        echo '<ul class="arwp-summary-groups" data-arwp-summary="groups">';
        foreach ((array) ($summary['groups'] ?? []) as $group) {
            $groupText = (int) ($group['total'] ?? 0) === 0
                ? (($group['label'] ?? '') . ': ' . __('Not checked', 'agent-ready-wp'))
                : (($group['label'] ?? '') . ': ' . ($group['passed'] ?? 0) . '/' . ($group['total'] ?? 0));
            echo '<li class="arwp-state-' . esc_attr((string) ($group['state'] ?? 'fail')) . '">';
            echo esc_html((string) $groupText);
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    private function renderPanel(array $panel): void
    {
        $key = (string) $panel['panel_key'];
        echo '<section class="arwp-panel" id="arwp-panel-' . esc_attr($key) . '">';
        echo '<h2>' . esc_html((string) $panel['title']) . '</h2>';
        if (! empty($panel['status_note'])) {
            echo '<p class="description">' . esc_html((string) $panel['status_note']) . '</p>';
        }

        echo '<label>';
        echo '<input type="checkbox" name="agent_ready_wp_settings[' . esc_attr($key) . '][enabled]" value="1" ' . checked(! empty($panel['enabled']), true, false) . ' />';
        echo ' ' . esc_html__('Enabled', 'agent-ready-wp');
        echo '</label>';
        $this->renderPanelFields($key, (array) $panel['controls']);

        if (! empty($panel['preview'])) {
            echo '<div class="arwp-preview"><strong>' . esc_html__('Preview', 'agent-ready-wp') . ':</strong> ';
            if (is_array($panel['preview']['display_value'])) {
                echo esc_html(wp_json_encode($panel['preview']['display_value']));
            } else {
                echo esc_html((string) $panel['preview']['display_value']);
            }
            echo '</div>';
        }
        echo '</section>';
    }

    /**
     * @param array<int,array<string,mixed>> $controls
     */
    private function renderPanelFields(string $panelKey, array $controls): void
    {
        foreach ($controls as $control) {
            $name     = (string) $control['control_key'];
            $value    = $control['value'];
            $disabled = ! empty($control['disabled']) ? 'disabled' : '';

            echo '<div class="arwp-control">';
            echo '<label>' . esc_html((string) $control['label']) . '</label> ';

            switch ($name) {
                case 'markdown_post_types':
                    $available = ['post', 'page'];
                    foreach ((array) $value as $selected) {
                        if (! in_array($selected, $available, true)) {
                            $available[] = $selected;
                        }
                    }
                    foreach ($available as $item) {
                        echo '<label class="arwp-inline-checkbox"><input type="checkbox" name="agent_ready_wp_settings[markdown][post_types][]" value="' . esc_attr($item) . '" ' . checked(in_array($item, (array) $value, true), true, false) . ' ' . esc_attr($disabled) . ' /> ' . esc_html($item) . '</label> ';
                    }
                    break;

                case 'markdown_include_woo':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[markdown][include_woo]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;

                case 'content_signals_ai_train':
                    $this->renderSelect('agent_ready_wp_settings[content_signals][ai_train]', (string) $value, $disabled);
                    break;

                case 'content_signals_search':
                    $this->renderSelect('agent_ready_wp_settings[content_signals][search]', (string) $value, $disabled);
                    break;

                case 'content_signals_ai_input':
                    $this->renderSelect('agent_ready_wp_settings[content_signals][ai_input]', (string) $value, $disabled);
                    break;

                case 'api_catalog_include_wp_rest':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[api_catalog][include_wp_rest]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;

                case 'api_catalog_include_woo_rest':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[api_catalog][include_woo_rest]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;

                case 'webmcp_search':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[webmcp][tools][search]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;

                case 'webmcp_get_posts':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[webmcp][tools][get_posts]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;

                case 'webmcp_get_page':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[webmcp][tools][get_page]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;

                case 'webmcp_get_products':
                    echo '<input type="checkbox" name="agent_ready_wp_settings[webmcp][tools][get_products]" value="1" ' . checked((bool) $value, true, false) . ' ' . esc_attr($disabled) . ' />';
                    break;
            }

            if (! empty($control['help_text'])) {
                echo '<p class="description">' . esc_html((string) $control['help_text']) . '</p>';
            }
            echo '</div>';
        }

        if ($panelKey === 'api_catalog') {
            echo '<div class="arwp-control">';
            echo '<label>' . esc_html__('Custom API entry', 'agent-ready-wp') . '</label>';
            echo '<input type="text" name="agent_ready_wp_settings[api_catalog][custom_entries][0][name]" placeholder="' . esc_attr__('Name', 'agent-ready-wp') . '" /> ';
            echo '<input type="url" name="agent_ready_wp_settings[api_catalog][custom_entries][0][anchor]" placeholder="' . esc_attr__('Anchor URL', 'agent-ready-wp') . '" /> ';
            echo '<input type="url" name="agent_ready_wp_settings[api_catalog][custom_entries][0][service_desc]" placeholder="' . esc_attr__('Service Description URL', 'agent-ready-wp') . '" />';
            echo '</div>';
        }
    }

    private function renderSelect(string $name, string $value, string $disabled): void
    {
        $options = ['', 'yes', 'no'];
        echo '<select name="' . esc_attr($name) . '" ' . esc_attr($disabled) . '>';
        foreach ($options as $option) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($value, $option, false) . '>' . esc_html($option === '' ? __('Unset', 'agent-ready-wp') : ucfirst($option)) . '</option>';
        }
        echo '</select>';
    }

    /**
     * @param array<string,array<string,mixed>> $sections
     * @param array<string,mixed>               $preview
     * @param array<int,array<string,mixed>>    $continuity
     * @param array<int,array<string,string>>   $missingGuidance
     */
    private function renderPhaseTwo(array $sections, array $preview, array $continuity, array $missingGuidance): void
    {
        echo '<section class="arwp-phase-two-sections" id="arwp-phase-two-sections">';
        echo '<h2>' . esc_html__('Phase 2 Foundation', 'agent-ready-wp') . '</h2>';

        if ($continuity !== []) {
            echo '<div class="arwp-phase-two-continuity">';
            foreach ($continuity as $item) {
                echo '<p class="description">' . esc_html((string) ($item['status_text'] ?? '')) . '</p>';
            }
            echo '</div>';
        }

        if ($missingGuidance !== []) {
            echo '<div class="arwp-phase-two-guidance" role="status">';
            echo '<h3>' . esc_html__('Configuration Guidance', 'agent-ready-wp') . '</h3>';
            echo '<ul>';
            foreach ($missingGuidance as $message) {
                echo '<li>' . esc_html((string) ($message['message'] ?? '')) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        if (isset($sections['protected_apis'])) {
            $this->renderProtectedApisSection((array) $sections['protected_apis']);
        }
        if (isset($sections['mcp_server_card'])) {
            $this->renderMcpSection((array) $sections['mcp_server_card']);
        }
        if (isset($sections['oauth_discovery'])) {
            $this->renderOAuthSection((array) $sections['oauth_discovery']);
        }
        if (isset($sections['protected_resource'])) {
            $this->renderProtectedResourceSection((array) $sections['protected_resource']);
        }

        $this->renderPhaseTwoPreview($preview);
        echo '</section>';
    }

    /**
     * @param array<string,mixed> $section
     */
    private function renderProtectedApisSection(array $section): void
    {
        $control = (array) (($section['controls'] ?? [])[0] ?? []);

        echo '<section class="arwp-panel arwp-phase2-section" id="arwp-panel-protected-apis">';
        echo '<h3>' . esc_html((string) ($section['title'] ?? '')) . '</h3>';
        echo '<p class="description">' . esc_html((string) ($section['description'] ?? '')) . '</p>';

        echo '<div class="arwp-control">';
        echo '<input type="hidden" name="agent_ready_wp_settings[protected_apis][enabled]" value="0" />';
        echo '<label><input type="checkbox" id="arwp-protected-apis-enabled" name="agent_ready_wp_settings[protected_apis][enabled]" value="1" ' . checked(! empty($control['value']), true, false) . ' /> ' . esc_html((string) ($control['label'] ?? '')) . '</label>';
        if (! empty($control['help_text'])) {
            echo '<p class="description">' . esc_html((string) $control['help_text']) . '</p>';
        }
        if (! empty($control['validation_error'])) {
            echo '<p class="arwp-field-error" role="alert">' . esc_html((string) $control['validation_error']) . '</p>';
        }
        echo '</div>';

        echo '</section>';
    }

    /**
     * @param array<string,mixed> $section
     */
    private function renderMcpSection(array $section): void
    {
        $controls = (array) ($section['controls'] ?? []);
        echo '<section class="arwp-panel arwp-phase2-section" id="arwp-panel-mcp-server-card" data-arwp-phase2-section="mcp_server_card">';
        echo '<h3>' . esc_html((string) ($section['title'] ?? '')) . '</h3>';
        echo '<p class="description">' . esc_html((string) ($section['description'] ?? '')) . '</p>';

        if (! empty($section['missing_requirements'])) {
            echo '<p class="arwp-phase2-missing">' . esc_html__('Missing:', 'agent-ready-wp') . ' ' . esc_html(implode(', ', (array) $section['missing_requirements'])) . '</p>';
        }

        foreach ($controls as $control) {
            $controlKey = (string) ($control['control_key'] ?? '');
            if ($controlKey === 'mcp_server_card_enabled') {
                echo '<div class="arwp-control">';
                echo '<input type="hidden" name="agent_ready_wp_settings[mcp_server_card][enabled]" value="0" />';
                echo '<label><input type="checkbox" name="agent_ready_wp_settings[mcp_server_card][enabled]" value="1" ' . checked(! empty($control['value']), true, false) . ' data-arwp-phase2-control="mcp_enabled" /> ' . esc_html((string) ($control['label'] ?? '')) . '</label>';
                if (! empty($control['help_text'])) {
                    echo '<p class="description">' . esc_html((string) $control['help_text']) . '</p>';
                }
                echo '</div>';
                continue;
            }

            $fieldName = '';
            if ($controlKey === 'mcp_server_card_name') {
                $fieldName = 'name';
            } elseif ($controlKey === 'mcp_server_card_version') {
                $fieldName = 'version';
            } elseif ($controlKey === 'mcp_server_card_transport') {
                $fieldName = 'transport';
            }

            if ($fieldName === '') {
                continue;
            }

            echo '<div class="arwp-control' . (! empty($control['validation_error']) ? ' arwp-control-has-error' : '') . '">';
            echo '<label for="arwp-' . esc_attr($controlKey) . '">' . esc_html((string) ($control['label'] ?? '')) . '</label>';
            $type = $fieldName === 'transport' ? 'url' : 'text';
            echo '<input type="' . esc_attr($type) . '" id="arwp-' . esc_attr($controlKey) . '" name="agent_ready_wp_settings[mcp_server_card][' . esc_attr($fieldName) . ']" value="' . esc_attr((string) ($control['value'] ?? '')) . '" data-arwp-phase2-control="' . esc_attr($fieldName) . '" />';
            if (! empty($control['validation_error'])) {
                echo '<p class="arwp-field-error" role="alert">' . esc_html((string) $control['validation_error']) . '</p>';
            }
            echo '</div>';
        }

        echo '</section>';
    }

    /**
     * @param array<string,mixed> $section
     */
    private function renderOAuthSection(array $section): void
    {
        $controls    = (array) ($section['controls'] ?? []);
        $isDisabled  = ! empty($section['disabled_reason']);
        $disabled    = $isDisabled ? 'disabled' : '';
        $sectionAttr = $isDisabled ? ' arwp-phase2-section-disabled' : '';

        echo '<section class="arwp-panel arwp-phase2-section arwp-phase2-section-conditional' . esc_attr($sectionAttr) . '" id="arwp-panel-oauth-discovery" data-arwp-phase2-conditional="oauth_discovery" aria-disabled="' . ($isDisabled ? 'true' : 'false') . '">';
        echo '<h3>' . esc_html((string) ($section['title'] ?? '')) . '</h3>';
        echo '<p class="description">' . esc_html((string) ($section['description'] ?? '')) . '</p>';

        if ($isDisabled) {
            echo '<p class="arwp-phase2-disabled-reason">' . esc_html((string) $section['disabled_reason']) . '</p>';
        }
        if (! empty($section['missing_requirements'])) {
            echo '<p class="arwp-phase2-missing">' . esc_html__('Missing:', 'agent-ready-wp') . ' ' . esc_html(implode(', ', (array) $section['missing_requirements'])) . '</p>';
        }

        foreach ($controls as $control) {
            $controlKey = (string) ($control['control_key'] ?? '');
            echo '<div class="arwp-control' . (! empty($control['validation_error']) ? ' arwp-control-has-error' : '') . '">';

            if ($controlKey === 'oauth_enabled') {
                echo '<input type="hidden" name="agent_ready_wp_settings[oauth][enabled]" value="0" />';
                echo '<label><input type="checkbox" name="agent_ready_wp_settings[oauth][enabled]" value="1" ' . checked(! empty($control['value']), true, false) . ' ' . esc_attr($disabled) . ' data-arwp-phase2-control="oauth_enabled" /> ' . esc_html((string) ($control['label'] ?? '')) . '</label>';
                echo '</div>';
                continue;
            }

            $fieldName = str_replace('oauth_', '', $controlKey);
            echo '<label for="arwp-' . esc_attr($controlKey) . '">' . esc_html((string) ($control['label'] ?? '')) . '</label>';
            echo '<input type="url" id="arwp-' . esc_attr($controlKey) . '" name="agent_ready_wp_settings[oauth][' . esc_attr($fieldName) . ']" value="' . esc_attr((string) ($control['value'] ?? '')) . '" ' . esc_attr($disabled) . ' data-arwp-phase2-control="oauth_' . esc_attr($fieldName) . '" />';
            if (! empty($control['validation_error'])) {
                echo '<p class="arwp-field-error" role="alert">' . esc_html((string) $control['validation_error']) . '</p>';
            }
            echo '</div>';
        }

        echo '</section>';
    }

    /**
     * @param array<string,mixed> $section
     */
    private function renderProtectedResourceSection(array $section): void
    {
        $controls    = (array) ($section['controls'] ?? []);
        $isDisabled  = ! empty($section['disabled_reason']);
        $disabled    = $isDisabled ? 'disabled' : '';
        $sectionAttr = $isDisabled ? ' arwp-phase2-section-disabled' : '';

        echo '<section class="arwp-panel arwp-phase2-section arwp-phase2-section-conditional' . esc_attr($sectionAttr) . '" id="arwp-panel-protected-resource" data-arwp-phase2-conditional="protected_resource" aria-disabled="' . ($isDisabled ? 'true' : 'false') . '">';
        echo '<h3>' . esc_html((string) ($section['title'] ?? '')) . '</h3>';
        echo '<p class="description">' . esc_html((string) ($section['description'] ?? '')) . '</p>';

        if ($isDisabled) {
            echo '<p class="arwp-phase2-disabled-reason">' . esc_html((string) $section['disabled_reason']) . '</p>';
        }
        if (! empty($section['missing_requirements'])) {
            echo '<p class="arwp-phase2-missing">' . esc_html__('Missing:', 'agent-ready-wp') . ' ' . esc_html(implode(', ', (array) $section['missing_requirements'])) . '</p>';
        }

        foreach ($controls as $control) {
            $controlKey = (string) ($control['control_key'] ?? '');
            echo '<div class="arwp-control' . (! empty($control['validation_error']) ? ' arwp-control-has-error' : '') . '">';

            if ($controlKey === 'protected_resource_enabled') {
                echo '<input type="hidden" name="agent_ready_wp_settings[protected_resource][enabled]" value="0" />';
                echo '<label><input type="checkbox" name="agent_ready_wp_settings[protected_resource][enabled]" value="1" ' . checked(! empty($control['value']), true, false) . ' ' . esc_attr($disabled) . ' data-arwp-phase2-control="resource_enabled" /> ' . esc_html((string) ($control['label'] ?? '')) . '</label>';
                echo '</div>';
                continue;
            }

            if ($controlKey === 'protected_resource_authorization_servers') {
                echo '<label for="arwp-' . esc_attr($controlKey) . '">' . esc_html((string) ($control['label'] ?? '')) . '</label>';
                echo '<textarea id="arwp-' . esc_attr($controlKey) . '" name="agent_ready_wp_settings[protected_resource][authorization_servers]" rows="4" ' . esc_attr($disabled) . ' data-arwp-phase2-control="authorization_servers">' . esc_textarea(implode("\n", (array) ($control['value'] ?? []))) . '</textarea>';
                if (! empty($control['help_text'])) {
                    echo '<p class="description">' . esc_html((string) $control['help_text']) . '</p>';
                }
                if (! empty($control['validation_error'])) {
                    echo '<p class="arwp-field-error" role="alert">' . esc_html((string) $control['validation_error']) . '</p>';
                }
                echo '</div>';
                continue;
            }

            if ($controlKey === 'protected_resource_resource') {
                echo '<label for="arwp-' . esc_attr($controlKey) . '">' . esc_html((string) ($control['label'] ?? '')) . '</label>';
                echo '<input type="url" id="arwp-' . esc_attr($controlKey) . '" name="agent_ready_wp_settings[protected_resource][resource]" value="' . esc_attr((string) ($control['value'] ?? '')) . '" ' . esc_attr($disabled) . ' data-arwp-phase2-control="resource" />';
                if (! empty($control['validation_error'])) {
                    echo '<p class="arwp-field-error" role="alert">' . esc_html((string) $control['validation_error']) . '</p>';
                }
                echo '</div>';
                continue;
            }

            echo '</div>';
        }

        echo '</section>';
    }

    /**
     * @param array<string,mixed> $preview
     */
    private function renderPhaseTwoPreview(array $preview): void
    {
        echo '<section class="arwp-panel arwp-phase2-preview" data-arwp-phase2-preview-root="1">';
        echo '<h3>' . esc_html__('Phase 2 Draft Preview', 'agent-ready-wp') . '</h3>';
        echo '<p class="description">' . esc_html__('Draft preview only. Published metadata changes after a successful save.', 'agent-ready-wp') . '</p>';
        echo '<ul class="arwp-phase2-preview-items">';

        foreach ((array) ($preview['items'] ?? []) as $item) {
            $sectionKey = (string) ($item['section_key'] ?? '');
            $label      = (string) ($item['label'] ?? '');
            $display    = $item['display_value'] ?? '';
            $text       = is_array($display) ? implode(' | ', array_map('strval', $display)) : (string) $display;

            echo '<li class="arwp-phase2-preview-item">';
            echo '<strong>' . esc_html($label) . ':</strong> ';
            echo '<span data-arwp-phase2-preview="' . esc_attr($sectionKey) . '">' . esc_html($text) . '</span>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</section>';
    }
}
