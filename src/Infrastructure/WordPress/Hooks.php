<?php

declare(strict_types=1);

namespace AgentReadyWP\Infrastructure\WordPress;

use AgentReadyWP\Admin\Assets\SettingsPageAssets;
use AgentReadyWP\Admin\Ajax\RunScanAction;
use AgentReadyWP\Admin\Notices\CompatibilityNoticeRenderer;
use AgentReadyWP\Admin\Page\SettingsPage;
use AgentReadyWP\Admin\ViewModel\SettingsPageViewModelFactory;
use AgentReadyWP\Application\Compatibility\EnvironmentDetector;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogDocumentBuilder;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogEntryFactory;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRequestContextFactory;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRequestMatcher;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogResponseWriter;
use AgentReadyWP\Application\Runtime\ApiCatalog\ApiCatalogRuntimeHandler;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalDirectiveBuilder;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalLineNormalizer;
use AgentReadyWP\Application\Runtime\ContentSignals\ContentSignalsRobotsFilter;
use AgentReadyWP\Application\Runtime\Markdown\ContentVisibilityGuard;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownAcceptPreferenceParser;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownEligibilityEvaluator;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRenderer;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRequestContextFactory;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownResponseWriter;
use AgentReadyWP\Application\Runtime\Markdown\MarkdownRuntimeHandler;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardCapabilityResolver;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardDocumentBuilder;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestContextFactory;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRequestMatcher;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardResponseWriter;
use AgentReadyWP\Application\Runtime\McpServerCard\McpServerCardRuntimeHandler;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryDocumentBuilder;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestContextFactory;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRequestMatcher;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryResponseWriter;
use AgentReadyWP\Application\Runtime\OAuthDiscovery\OAuthDiscoveryRuntimeHandler;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceDocumentBuilder;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRequestContextFactory;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRequestMatcher;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceResponseWriter;
use AgentReadyWP\Application\Runtime\ProtectedResource\ProtectedResourceRuntimeHandler;
use AgentReadyWP\Application\Runtime\Markdown\TokenEstimator;
use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpPayloadBuilder;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpRuntimeContextFactory;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpRuntimeEmitter;
use AgentReadyWP\Application\Runtime\WebMcp\WebMcpToolResolver;
use AgentReadyWP\Application\Scan\ScanCache;
use AgentReadyWP\Application\Scan\ScanClient;
use AgentReadyWP\Application\Scan\ScanSummaryMapper;
use AgentReadyWP\Application\Settings\SettingsRepository;
use AgentReadyWP\Application\Settings\SettingsSanitizer;
use AgentReadyWP\Integrations\WooCommerce\WooCommerceDetector;
use AgentReadyWP\Infrastructure\WordPress\Runtime\RuntimeHooksRegistrar;
use AgentReadyWP\Public\AdminPagePlaceholders;

final class Hooks
{
    public function register(): void
    {
        $wooDetector         = new WooCommerceDetector();
        $environmentDetector = new EnvironmentDetector($wooDetector);
        $sanitizer           = new SettingsSanitizer($environmentDetector);
        $settingsRepository  = new SettingsRepository($sanitizer);
        $scanCache           = new ScanCache();
        $scanMapper          = new ScanSummaryMapper();
        $scanClient          = new ScanClient();
        $placeholders        = new AdminPagePlaceholders();
        $notices             = new CompatibilityNoticeRenderer();
        $viewModelFactory    = new SettingsPageViewModelFactory($settingsRepository, $environmentDetector, $placeholders, $scanMapper);

        $settingsPage = new SettingsPage($settingsRepository, $scanCache, $viewModelFactory, $notices);
        $assets       = new SettingsPageAssets($settingsPage);
        $scanAction   = new RunScanAction($scanClient, $scanCache, $scanMapper);

        $runtimeSettingsGateway     = new RuntimeFeatureSettingsGateway($settingsRepository);
        $runtimeCompatibilityGateway = new RuntimeCompatibilityGateway($environmentDetector);
        $apiCatalogHandler          = new ApiCatalogRuntimeHandler(
            new ApiCatalogRequestContextFactory($runtimeSettingsGateway, $runtimeCompatibilityGateway),
            new ApiCatalogRequestMatcher(),
            new ApiCatalogDocumentBuilder(new ApiCatalogEntryFactory()),
            new ApiCatalogResponseWriter()
        );
        $markdownContextFactory     = new MarkdownRequestContextFactory($runtimeSettingsGateway, $runtimeCompatibilityGateway);
        $markdownHandler            = new MarkdownRuntimeHandler(
            $markdownContextFactory,
            new MarkdownAcceptPreferenceParser(),
            new MarkdownEligibilityEvaluator(),
            new MarkdownRenderer(),
            new TokenEstimator(),
            new MarkdownResponseWriter(),
            new ContentVisibilityGuard()
        );
        $contentSignalsFilter       = new ContentSignalsRobotsFilter(
            $runtimeSettingsGateway,
            $runtimeCompatibilityGateway,
            new ContentSignalDirectiveBuilder(),
            new ContentSignalLineNormalizer()
        );
        $webMcpEmitter = new WebMcpRuntimeEmitter(
            new WebMcpRuntimeContextFactory($runtimeSettingsGateway, $runtimeCompatibilityGateway),
            new WebMcpToolResolver(),
            new WebMcpPayloadBuilder()
        );
        $mcpServerCardHandler = new McpServerCardRuntimeHandler(
            new McpServerCardRequestContextFactory(
                $runtimeSettingsGateway,
                $runtimeCompatibilityGateway,
                new McpServerCardCapabilityResolver($runtimeSettingsGateway, $runtimeCompatibilityGateway)
            ),
            new McpServerCardRequestMatcher(),
            new McpServerCardDocumentBuilder(),
            new McpServerCardResponseWriter()
        );
        $oAuthDiscoveryHandler = new OAuthDiscoveryRuntimeHandler(
            new OAuthDiscoveryRequestContextFactory($runtimeSettingsGateway, $runtimeCompatibilityGateway),
            new OAuthDiscoveryRequestMatcher(),
            new OAuthDiscoveryDocumentBuilder(),
            new OAuthDiscoveryResponseWriter()
        );
        $protectedResourceHandler = new ProtectedResourceRuntimeHandler(
            new ProtectedResourceRequestContextFactory($runtimeSettingsGateway, $runtimeCompatibilityGateway),
            new ProtectedResourceRequestMatcher(),
            new ProtectedResourceDocumentBuilder(),
            new ProtectedResourceResponseWriter()
        );
        $runtimeHooks = new RuntimeHooksRegistrar(
            $apiCatalogHandler,
            $markdownHandler,
            $contentSignalsFilter,
            $webMcpEmitter,
            $mcpServerCardHandler,
            $oAuthDiscoveryHandler,
            $protectedResourceHandler
        );

        add_action('admin_menu', [$settingsPage, 'registerMenu']);
        add_action('admin_init', [$settingsPage, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$assets, 'enqueue']);
        add_action('wp_ajax_arwp_run_scan', [$scanAction, 'handle']);
        $runtimeHooks->register();
    }
}
