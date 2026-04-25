<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

use AgentReadyWP\Application\Runtime\RuntimeCompatibilityGateway;
use AgentReadyWP\Application\Runtime\RuntimeFeatureSettingsGateway;

final class MarkdownRequestContextFactory
{
    public function __construct(
        private readonly RuntimeFeatureSettingsGateway $settingsGateway,
        private readonly RuntimeCompatibilityGateway $compatibilityGateway
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(): array
    {
        $settings      = $this->settingsGateway->getMarkdownSettings();
        $compatibility = $this->compatibilityGateway->get();
        $requestPath   = $this->getRequestPath();

        $queryObject = function_exists('get_queried_object') ? get_queried_object() : null;
        $postType    = is_object($queryObject) && isset($queryObject->post_type)
            ? (string) $queryObject->post_type
            : null;

        $supportedPostTypes = (array) $settings['post_types'];
        if (! empty($settings['include_woo']) && ! empty($compatibility['woocommerce_active'])) {
            $supportedPostTypes[] = 'product';
        }

        return [
            'accept_header' => (string) ($_SERVER['HTTP_ACCEPT'] ?? ''),
            'request_path' => $requestPath,
            'is_singular' => function_exists('is_singular') ? (bool) is_singular() : false,
            'query_object' => is_object($queryObject) ? $queryObject : null,
            'post_type' => $postType,
            'post_id' => is_object($queryObject) && isset($queryObject->ID) ? (int) $queryObject->ID : null,
            'feature_enabled' => (bool) $settings['enabled'],
            'supported_post_types' => array_values(array_unique(array_map('strval', $supportedPostTypes))),
            'is_supported_post_type' => $postType !== null && in_array($postType, $supportedPostTypes, true),
            'is_eligible_frontend_document_request' => $this->isEligibleFrontendDocumentRequest($requestPath),
            'requester_can_view' => true,
        ];
    }

    private function getRequestPath(): string
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path       = (string) parse_url($requestUri, PHP_URL_PATH);

        return $path !== '' ? $path : '/';
    }

    private function isEligibleFrontendDocumentRequest(string $requestPath): bool
    {
        if (function_exists('is_admin') && is_admin()) {
            return false;
        }

        if (function_exists('is_feed') && is_feed()) {
            return false;
        }

        if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
            return false;
        }

        if (function_exists('wp_doing_cron') && wp_doing_cron()) {
            return false;
        }

        if ($this->isRestRequest($requestPath) || $this->isSitemapRequest($requestPath) || $this->isLoginRequest($requestPath) || $this->isAssetRequest($requestPath)) {
            return false;
        }

        return true;
    }

    private function isRestRequest(string $requestPath): bool
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        if (str_starts_with($requestPath, '/wp-json/') || $requestPath === '/wp-json') {
            return true;
        }

        return isset($_GET['rest_route']) && $_GET['rest_route'] !== '';
    }

    private function isSitemapRequest(string $requestPath): bool
    {
        return str_contains($requestPath, 'wp-sitemap');
    }

    private function isLoginRequest(string $requestPath): bool
    {
        return in_array(basename($requestPath), ['wp-login.php', 'wp-register.php'], true);
    }

    private function isAssetRequest(string $requestPath): bool
    {
        return in_array(
            strtolower((string) pathinfo($requestPath, PATHINFO_EXTENSION)),
            ['css', 'js', 'map', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'otf'],
            true
        );
    }
}
