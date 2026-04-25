<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ContentSignals;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class ContentSignalsRobotsFilter
{
    public function __construct(
        private readonly RuntimeFeatureSettingsGateway $settingsGateway,
        private readonly RuntimeCompatibilityGateway $compatibilityGateway,
        private readonly ContentSignalDirectiveBuilder $directiveBuilder,
        private readonly ContentSignalLineNormalizer $lineNormalizer
    ) {
    }

    public function filterRobots(string $output, bool $public = true): string
    {
        $compatibility = $this->compatibilityGateway->get();

        if (! empty($compatibility['physical_robots_txt_present'])) {
            return $output;
        }

        $state = $this->directiveBuilder->buildState($this->settingsGateway->getContentSignalsSettings());
        if (! $state['enabled']) {
            return $output;
        }

        if ($state['directive_line'] === null) {
            return $output;
        }

        return $this->lineNormalizer->normalize($output, $state['directive_line']);
    }
}
