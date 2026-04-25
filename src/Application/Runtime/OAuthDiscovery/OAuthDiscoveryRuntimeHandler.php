<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\OAuthDiscovery;

final class OAuthDiscoveryRuntimeHandler
{
    public function __construct(
        private readonly OAuthDiscoveryRequestContextFactory $contextFactory,
        private readonly OAuthDiscoveryRequestMatcher $requestMatcher,
        private readonly OAuthDiscoveryDocumentBuilder $documentBuilder,
        private readonly OAuthDiscoveryResponseWriter $responseWriter
    ) {
    }

    public function registerRewriteRule(): void
    {
        add_rewrite_rule('^\.well-known/openid-configuration/?$', 'index.php?' . OAuthDiscoveryRequestContextFactory::QUERY_VAR . '=1', 'top');
    }

    /**
     * @param array<int,string> $queryVars
     * @return array<int,string>
     */
    public function registerQueryVars(array $queryVars): array
    {
        $queryVars[] = OAuthDiscoveryRequestContextFactory::QUERY_VAR;

        return array_values(array_unique($queryVars));
    }

    /**
     * @return array{decision: array<string,mixed>, response: array<string,mixed>|null}
     */
    public function handleOAuthDiscoveryRequest(bool $terminate = true): array
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
