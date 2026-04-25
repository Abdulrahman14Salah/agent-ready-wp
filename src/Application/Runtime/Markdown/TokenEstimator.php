<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class TokenEstimator
{
    public function estimate(string $markdown): int
    {
        $clean = trim($markdown);
        if ($clean === '') {
            return 0;
        }

        $words = preg_split('/\s+/', $clean) ?: [];
        $count = count(array_filter($words, static fn (string $word): bool => $word !== ''));

        return (int) ceil($count * 1.33);
    }
}
