<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\McpServerCard;

final class McpServerCardRequestMatcher
{
    /**
     * @param array<string,mixed> $context
     */
    public function evaluate(array $context): McpServerCardResolutionDecision
    {
        if (empty($context['is_server_card_route'])) {
            return new McpServerCardResolutionDecision(false, 'not_request_target');
        }

        if (empty($context['feature_enabled'])) {
            return new McpServerCardResolutionDecision(false, 'feature_disabled');
        }

        if (! empty($context['physical_file_conflict']) || (array_key_exists('runtime_available', $context) && empty($context['runtime_available']))) {
            return new McpServerCardResolutionDecision(false, 'physical_file_conflict');
        }

        if ((string) ($context['name'] ?? '') === '' || (string) ($context['version'] ?? '') === '' || (string) ($context['transport'] ?? '') === '') {
            return new McpServerCardResolutionDecision(false, 'settings_incomplete');
        }

        return new McpServerCardResolutionDecision(true, 'eligible');
    }
}
