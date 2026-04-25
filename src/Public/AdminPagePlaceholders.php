<?php

declare(strict_types=1);

namespace AgentReadyWP\Public;

final class AdminPagePlaceholders
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        return [
            [
                'continuity_key' => 'shared_save_flow',
                'label'          => __('Shared Save Flow', 'agent-ready-wp'),
                'status_text'    => __('Phase 1 and Phase 2 settings are saved together with one page-level action.', 'agent-ready-wp'),
                'interactive'    => true,
            ],
            [
                'continuity_key' => 'draft_preview',
                'label'          => __('Draft Preview', 'agent-ready-wp'),
                'status_text'    => __('Phase 2 previews show draft metadata and only become published after save succeeds.', 'agent-ready-wp'),
                'interactive'    => true,
            ],
        ];
    }
}
