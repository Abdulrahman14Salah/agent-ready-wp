<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Scan;

final class ScanSummaryMapper
{
    public function empty(): array
    {
        return [
            'url'        => get_site_url(),
            'score'      => 0,
            'level'      => 0,
            'level_name' => __('No scan yet', 'agent-ready-wp'),
            'checks'     => [],
            'groups'     => [],
            'scanned_at' => '',
            'status'     => 'empty',
            'message'    => __('No scan yet. Click Run Scan.', 'agent-ready-wp'),
        ];
    }

    public function map(array $payload): array
    {
        $rawChecks = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];
        $checks    = $this->flattenChecks($rawChecks);

        return [
            'url'        => (string) ($payload['url'] ?? get_site_url()),
            'score'      => (int) ($payload['score'] ?? $this->calculateScore($checks)),
            'level'      => (int) ($payload['level'] ?? 0),
            'level_name' => (string) ($payload['level_name'] ?? $payload['levelName'] ?? ''),
            'checks'     => $this->mapChecks($checks),
            'groups'     => $this->groupChecks($checks),
            'scanned_at' => (string) ($payload['scanned_at'] ?? $payload['scannedAt'] ?? current_time('mysql')),
            'status'     => 'fresh',
            'message'    => __('Scan complete.', 'agent-ready-wp'),
        ];
    }

    public function failureFallback(?array $cached, string $message): array
    {
        if (is_array($cached)) {
            $cached['status']  = 'refresh_failed';
            $cached['message'] = $message;
            return $cached;
        }

        $empty = $this->empty();
        $empty['status']  = 'refresh_failed';
        $empty['message'] = $message;

        return $empty;
    }

    /**
     * @param array<int|string,mixed> $checks
     * @return array<int,array<string,mixed>>
     */
    private function mapChecks(array $checks): array
    {
        $results = [];

        foreach ($checks as $key => $check) {
            if (! is_array($check)) {
                continue;
            }

            $label  = (string) ($check['label'] ?? $check['name'] ?? $key);
            $state  = $this->normalizeState((string) ($check['state'] ?? $check['status'] ?? 'fail'));
            $mapped = $this->mapPanel((string) $key, $label);

            $results[] = [
                'key'          => (string) $key,
                'label'        => $label,
                'state'        => $state,
                'mapped_panel' => $mapped,
            ];
        }

        return $results;
    }

    /**
     * @param array<int|string,mixed> $checks
     * @return array<int,array<string,mixed>>
     */
    private function groupChecks(array $checks): array
    {
        $groups = [];

        foreach ($checks as $key => $check) {
            if (! is_array($check)) {
                continue;
            }

            $group = (string) ($check['group'] ?? $check['category'] ?? __('General', 'agent-ready-wp'));
            if (! isset($groups[$group])) {
                $groups[$group] = [
                    'label'               => $group,
                    'passed'              => 0,
                    'total'               => 0,
                    'state'               => 'neutral',
                    'primary_linked_panel' => null,
                ];
            }

            $state = $this->normalizeState((string) ($check['state'] ?? $check['status'] ?? 'fail'));
            if ($state !== 'neutral') {
                $groups[$group]['total']++;
            }

            if ($state === 'pass') {
                $groups[$group]['passed']++;
            }

            if ($groups[$group]['primary_linked_panel'] === null) {
                $groups[$group]['primary_linked_panel'] = $this->mapPanel((string) $key, (string) ($check['label'] ?? $key));
            }
        }

        foreach ($groups as &$group) {
            if ($group['total'] === 0) {
                $group['state'] = 'neutral';
            } elseif ($group['passed'] === $group['total']) {
                $group['state'] = 'pass';
            } elseif ($group['passed'] > 0) {
                $group['state'] = 'partial';
            } else {
                $group['state'] = 'fail';
            }
        }
        unset($group);

        return array_values($groups);
    }

    private function normalizeState(string $state): string
    {
        $state = strtolower($state);

        return in_array($state, ['pass', 'partial', 'fail', 'neutral'], true) ? $state : 'fail';
    }

    /**
     * The scan API now returns checks grouped by readiness area. Older fixture
     * payloads returned a flat checks map, so support both shapes here.
     *
     * @param array<int|string,mixed> $checks
     * @return array<string,array<string,mixed>>
     */
    private function flattenChecks(array $checks): array
    {
        $flat = [];

        foreach ($checks as $groupKey => $groupOrCheck) {
            if (! is_array($groupOrCheck)) {
                continue;
            }

            if ($this->isCheck($groupOrCheck)) {
                $flat[(string) $groupKey] = $groupOrCheck;
                continue;
            }

            $groupLabel = $this->groupLabel((string) $groupKey);
            foreach ($groupOrCheck as $checkKey => $check) {
                if (! is_array($check) || ! $this->isCheck($check)) {
                    continue;
                }

                $flat[(string) $groupKey . '.' . (string) $checkKey] = array_merge(
                    [
                        'label' => $this->humanizeKey((string) $checkKey),
                        'group' => $groupLabel,
                    ],
                    $check
                );
            }
        }

        return $flat;
    }

    /**
     * @param array<string,mixed> $check
     */
    private function isCheck(array $check): bool
    {
        return array_key_exists('status', $check) || array_key_exists('state', $check);
    }

    /**
     * @param array<string,array<string,mixed>> $checks
     */
    private function calculateScore(array $checks): int
    {
        $earned = 0.0;
        $total  = 0;

        foreach ($checks as $check) {
            $state = $this->normalizeState((string) ($check['state'] ?? $check['status'] ?? 'fail'));
            if ($state === 'neutral') {
                continue;
            }

            $total++;
            if ($state === 'pass') {
                $earned++;
            } elseif ($state === 'partial') {
                $earned += 0.5;
            }
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($earned / $total) * 100);
    }

    private function groupLabel(string $key): string
    {
        return match ($key) {
            'discoverability' => __('Discoverability', 'agent-ready-wp'),
            'contentAccessibility' => __('Content', 'agent-ready-wp'),
            'botAccessControl' => __('Bot Access Control', 'agent-ready-wp'),
            'discovery' => __('API, Auth, MCP & Skill Discovery', 'agent-ready-wp'),
            'commerce' => __('Commerce', 'agent-ready-wp'),
            default => $this->humanizeKey($key),
        };
    }

    private function humanizeKey(string $key): string
    {
        $key = (string) preg_replace('/(?<!^)[A-Z]/', ' $0', $key);
        $key = str_replace(['_', '-', '.'], ' ', $key);

        return ucwords(trim($key));
    }

    private function mapPanel(string $key, string $label): ?string
    {
        $haystack = strtolower($key . ' ' . $label);
        if (str_contains($haystack, 'markdown')) {
            return 'markdown';
        }
        if (str_contains($haystack, 'content-signal') || str_contains($haystack, 'robots')) {
            return 'content_signals';
        }
        if (str_contains($haystack, 'catalog') || str_contains($haystack, 'api')) {
            return 'api_catalog';
        }
        if (str_contains($haystack, 'webmcp') || str_contains($haystack, 'mcp')) {
            return 'webmcp';
        }

        return null;
    }
}
