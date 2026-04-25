<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ApiCatalog;

final class ApiCatalogRuntimeHandler
{
    public function __construct(
        private readonly ApiCatalogRequestContextFactory $contextFactory,
        private readonly ApiCatalogRequestMatcher $requestMatcher,
        private readonly ApiCatalogDocumentBuilder $documentBuilder,
        private readonly ApiCatalogResponseWriter $responseWriter
    ) {
    }

    public function registerRewriteRule(): void
    {
        add_rewrite_rule('^\.well-known/api-catalog/?$', 'index.php?' . ApiCatalogRequestContextFactory::QUERY_VAR . '=1', 'top');
    }

    /**
     * @param array<int,string> $queryVars
     * @return array<int,string>
     */
    public function registerQueryVars(array $queryVars): array
    {
        $queryVars[] = ApiCatalogRequestContextFactory::QUERY_VAR;

        return array_values(array_unique($queryVars));
    }

    /**
     * @return array{decision: array<string,mixed>, response: array<string,mixed>|null}
     */
    public function handleCatalogRequest(bool $terminate = true): array
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
