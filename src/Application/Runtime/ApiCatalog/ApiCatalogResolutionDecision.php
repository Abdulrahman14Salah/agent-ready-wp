<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ApiCatalog;

final class ApiCatalogResolutionDecision
{
    /**
     * @param array<int,array<string,string>> $entries
     */
    public function __construct(
        private readonly bool $applies,
        private readonly string $reason,
        private readonly array $entries = []
    ) {
    }

    /**
     * @return array{applies: bool, reason: string, entries: array<int,array<string,string>>}
     */
    public function toArray(): array
    {
        return [
            'applies' => $this->applies,
            'reason'  => $this->reason,
            'entries' => $this->entries,
        ];
    }
}
