<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ProtectedResource;

final class ProtectedResourceRuntimeHandler
{
    public function __construct(
        private readonly ProtectedResourceRequestContextFactory $contextFactory,
        private readonly ProtectedResourceRequestMatcher $requestMatcher,
        private readonly ProtectedResourceDocumentBuilder $documentBuilder,
        private readonly ProtectedResourceResponseWriter $responseWriter
    ) {
    }

    public function registerRewriteRule(): void
    {
        add_rewrite_rule('^\.well-known/oauth-protected-resource/?$', 'index.php?' . ProtectedResourceRequestContextFactory::QUERY_VAR . '=1', 'top');
    }

    /**
     * @param array<int,string> $queryVars
     * @return array<int,string>
     */
    public function registerQueryVars(array $queryVars): array
    {
        $queryVars[] = ProtectedResourceRequestContextFactory::QUERY_VAR;

        return array_values(array_unique($queryVars));
    }

    /**
     * @return array{decision: array<string,mixed>, response: array<string,mixed>|null}
     */
    public function handleProtectedResourceRequest(bool $terminate = true): array
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
