<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\OAuthDiscovery;

final class OAuthDiscoveryRequestMatcher
{
    /**
     * @param array<string,mixed> $context
     */
    public function evaluate(array $context): OAuthDiscoveryResolutionDecision
    {
        if (empty($context['is_openid_configuration_route'])) {
            return new OAuthDiscoveryResolutionDecision(false, 'not_request_target');
        }

        if (empty($context['protected_apis_enabled'])) {
            return new OAuthDiscoveryResolutionDecision(false, 'protected_apis_disabled');
        }

        if (empty($context['feature_enabled'])) {
            return new OAuthDiscoveryResolutionDecision(false, 'feature_disabled');
        }

        if (! empty($context['physical_file_conflict']) || (array_key_exists('runtime_available', $context) && empty($context['runtime_available']))) {
            return new OAuthDiscoveryResolutionDecision(false, 'physical_file_conflict');
        }

        if (
            (string) ($context['issuer'] ?? '') === ''
            || (string) ($context['authorization_endpoint'] ?? '') === ''
            || (string) ($context['token_endpoint'] ?? '') === ''
            || (string) ($context['jwks_uri'] ?? '') === ''
        ) {
            return new OAuthDiscoveryResolutionDecision(false, 'settings_incomplete');
        }

        return new OAuthDiscoveryResolutionDecision(true, 'eligible');
    }
}
