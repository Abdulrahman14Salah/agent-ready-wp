<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ApiCatalog;

final class ApiCatalogRequestMatcher
{
    /**
     * @param array<string,mixed> $context
     */
    public function evaluate(array $context): ApiCatalogResolutionDecision
    {
        if (empty($context['is_catalog_route'])) {
            return new ApiCatalogResolutionDecision(false, 'not_catalog_route');
        }

        if (empty($context['feature_enabled'])) {
            return new ApiCatalogResolutionDecision(false, 'feature_disabled');
        }

        if (! empty($context['physical_file_conflict'])) {
            return new ApiCatalogResolutionDecision(false, 'physical_file_conflict');
        }

        if (array_key_exists('runtime_available', $context) && empty($context['runtime_available'])) {
            return new ApiCatalogResolutionDecision(false, 'physical_file_conflict');
        }

        return new ApiCatalogResolutionDecision(true, 'eligible');
    }
}
