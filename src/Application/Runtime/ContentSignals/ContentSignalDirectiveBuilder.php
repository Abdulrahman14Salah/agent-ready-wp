<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ContentSignals;

final class ContentSignalDirectiveBuilder
{
    /**
     * @param array<string,mixed> $settings
     * @return array{enabled: bool, pairs: array<string,string>, directive_line: string|null}
     */
    public function buildState(array $settings): array
    {
        $enabled = ! empty($settings['enabled']);
        if (! $enabled) {
            return [
                'enabled' => false,
                'pairs' => [],
                'directive_line' => null,
            ];
        }

        $pairs = $this->buildPairs($settings);
        if ($pairs === []) {
            return [
                'enabled' => true,
                'pairs' => [],
                'directive_line' => null,
            ];
        }

        $parts = [];
        foreach ($pairs as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return [
            'enabled' => true,
            'pairs' => $pairs,
            'directive_line' => 'Content-Signal: ' . implode(', ', $parts),
        ];
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<string,string>
     */
    private function buildPairs(array $settings): array
    {
        $pairs = [];

        $map = [
            'ai_train' => 'ai-train',
            'search' => 'search',
            'ai_input' => 'ai-input',
        ];

        foreach ($map as $settingKey => $outputKey) {
            $value = (string) ($settings[$settingKey] ?? '');
            if (! in_array($value, ['yes', 'no'], true)) {
                continue;
            }

            $pairs[$outputKey] = $value;
        }

        return $pairs;
    }
}
