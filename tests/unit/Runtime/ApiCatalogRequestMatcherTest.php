<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRequestMatcher;
use PHPUnit\Framework\TestCase;

final class ApiCatalogRequestMatcherTest extends TestCase
{
    public function test_request_matcher_denies_non_catalog_routes(): void
    {
        $matcher  = new ApiCatalogRequestMatcher();
        $decision = $matcher->evaluate([
            'is_catalog_route'       => false,
            'feature_enabled'        => true,
            'physical_file_conflict' => false,
        ])->toArray();

        $this->assertFalse($decision['applies']);
        $this->assertSame('not_catalog_route', $decision['reason']);
    }

    public function test_request_matcher_denies_disabled_or_conflicting_routes(): void
    {
        $matcher = new ApiCatalogRequestMatcher();

        $disabled = $matcher->evaluate([
            'is_catalog_route'       => true,
            'feature_enabled'        => false,
            'physical_file_conflict' => false,
        ])->toArray();
        $this->assertSame('feature_disabled', $disabled['reason']);

        $conflict = $matcher->evaluate([
            'is_catalog_route'       => true,
            'feature_enabled'        => true,
            'physical_file_conflict' => true,
        ])->toArray();
        $this->assertSame('physical_file_conflict', $conflict['reason']);
    }

    public function test_request_matcher_allows_eligible_catalog_routes(): void
    {
        $matcher  = new ApiCatalogRequestMatcher();
        $decision = $matcher->evaluate([
            'is_catalog_route'       => true,
            'feature_enabled'        => true,
            'physical_file_conflict' => false,
        ])->toArray();

        $this->assertTrue($decision['applies']);
        $this->assertSame('eligible', $decision['reason']);
    }
}
