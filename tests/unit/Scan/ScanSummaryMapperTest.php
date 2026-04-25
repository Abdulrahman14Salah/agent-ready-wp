<?php

declare(strict_types=1);

use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use PHPUnit\Framework\TestCase;

final class ScanSummaryMapperTest extends TestCase
{
    public function test_mapper_groups_checks_and_maps_panels(): void
    {
        $mapper = new ScanSummaryMapper();

        $summary = $mapper->map([
            'url' => 'https://example.com',
            'score' => 10,
            'level' => 1,
            'level_name' => 'Basic',
            'checks' => [
                'markdown' => [
                    'label' => 'Markdown Negotiation',
                    'status' => 'fail',
                    'group' => 'Content',
                ],
                'catalog' => [
                    'label' => 'API Catalog',
                    'status' => 'pass',
                    'group' => 'Discoverability',
                ],
            ],
            'scanned_at' => '2026-04-23T17:41:26Z',
        ]);

        $this->assertCount(2, $summary['checks']);
        $this->assertSame('markdown', $summary['checks'][0]['mapped_panel']);
        $this->assertCount(2, $summary['groups']);
    }

    public function test_mapper_supports_grouped_scan_api_payload(): void
    {
        $mapper = new ScanSummaryMapper();

        $summary = $mapper->map([
            'url' => 'https://example.com',
            'scannedAt' => '2026-04-25T15:50:09.971Z',
            'level' => 3,
            'levelName' => 'Agent-Readable',
            'checks' => [
                'discoverability' => [
                    'robotsTxt' => ['status' => 'pass'],
                    'sitemap' => ['status' => 'pass'],
                    'linkHeaders' => ['status' => 'pass'],
                ],
                'contentAccessibility' => [
                    'markdownNegotiation' => ['status' => 'pass'],
                ],
                'botAccessControl' => [
                    'robotsTxtAiRules' => ['status' => 'pass'],
                    'contentSignals' => ['status' => 'pass'],
                    'webBotAuth' => ['status' => 'neutral'],
                ],
                'discovery' => [
                    'apiCatalog' => ['status' => 'fail'],
                    'oauthDiscovery' => ['status' => 'fail'],
                    'oauthProtectedResource' => ['status' => 'fail'],
                    'mcpServerCard' => ['status' => 'fail'],
                    'a2aAgentCard' => ['status' => 'fail'],
                    'agentSkills' => ['status' => 'fail'],
                ],
                'commerce' => [
                    'x402' => ['status' => 'neutral'],
                ],
            ],
        ]);

        $this->assertSame(50, $summary['score']);
        $this->assertSame('Agent-Readable', $summary['level_name']);
        $this->assertSame('2026-04-25T15:50:09.971Z', $summary['scanned_at']);
        $this->assertCount(14, $summary['checks']);

        $groups = [];
        foreach ($summary['groups'] as $group) {
            $groups[$group['label']] = $group;
        }

        $this->assertSame([3, 3, 'pass'], [
            $groups['Discoverability']['passed'],
            $groups['Discoverability']['total'],
            $groups['Discoverability']['state'],
        ]);
        $this->assertSame([0, 6, 'fail'], [
            $groups['API, Auth, MCP & Skill Discovery']['passed'],
            $groups['API, Auth, MCP & Skill Discovery']['total'],
            $groups['API, Auth, MCP & Skill Discovery']['state'],
        ]);
        $this->assertSame([0, 0, 'neutral'], [
            $groups['Commerce']['passed'],
            $groups['Commerce']['total'],
            $groups['Commerce']['state'],
        ]);
    }
}
