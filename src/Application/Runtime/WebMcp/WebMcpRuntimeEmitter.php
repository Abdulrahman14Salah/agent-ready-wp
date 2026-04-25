<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\WebMcp;

final class WebMcpRuntimeEmitter
{
    public const SCRIPT_HANDLE = 'arwp-webmcp-runtime';

    public function __construct(
        private readonly WebMcpRuntimeContextFactory $contextFactory,
        private readonly WebMcpToolResolver $toolResolver,
        private readonly WebMcpPayloadBuilder $payloadBuilder
    ) {
    }

    /**
     * @return array{decision: array<string,mixed>, payload: array<string,mixed>|null}
     */
    public function enqueueRuntime(): array
    {
        $context  = $this->contextFactory->create();
        $decision = $this->toolResolver->resolve($context)->toArray();

        if (! $decision['applies']) {
            return [
                'decision' => $decision,
                'payload'  => null,
            ];
        }

        wp_register_script(
            self::SCRIPT_HANDLE,
            ARWP_PLUGIN_URL . 'assets/js/webmcp-runtime.js',
            [],
            ARWP_VERSION,
            true
        );

        $payload = $this->payloadBuilder->build((array) $decision['tools']);
        wp_localize_script(self::SCRIPT_HANDLE, 'arwpWebMcpRuntime', $payload);
        wp_enqueue_script(self::SCRIPT_HANDLE);

        return [
            'decision' => $decision,
            'payload'  => $payload,
        ];
    }
}
