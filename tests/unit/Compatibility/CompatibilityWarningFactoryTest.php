<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class CompatibilityWarningFactoryTest extends TestCase
{
    public function test_detector_creates_warning_items(): void
    {
        $detector = new EnvironmentDetector(new WooCommerceDetector());

        $warnings = $detector->detect()['warnings'];

        $this->assertIsArray($warnings);
        $this->assertNotEmpty($warnings);
    }
}
