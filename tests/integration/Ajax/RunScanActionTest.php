<?php

declare(strict_types=1);

use AgentReadyWP\Admin\Ajax\RunScanAction;
use AgentReadyWP\Application\Scan\ScanCache;
use AgentReadyWP\Application\Scan\ScanClient;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use PHPUnit\Framework\TestCase;

final class RunScanActionTest extends TestCase
{
    public function test_handle_returns_success_payload(): void
    {
        $action = new RunScanAction(new ScanClient(), new ScanCache(), new ScanSummaryMapper());

        try {
            $action->handle();
            $this->fail('Expected JSON response exception.');
        } catch (RuntimeException $exception) {
            $payload = json_decode($exception->getMessage(), true);
            $this->assertTrue($payload['success']);
            $this->assertArrayHasKey('summary', $payload['data']);
        }
    }
}
