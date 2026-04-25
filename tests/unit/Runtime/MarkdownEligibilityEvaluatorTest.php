<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownEligibilityEvaluator;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class MarkdownEligibilityEvaluatorTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['arwp_test_options'] = [];
    }

    public function test_evaluate_returns_eligible_for_valid_context(): void
    {
        $evaluator = new MarkdownEligibilityEvaluator();

        $result = $evaluator->evaluate([
            'feature_enabled' => true,
            'markdown_preferred' => true,
            'is_singular' => true,
            'is_supported_post_type' => true,
            'requester_can_view' => true,
        ]);

        $this->assertTrue($result['applies']);
        $this->assertSame('eligible', $result['reason']);
        $this->assertSame('markdown', $result['selected_representation']);
    }

    public function test_evaluate_maps_fallback_reasons(): void
    {
        $evaluator = new MarkdownEligibilityEvaluator();

        $disabled = $evaluator->evaluate([
            'feature_enabled' => false,
            'markdown_preferred' => true,
            'is_singular' => true,
            'is_supported_post_type' => true,
            'requester_can_view' => true,
        ]);
        $this->assertSame('feature_disabled', $disabled['reason']);

        $accept = $evaluator->evaluate([
            'feature_enabled' => true,
            'markdown_preferred' => false,
            'is_eligible_frontend_document_request' => true,
            'is_singular' => true,
            'is_supported_post_type' => true,
            'requester_can_view' => true,
        ]);
        $this->assertSame('accept_not_preferred', $accept['reason']);

        $excluded = $evaluator->evaluate([
            'feature_enabled' => true,
            'markdown_preferred' => true,
            'is_eligible_frontend_document_request' => false,
            'is_singular' => true,
            'is_supported_post_type' => true,
            'requester_can_view' => true,
        ]);
        $this->assertSame('unsupported_context', $excluded['reason']);

        $unsupported = $evaluator->evaluate([
            'feature_enabled' => true,
            'markdown_preferred' => true,
            'is_eligible_frontend_document_request' => true,
            'is_singular' => false,
            'is_supported_post_type' => false,
            'requester_can_view' => true,
        ]);
        $this->assertSame('unsupported_context', $unsupported['reason']);

        $denied = $evaluator->evaluate([
            'feature_enabled' => true,
            'markdown_preferred' => true,
            'is_eligible_frontend_document_request' => true,
            'is_singular' => true,
            'is_supported_post_type' => true,
            'requester_can_view' => false,
        ]);
        $this->assertSame('access_denied', $denied['reason']);
    }

    public function test_runtime_gateways_expose_markdown_and_compatibility_state(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $repository->update([
            'markdown' => [
                'enabled' => '1',
                'post_types' => ['post', 'book'],
                'include_woo' => '0',
            ],
        ]);

        $settingsGateway = new RuntimeFeatureSettingsGateway($repository);
        $compatGateway   = new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()));

        $markdown = $settingsGateway->getMarkdownSettings();
        $compat   = $compatGateway->get();

        $this->assertTrue($markdown['enabled']);
        $this->assertSame(['post', 'book'], $markdown['post_types']);
        $this->assertArrayHasKey('physical_robots_txt_present', $compat);
    }

    public function test_request_context_factory_marks_excluded_requests_ineligible(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $factory = new AgentReadyWP\Application\Runtime\Markdown\MarkdownRequestContextFactory(
            new RuntimeFeatureSettingsGateway($repository),
            new RuntimeCompatibilityGateway(new EnvironmentDetector(new WooCommerceDetector()))
        );

        arwp_tests_reset_request();
        arwp_tests_set_runtime_request('text/markdown', true, (object) [
            'ID' => 101,
            'post_type' => 'post',
        ]);
        arwp_tests_set_request_uri('/wp-login.php');

        $loginContext = $factory->create();
        $this->assertFalse($loginContext['is_eligible_frontend_document_request']);

        arwp_tests_reset_request();
        arwp_tests_set_runtime_request('text/markdown', true, (object) [
            'ID' => 102,
            'post_type' => 'post',
        ]);
        arwp_tests_set_system_request_flags(true, false);

        $ajaxContext = $factory->create();
        $this->assertFalse($ajaxContext['is_eligible_frontend_document_request']);
    }
}
