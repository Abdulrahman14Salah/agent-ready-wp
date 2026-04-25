<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\McpServerCard;

final class McpServerCardRuntimeHandler
{
    public function __construct(
        private readonly McpServerCardRequestContextFactory $contextFactory,
        private readonly McpServerCardRequestMatcher $requestMatcher,
        private readonly McpServerCardDocumentBuilder $documentBuilder,
        private readonly McpServerCardResponseWriter $responseWriter
    ) {
    }

    public function registerRewriteRule(): void
    {
        add_rewrite_rule('^\.well-known/mcp/server-card\.json/?$', 'index.php?' . McpServerCardRequestContextFactory::QUERY_VAR . '=1', 'top');
    }

    /**
     * @param array<int,string> $queryVars
     * @return array<int,string>
     */
    public function registerQueryVars(array $queryVars): array
    {
        $queryVars[] = McpServerCardRequestContextFactory::QUERY_VAR;

        return array_values(array_unique($queryVars));
    }

    /**
     * @return array{decision: array<string,mixed>, response: array<string,mixed>|null}
     */
    public function handleServerCardRequest(bool $terminate = true): array
    {
        $context  = $this->contextFactory->create();
        $decision = $this->requestMatcher->evaluate($context)->toArray();

        if (! $decision['applies']) {
            return [
                'decision' => $decision,
                'response' => null,
            ];
        }

        $document = $this->documentBuilder->build($context);
        $response = $this->responseWriter->write($document, $terminate);

        return [
            'decision' => $decision,
            'response' => $response,
        ];
    }
}
