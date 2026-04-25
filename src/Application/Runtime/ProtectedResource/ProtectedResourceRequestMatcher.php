<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ProtectedResource;

final class ProtectedResourceRequestMatcher
{
    /**
     * @param array<string,mixed> $context
     */
    public function evaluate(array $context): ProtectedResourceResolutionDecision
    {
        if (empty($context['is_protected_resource_route'])) {
            return new ProtectedResourceResolutionDecision(false, 'not_request_target');
        }

        if (empty($context['protected_apis_enabled'])) {
            return new ProtectedResourceResolutionDecision(false, 'protected_apis_disabled');
        }

        if (empty($context['feature_enabled'])) {
            return new ProtectedResourceResolutionDecision(false, 'feature_disabled');
        }

        if (! empty($context['physical_file_conflict']) || (array_key_exists('runtime_available', $context) && empty($context['runtime_available']))) {
            return new ProtectedResourceResolutionDecision(false, 'physical_file_conflict');
        }

        if ((string) ($context['resource'] ?? '') === '' || (array) ($context['authorization_servers'] ?? []) === []) {
            return new ProtectedResourceResolutionDecision(false, 'settings_incomplete');
        }

        return new ProtectedResourceResolutionDecision(true, 'eligible');
    }
}
