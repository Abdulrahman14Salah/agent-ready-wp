<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class MarkdownEligibilityEvaluator
{
    /**
     * @param array<string,mixed> $context
     * @return array{applies: bool, reason: string, selected_representation: string}
     */
    public function evaluate(array $context): array
    {
        if (empty($context['feature_enabled'])) {
            return $this->deny('feature_disabled');
        }

        if (empty($context['markdown_preferred'])) {
            return $this->deny('accept_not_preferred');
        }

        if (array_key_exists('is_eligible_frontend_document_request', $context) && empty($context['is_eligible_frontend_document_request'])) {
            return $this->deny('unsupported_context');
        }

        if (empty($context['is_singular'])) {
            return $this->deny('unsupported_context');
        }

        if (empty($context['is_supported_post_type'])) {
            return $this->deny('unsupported_context');
        }

        if (empty($context['requester_can_view'])) {
            return $this->deny('access_denied');
        }

        return [
            'applies' => true,
            'reason' => 'eligible',
            'selected_representation' => 'markdown',
        ];
    }

    /**
     * @return array{applies: bool, reason: string, selected_representation: string}
     */
    private function deny(string $reason): array
    {
        return [
            'applies' => false,
            'reason' => $reason,
            'selected_representation' => 'default',
        ];
    }
}
