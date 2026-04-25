<?php

declare(strict_types=1);

use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use PHPUnit\Framework\TestCase;

final class CompatibilityWarningsTest extends TestCase
{
    public function test_detector_returns_warning_list(): void
    {
        $warnings = (new EnvironmentDetector(new WooCommerceDetector()))->detect()['warnings'];

        $this->assertIsArray($warnings);
    }
}
