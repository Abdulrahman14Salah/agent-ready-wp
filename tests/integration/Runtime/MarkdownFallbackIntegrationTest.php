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

final class MarkdownFallbackIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];
        arwp_tests_set_current_user_can(true);
        arwp_tests_reset_request();
    }

    public function test_non_singular_markdown_request_falls_back_without_markdown_contract(): void
    {
        $post = (object) [
            'ID' => 21,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'Sample Post',
            'post_content' => '<p>Hello world.</p>',
        ];

        arwp_tests_set_runtime_request('text/markdown', false, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('unsupported_context', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    public function test_private_content_does_not_bypass_access_controls(): void
    {
        arwp_tests_set_capability('read_private_posts', false);

        $post = (object) [
            'ID' => 22,
            'post_type' => 'post',
            'post_status' => 'private',
            'post_title' => 'Private Post',
            'post_content' => '<p>Secret</p>',
        ];

        arwp_tests_set_runtime_request('text/markdown', true, $post);

        [$handler, $collector] = $this->createHandler();
        $result = $handler->handleCurrentRequest(false);

        $this->assertFalse($result['decision']['applies']);
        $this->assertSame('access_denied', $result['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
    }

    public function test_excluded_request_classes_preserve_default_behavior(): void
    {
        $post = (object) [
            'ID' => 23,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'Sample Post',
            'post_content' => '<p>Hello world.</p>',
        ];

        arwp_tests_set_runtime_request('text/markdown', true, $post);
        arwp_tests_set_frontend_context(false, true, false);

        [$handler, $collector] = $this->createHandler();
        $adminResult = $handler->handleCurrentRequest(false);

        $this->assertFalse($adminResult['decision']['applies']);
        $this->assertSame('unsupported_context', $adminResult['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);

        arwp_tests_reset_request();
        $GLOBALS['arwp_test_options'] = [
            'agent_ready_wp_settings' => Defaults::all(),
        ];
        arwp_tests_set_current_user_can(true);
        arwp_tests_set_runtime_request('text/markdown', true, $post);
        arwp_tests_set_request_uri('/wp-content/themes/example/style.css');

        [$handler, $collector] = $this->createHandler();
        $assetResult = $handler->handleCurrentRequest(false);

        $this->assertFalse($assetResult['decision']['applies']);
        $this->assertSame('unsupported_context', $assetResult['decision']['reason']);
        $this->assertSame([], $collector->headers);
        $this->assertSame('', $collector->body);
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
        ];

        $writer = new MarkdownResponseWriter(
            static function (string $header) use ($collector): void {
                $collector->headers[] = $header;
            },
            static function (string $body) use ($collector): void {
                $collector->body .= $body;
            },
            static function (): void {
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
