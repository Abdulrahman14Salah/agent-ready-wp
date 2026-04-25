<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\WebMcp;

final class WebMcpToolResolver
{
    /**
     * @param array<string,mixed> $context
     */
    public function resolve(array $context): WebMcpEmissionDecision
    {
        if (empty($context['feature_enabled'])) {
            return new WebMcpEmissionDecision(false, 'feature_disabled');
        }

        if (
            empty($context['is_public_frontend'])
            || empty($context['wp_head_supported'])
            || (array_key_exists('runtime_available', $context) && empty($context['runtime_available']))
        ) {
            return new WebMcpEmissionDecision(false, 'unsupported_context');
        }

        $selectedTools = (array) ($context['selected_tools'] ?? []);
        $tools         = [];
        $restRoot      = function_exists('rest_url') ? rest_url() : '';

        $definitions = [
            'search' => [
                'label'                => 'search',
                'route'                => rtrim($restRoot, '/') . '/wp/v2/search',
                'requires_woocommerce' => false,
            ],
            'get_posts' => [
                'label'                => 'get_posts',
                'route'                => rtrim($restRoot, '/') . '/wp/v2/posts',
                'requires_woocommerce' => false,
            ],
            'get_page' => [
                'label'                => 'get_page',
                'route'                => rtrim($restRoot, '/') . '/wp/v2/pages',
                'requires_woocommerce' => false,
            ],
            'get_products' => [
                'label'                => 'get_products',
                'route'                => rtrim($restRoot, '/') . '/wc/v3/products',
                'requires_woocommerce' => true,
            ],
        ];

        foreach ($definitions as $name => $definition) {
            if (empty($selectedTools[$name])) {
                continue;
            }

            if (! empty($definition['requires_woocommerce']) && empty($context['woocommerce_active'])) {
                continue;
            }

            $tools[] = [
                'name'                 => $name,
                'label'                => (string) $definition['label'],
                'route'                => (string) $definition['route'],
                'enabled'              => true,
                'requires_woocommerce' => (bool) $definition['requires_woocommerce'],
            ];
        }

        if ($tools === []) {
            return new WebMcpEmissionDecision(false, 'no_tools_enabled');
        }

        return new WebMcpEmissionDecision(true, 'eligible', $tools);
    }
}
