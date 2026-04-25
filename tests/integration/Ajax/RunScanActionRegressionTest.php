<?php

declare(strict_types=1);

use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use PHPUnit\Framework\TestCase;

final class RunScanActionRegressionTest extends TestCase
{
    public function test_failure_fallback_preserves_message_and_status(): void
    {
        $mapper = new ScanSummaryMapper();

        $fallback = $mapper->failureFallback(null, 'Scan failed.');

        $this->assertSame('refresh_failed', $fallback['status']);
        $this->assertSame('Scan failed.', $fallback['message']);
    }
}
