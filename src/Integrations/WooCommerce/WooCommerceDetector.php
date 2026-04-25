<?php

declare(strict_types=1);

namespace AgentReadyWP\Integrations\WooCommerce;

final class WooCommerceDetector
{
    public function isActive(): bool
    {
        if (array_key_exists('arwp_test_woocommerce_active', $GLOBALS)) {
            return (bool) $GLOBALS['arwp_test_woocommerce_active'];
        }

        return class_exists('WooCommerce');
    }
}
