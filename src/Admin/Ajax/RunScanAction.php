<?php

declare(strict_types=1);

namespace AgentReadyWP\Admin\Ajax;

use AgentReadyWP\Application\Scan\ScanCache;
use AgentReadyWP\Application\Scan\ScanClient;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;

final class RunScanAction
{
    public function __construct(
        private readonly ScanClient $scanClient,
        private readonly ScanCache $scanCache,
        private readonly ScanSummaryMapper $scanSummaryMapper
    ) {
    }

    public function handle(): void
    {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(
                [
                    'message' => __('You are not allowed to run scans.', 'agent-ready-wp'),
                    'errors'  => [__('Insufficient permissions.', 'agent-ready-wp')],
                ],
                403
            );
        }

        check_ajax_referer('arwp_run_scan', 'nonce');

        $result = $this->scanClient->run(get_site_url());
        if (is_wp_error($result)) {
            $summary = $this->scanSummaryMapper->failureFallback(
                $this->scanCache->get(),
                $result->get_error_message()
            );

            wp_send_json_error(
                [
                    'message' => $result->get_error_message(),
                    'summary' => $summary,
                    'errors'  => $result->get_error_messages(),
                ],
                500
            );
        }

        $summary = $this->scanSummaryMapper->map($result);
        $this->scanCache->set($summary);

        wp_send_json_success(
            [
                'message' => __('Scan complete.', 'agent-ready-wp'),
                'summary' => $summary,
                'errors'  => [],
            ]
        );
    }
}
