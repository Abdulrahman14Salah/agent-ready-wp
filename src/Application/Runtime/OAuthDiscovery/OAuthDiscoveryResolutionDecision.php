<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\OAuthDiscovery;

final class OAuthDiscoveryResolutionDecision
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
