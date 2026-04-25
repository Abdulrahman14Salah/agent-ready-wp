<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\Markdown\MarkdownAcceptPreferenceParser;
use PHPUnit\Framework\TestCase;

final class MarkdownAcceptPreferenceParserTest extends TestCase
{
    public function test_prefers_markdown_matches_fixture_cases(): void
    {
        $json  = file_get_contents(__DIR__ . '/../../fixtures/markdown-accept-cases.json');
        $cases = json_decode((string) $json, true);

        $parser = new MarkdownAcceptPreferenceParser();

        foreach ((array) $cases as $case) {
            $this->assertSame(
                (bool) $case['expects_markdown'],
                $parser->prefersMarkdown((string) $case['accept']),
                (string) $case['name']
            );
        }
    }

    public function test_prefers_markdown_returns_false_for_empty_header(): void
    {
        $parser = new MarkdownAcceptPreferenceParser();

        $this->assertFalse($parser->prefersMarkdown(''));
    }
}
