<?php

declare(strict_types=1);

use AgentReadyWP\Public\AdminPagePlaceholders;
use PHPUnit\Framework\TestCase;

final class PhasePlaceholderRenderTest extends TestCase
{
    public function test_phase_two_continuity_messages_replace_coming_soon_placeholders(): void
    {
        $messages = (new AdminPagePlaceholders())->all();

        $this->assertCount(2, $messages);
        $this->assertTrue($messages[0]['interactive']);
        $this->assertStringContainsString('saved together', $messages[0]['status_text']);
    }
}
