<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ContentSignals;

final class ContentSignalLineNormalizer
{
    public function normalize(string $robotsOutput, ?string $directiveLine): string
    {
        $withoutDirectives = preg_replace('/^\s*Content-Signal:.*$(\R)?/mi', '', $robotsOutput);
        $clean             = rtrim((string) ($withoutDirectives ?? $robotsOutput), "\r\n");

        if ($directiveLine === null || $directiveLine === '') {
            return $clean === '' ? '' : $clean . "\n";
        }

        if ($clean === '') {
            return $directiveLine . "\n";
        }

        return $clean . "\n" . $directiveLine . "\n";
    }
}
