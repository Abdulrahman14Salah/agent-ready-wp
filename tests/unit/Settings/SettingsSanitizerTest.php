<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Settings\Defaults;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class SettingsSanitizerTest extends TestCase
{
    public function test_sanitizer_coerces_values_and_removes_invalid_entries(): void
    {
        $sanitizer = new SettingsSanitizer(
            new EnvironmentDetector(new WooCommerceDetector())
        );

        $result = $sanitizer->sanitize(
            [
                'enabled' => '1',
                'markdown' => [
                    'enabled' => '1',
                    'post_types' => ['post', 'bad<script>'],
                    'include_woo' => '1',
                ],
                'content_signals' => [
                    'enabled' => '1',
                    'ai_train' => 'maybe',
                    'search' => 'yes',
                    'ai_input' => 'no',
                ],
                'api_catalog' => [
                    'enabled' => '1',
                    'include_wp_rest' => '1',
                    'custom_entries' => [
                        [
                            'name' => 'Docs',
                            'anchor' => 'https://example.com/a',
                            'service_desc' => 'https://example.com/b',
                        ],
                    ],
                ],
                'webmcp' => [
                    'enabled' => '1',
                    'tools' => [
                        'search' => '1',
                        'get_posts' => '1',
                        'get_page' => '1',
                        'get_products' => '1',
                    ],
                ],
            ],
            Defaults::all()
        );

        $this->assertSame(['post'], $result['markdown']['post_types']);
        $this->assertSame('', $result['content_signals']['ai_train']);
        $this->assertCount(1, $result['api_catalog']['custom_entries']);
        $this->assertFalse($result['webmcp']['tools']['get_products']);
    }
}
