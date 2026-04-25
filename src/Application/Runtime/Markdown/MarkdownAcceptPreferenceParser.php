<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class MarkdownAcceptPreferenceParser
{
    public function prefersMarkdown(string $acceptHeader): bool
    {
        $acceptHeader = trim($acceptHeader);
        if ($acceptHeader === '') {
            return false;
        }

        $parts = array_filter(array_map('trim', explode(',', $acceptHeader)), static fn (string $part): bool => $part !== '');
        if ($parts === []) {
            return false;
        }

        $maxQ          = -1.0;
        $markdownMaxQ  = -1.0;

        foreach ($parts as $part) {
            $segments  = array_map('trim', explode(';', $part));
            $mediaType = strtolower((string) ($segments[0] ?? ''));
            if ($mediaType === '') {
                continue;
            }

            $q = 1.0;
            foreach (array_slice($segments, 1) as $parameter) {
                if (! str_starts_with(strtolower($parameter), 'q=')) {
                    continue;
                }
                $qValue = (float) substr($parameter, 2);
                if ($qValue >= 0.0 && $qValue <= 1.0) {
                    $q = $qValue;
                }
            }

            if ($q > $maxQ) {
                $maxQ = $q;
            }

            if ($mediaType === 'text/markdown' && $q > $markdownMaxQ) {
                $markdownMaxQ = $q;
            }
        }

        if ($markdownMaxQ < 0) {
            return false;
        }

        return $markdownMaxQ >= ($maxQ - 0.00001);
    }
}
