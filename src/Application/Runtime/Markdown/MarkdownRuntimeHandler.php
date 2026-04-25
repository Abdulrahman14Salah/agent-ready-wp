<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class MarkdownRuntimeHandler
{
    public function __construct(
        private readonly MarkdownRequestContextFactory $contextFactory,
        private readonly MarkdownAcceptPreferenceParser $acceptParser,
        private readonly MarkdownEligibilityEvaluator $eligibilityEvaluator,
        private readonly MarkdownRenderer $renderer,
        private readonly TokenEstimator $tokenEstimator,
        private readonly MarkdownResponseWriter $responseWriter,
        private readonly ContentVisibilityGuard $visibilityGuard
    ) {
    }

    /**
     * @return array{decision: array<string,mixed>, response: array<string,mixed>|null}
     */
    public function handleCurrentRequest(bool $terminate = true): array
    {
        $context = $this->contextFactory->create();

        $context['markdown_preferred'] = $this->acceptParser->prefersMarkdown((string) ($context['accept_header'] ?? ''));
        $context['requester_can_view'] = $this->visibilityGuard->canView($context['query_object'] ?? null);

        $decision = $this->eligibilityEvaluator->evaluate($context);
        if (! $decision['applies']) {
            return [
                'decision' => $decision,
                'response' => null,
            ];
        }

        $post = $context['query_object'] ?? null;
        if (! is_object($post)) {
            return [
                'decision' => [
                    'applies' => false,
                    'reason' => 'unsupported_context',
                    'selected_representation' => 'default',
                ],
                'response' => null,
            ];
        }

        $markdown = $this->renderer->render($post);
        $tokens   = $this->tokenEstimator->estimate($markdown);
        $response = $this->responseWriter->write($markdown, $tokens, $terminate);

        return [
            'decision' => $decision,
            'response' => $response,
        ];
    }
}
