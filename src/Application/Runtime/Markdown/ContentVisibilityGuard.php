<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class ContentVisibilityGuard
{
    public function canView(?object $post): bool
    {
        if (! is_object($post)) {
            return false;
        }

        if (function_exists('post_password_required') && post_password_required($post)) {
            return false;
        }

        $status = isset($post->post_status) ? (string) $post->post_status : 'publish';

        if ($status === 'private') {
            return function_exists('current_user_can')
                ? current_user_can('read_private_posts')
                : false;
        }

        if (in_array($status, ['draft', 'pending', 'future'], true)) {
            return function_exists('current_user_can')
                ? current_user_can('edit_posts')
                : false;
        }

        return true;
    }
}
