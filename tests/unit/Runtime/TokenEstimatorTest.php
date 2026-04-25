<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\Markdown\TokenEstimator;
use PHPUnit\Framework\TestCase;

final class TokenEstimatorTest extends TestCase
{
    public function test_estimate_returns_expected_approximate_token_count(): void
    {
        $estimator = new TokenEstimator();
        $tokens    = $estimator->estimate('one two three four five');

        $this->assertSame(7, $tokens);
    }

    public function test_estimate_returns_zero_for_empty_markdown(): void
    {
        $estimator = new TokenEstimator();

        $this->assertSame(0, $estimator->estimate('   '));
    }
}
