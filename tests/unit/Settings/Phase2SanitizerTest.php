<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Settings\Defaults;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class Phase2SanitizerTest extends TestCase
{
    public function test_sanitize_and_validate_accepts_valid_phase_two_payload(): void
    {
        $sanitizer = new SettingsSanitizer(
            new EnvironmentDetector(new WooCommerceDetector())
        );

        $validated = $sanitizer->sanitizeAndValidate(
            arwp_tests_phase2_valid_payload(),
            Defaults::all()
        );

        $settings = $validated['settings'];

        $this->assertSame([], $validated['errors']);
        $this->assertTrue($settings['protected_apis']['enabled']);
        $this->assertSame('https://example.com/mcp', $settings['mcp_server_card']['transport']);
        $this->assertCount(2, $settings['protected_resource']['authorization_servers']);
    }

    public function test_sanitize_and_validate_reports_targeted_errors_for_invalid_phase_two_values(): void
    {
        $sanitizer = new SettingsSanitizer(
            new EnvironmentDetector(new WooCommerceDetector())
        );

        $validated = $sanitizer->sanitizeAndValidate(
            arwp_tests_phase2_invalid_payload(),
            Defaults::all()
        );

        $errors = $validated['errors'];

        $this->assertArrayHasKey('mcp_server_card.name', $errors);
        $this->assertArrayHasKey('mcp_server_card.transport', $errors);
        $this->assertArrayHasKey('oauth.issuer', $errors);
        $this->assertArrayHasKey('protected_resource.authorization_servers', $errors);
        $this->assertNotEmpty($validated['messages']);
    }
}
