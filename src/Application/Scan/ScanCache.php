<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Scan;

final class ScanCache
{
    public function get(): ?array
    {
        $cached = get_transient('agent_ready_wp_scan_cache');

        return is_array($cached) ? $cached : null;
    }

    public function set(array $summary): void
    {
        set_transient('agent_ready_wp_scan_cache', $summary, 6 * HOUR_IN_SECONDS);
    }

    public function clear(): void
    {
        delete_transient('agent_ready_wp_scan_cache');
    }
}
