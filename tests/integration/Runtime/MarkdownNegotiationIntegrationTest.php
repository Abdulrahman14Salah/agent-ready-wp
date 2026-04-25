<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\Markdown\ContentVisibilityGuard;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownAcceptPreferenceParser;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownEligibilityEvaluator;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRenderer;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRequestContextFactory;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownResponseWriter;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRuntimeHandler;
use AgentReadyWP\Application\Runtime\Markdown\TokenEstimator;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\Defaults;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class MarkdownNegotiationIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];
        arwp_tests_set_current_user_can(true);
        arwp_tests_reset_request();
    }

    public function test_eligible_request_returns_markdown_contract_with_headers(): void
    {
        $post = (object) [
            'ID' => 11,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'Sample Post',
            'post_content' => '<p>Hello world.</p>',
        ];

        arwp_tests_set_runtime_request('text/markdown, text/html;q=0.9', true, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertContains('Content-Type: text/markdown; charset=utf-8', $collector->headers);
        $this->assertContains('Vary: Accept', $collector->headers);
        $this->assertStringContainsString('x-markdown-tokens:', implode(' | ', $collector->headers));
        $this->assertStringContainsString('# Sample Post', $collector->body);
    }

    public function test_html_preferred_accept_header_falls_back_to_default_handling(): void
    {
        $post = (object) [
            'ID' => 12,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'Sample Post',
            'post_content' => '<p>Hello world.</p>',
        ];

        arwp_tests_set_runtime_request('text/html, text/markdown;q=0.5', true, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('accept_not_preferred', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    public function test_tied_highest_accept_prefers_markdown(): void
    {
        $post = (object) [
            'ID' => 14,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'Sample Post',
            'post_content' => '<p>Hello world.</p>',
        ];

        arwp_tests_set_runtime_request('text/html;q=0.8, text/markdown;q=0.8', true, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertContains('Vary: Accept', $collector->headers);
        $this->assertStringContainsString('Content-Type: text/markdown;', implode(' | ', $collector->headers));
    }

    public function test_woocommerce_product_markdown_includes_product_details_when_supported(): void
    {
        arwp_tests_set_woocommerce_active(true);

        $settings = Defaults::all();
        $settings['markdown']['enabled'] = true;
        $settings['markdown']['include_woo'] = true;
        $GLOBALS['arwp_test_options']['agent_ready_wp_settings'] = $settings;

        $post = (object) [
            'ID' => 13,
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_title' => 'Product Name',
            'post_content' => '<p>Product details.</p>',
            'price' => '10.00',
            'sku' => 'SKU-1',
            'stock_status' => 'instock',
        ];

        arwp_tests_set_runtime_request('text/markdown', true, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertStringContainsString('- Price: 10.00', $collector->body);
        $this->assertStringContainsString('- SKU: SKU-1', $collector->body);
    }

    public function test_arabic_content_is_preserved_in_markdown_output(): void
    {
        $post = (object) [
            'ID' => 15,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'عنوان عربي',
            'post_content' => '<p>هذا نص عربي.</p><p><a href="https://example.com/ar">اقرأ المزيد</a></p>',
        ];

        arwp_tests_set_runtime_request('text/markdown', true, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertTrue($result['decision']['applies']);
        $this->assertStringContainsString('# عنوان عربي', $collector->body);
        $this->assertStringContainsString('هذا نص عربي.', $collector->body);
        $this->assertStringContainsString('[اقرأ المزيد](https://example.com/ar)', $collector->body);
    }

    /**
     * @return array{0: MarkdownRuntimeHandler, 1: object}
     */
    private function createHandler(): array
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $settingsGateway = new RuntimeFeatureSettingsGateway($repository);
        $compatGateway   = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));

        $collector = (object) [
            'headers' => [],
            'body' => '',
            'terminated' => false,
        ];

        $writer = new MarkdownResponseWriter(
            static function (string $header) use ($collector): void {
                $collector->headers[] = $header;
            },
            static function (string $body) use ($collector): void {
                $collector->body .= $body;
            },
            static function () use ($collector): void {
                $collector->terminated = true;
            }
        );

        $handler = new MarkdownRuntimeHandler(
            new MarkdownRequestContextFactory($settingsGateway, $compatGateway),
            new MarkdownAcceptPreferenceParser(),
            new MarkdownEligibilityEvaluator(),
            new MarkdownRenderer(),
            new TokenEstimator(),
            $writer,
            new ContentVisibilityGuard()
        );

        return [$handler, $collector];
    }
}
