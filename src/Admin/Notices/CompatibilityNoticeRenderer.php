<?php

declare(strict_types=1);

namespace AgentReadyWP\Admin\Notices;

final class CompatibilityNoticeRenderer
{
    /**
     * @param array<int,array<string,mixed>> $warnings
     */
    public function render(array $warnings): string
    {
        if ($warnings === []) {
            return '';
        }

        $html = '';
        foreach ($warnings as $warning) {
            $level    = (string) ($warning['severity'] ?? 'warning');
            $severity = match ($level) {
                'info' => 'notice-info',
                'error' => 'notice-error',
                'success' => 'notice-success',
                default => 'notice-warning',
            };
            $message  = esc_html((string) ($warning['message'] ?? ''));
            $action   = (string) ($warning['manual_action'] ?? '');

            $html .= '<div class="notice ' . esc_attr($severity) . ' inline"><p>';
            $html .= $message;

            if ($action !== '') {
                $html .= ' ' . esc_html($action);
            }

            $html .= '</p></div>';
        }

        return $html;
    }
}
