<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ProtectedResource;

final class ProtectedResourceResolutionDecision
{
    public function __construct(
        private readonly bool $applies,
        private readonly string $reason
    ) {
    }

    /**
     * @return array{applies: bool, reason: string}
     */
    public function toArray(): array
    {
        return [
            'applies' => $this->applies,
            'reason' => $this->reason,
        ];
    }
}
