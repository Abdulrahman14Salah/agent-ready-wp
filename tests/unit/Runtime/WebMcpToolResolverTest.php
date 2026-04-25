<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\WebMcp\WebMcpToolResolver;
use PHPUnit\Framework\TestCase;

final class WebMcpToolResolverTest extends TestCase
{
    public function test_resolver_returns_default_tools_for_public_enabled_context(): void
    {
        $resolver = new WebMcpToolResolver();

        $decision = $resolver->resolve([
            'feature_enabled'    => true,
            'is_public_frontend' => true,
            'wp_head_supported'  => true,
            'woocommerce_active' => false,
            'selected_tools'     => [
                'search'       => true,
                'get_posts'    => true,
                'get_page'     => true,
                'get_products' => true,
            ],
        ])->toArray();

        $this->assertTrue($decision['applies']);
        $this->assertCount(3, $decision['tools']);
        $this->assertSame(['search', 'get_posts', 'get_page'], array_column($decision['tools'], 'name'));
    }

    public function test_resolver_includes_products_only_when_woocommerce_is_active(): void
    {
        $resolver = new WebMcpToolResolver();

        $decision = $resolver->resolve([
            'feature_enabled'    => true,
            'is_public_frontend' => true,
            'wp_head_supported'  => true,
            'woocommerce_active' => true,
            'selected_tools'     => [
                'search'       => false,
                'get_posts'    => false,
                'get_page'     => false,
                'get_products' => true,
            ],
        ])->toArray();

        $this->assertTrue($decision['applies']);
        $this->assertSame(['get_products'], array_column($decision['tools'], 'name'));
    }

    public function test_resolver_denies_unsupported_or_empty_contexts(): void
    {
        $resolver = new WebMcpToolResolver();

        $unsupported = $resolver->resolve([
            'feature_enabled'    => true,
            'is_public_frontend' => false,
            'wp_head_supported'  => true,
            'woocommerce_active' => false,
            'selected_tools'     => [],
        ])->toArray();
        $this->assertSame('unsupported_context', $unsupported['reason']);

        $empty = $resolver->resolve([
            'feature_enabled'    => true,
            'is_public_frontend' => true,
            'wp_head_supported'  => true,
            'woocommerce_active' => false,
            'selected_tools'     => [
                'search'       => false,
                'get_posts'    => false,
                'get_page'     => false,
                'get_products' => false,
            ],
        ])->toArray();
        $this->assertSame('no_tools_enabled', $empty['reason']);
    }
}
