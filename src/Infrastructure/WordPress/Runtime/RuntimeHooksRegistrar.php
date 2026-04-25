<?php

declare(strict_types=1);

namespace AgentReadyWP\Infrastructure\WordPress\Runtime;

use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRuntimeHandler;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalsRobotsFilter;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRuntimeHandler;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRuntimeHandler;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRuntimeHandler;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRuntimeHandler;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpRuntimeEmitter;

final class RuntimeHooksRegistrar
{
    public function __construct(
        private readonly ApiCatalogRuntimeHandler $apiCatalogRuntimeHandler,
        private readonly MarkdownRuntimeHandler $markdownRuntimeHandler,
        private readonly ContentSignalsRobotsFilter $contentSignalsRobotsFilter,
        private readonly WebMcpRuntimeEmitter $webMcpRuntimeEmitter,
        private readonly McpServerCardRuntimeHandler $mcpServerCardRuntimeHandler,
        private readonly OAuthDiscoveryRuntimeHandler $oAuthDiscoveryRuntimeHandler,
        private readonly ProtectedResourceRuntimeHandler $protectedResourceRuntimeHandler
    ) {
    }

    public function register(): void
    {
        add_action('init', [$this->apiCatalogRuntimeHandler, 'registerRewriteRule']);
        add_action('init', [$this->mcpServerCardRuntimeHandler, 'registerRewriteRule']);
        add_action('init', [$this->oAuthDiscoveryRuntimeHandler, 'registerRewriteRule']);
        add_action('init', [$this->protectedResourceRuntimeHandler, 'registerRewriteRule']);
        add_filter('query_vars', [$this->apiCatalogRuntimeHandler, 'registerQueryVars']);
        add_filter('query_vars', [$this->mcpServerCardRuntimeHandler, 'registerQueryVars']);
        add_filter('query_vars', [$this->oAuthDiscoveryRuntimeHandler, 'registerQueryVars']);
        add_filter('query_vars', [$this->protectedResourceRuntimeHandler, 'registerQueryVars']);
        add_action('template_redirect', [$this->apiCatalogRuntimeHandler, 'handleCatalogRequest'], 0);
        add_action('template_redirect', [$this->mcpServerCardRuntimeHandler, 'handleServerCardRequest'], 0);
        add_action('template_redirect', [$this->oAuthDiscoveryRuntimeHandler, 'handleOAuthDiscoveryRequest'], 0);
        add_action('template_redirect', [$this->protectedResourceRuntimeHandler, 'handleProtectedResourceRequest'], 0);
        add_action('template_redirect', [$this->markdownRuntimeHandler, 'handleCurrentRequest'], 1);
        add_filter('robots_txt', [$this->contentSignalsRobotsFilter, 'filterRobots'], 10, 2);
        add_action('wp_enqueue_scripts', [$this->webMcpRuntimeEmitter, 'enqueueRuntime']);
    }
}
