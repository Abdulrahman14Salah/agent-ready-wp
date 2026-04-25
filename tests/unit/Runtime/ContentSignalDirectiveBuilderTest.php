<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalDirectiveBuilder;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalLineNormalizer;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class ContentSignalDirectiveBuilderTest extends TestCase
{
    public function test_builder_creates_canonical_directive_for_configured_values(): void
    {
        $builder = new ContentSignalDirectiveBuilder();

        $state = $builder->buildState([
            'enabled' => true,
            'ai_train' => 'no',
            'search' => 'yes',
            'ai_input' => '',
        ]);

        $this->assertTrue($state['enabled']);
        $this->assertSame('Content-Signal: ai-train=no, search=yes', $state['directive_line']);
    }

    public function test_builder_omits_directive_when_disabled_or_unset(): void
    {
        $builder = new ContentSignalDirectiveBuilder();

        $disabled = $builder->buildState([
            'enabled' => false,
            'ai_train' => 'no',
            'search' => 'yes',
            'ai_input' => 'no',
        ]);
        $this->assertNull($disabled['directive_line']);

        $unset = $builder->buildState([
            'enabled' => true,
            'ai_train' => '',
            'search' => '',
            'ai_input' => '',
        ]);
        $this->assertNull($unset['directive_line']);
    }

    public function test_line_normalizer_replaces_existing_content_signal_lines(): void
    {
        $normalizer = new ContentSignalLineNormalizer();

        $output = "User-agent: *\nDisallow: /wp-admin/\nContent-Signal: old=yes\n";

        $normalized = $normalizer->normalize($output, 'Content-Signal: ai-train=no, search=yes');

        $this->assertSame(1, substr_count($normalized, 'Content-Signal:'));
        $this->assertStringContainsString('Content-Signal: ai-train=no, search=yes', $normalized);
    }

    public function test_runtime_feature_gateway_returns_settings_for_directive_state(): void
    {
        $repository = new SettingsRepository(
            new SettingsSanitizer(new EnvironmentDetector(new WooCommerceDetector()))
        );

        $repository->update([
            'content_signals' => [
                'enabled' => '1',
                'ai_train' => 'no',
                'search' => 'yes',
                'ai_input' => '',
            ],
        ]);

        $gateway = new RuntimeFeatureSettingsGateway($repository);
        $state   = $gateway->getContentSignalsSettings();

        $this->assertTrue($state['enabled']);
        $this->assertSame('no', $state['ai_train']);
        $this->assertSame('yes', $state['search']);
        $this->assertSame('', $state['ai_input']);
    }
}
