<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\WebMcp;

final class WebMcpEmissionDecision
{
    /**
     * @param array<int,array<string,mixed>> $tools
     */
    public function __construct(
        private readonly bool $applies,
        private readonly string $reason,
        private readonly array $tools = []
    ) {
    }

    /**
     * @return array{applies: bool, reason: string, tools: array<int,array<string,mixed>>}
     */
    public function toArray(): array
    {
        return [
            'applies' => $this->applies,
            'reason'  => $this->reason,
            'tools'   => $this->tools,
        ];
    }
}
