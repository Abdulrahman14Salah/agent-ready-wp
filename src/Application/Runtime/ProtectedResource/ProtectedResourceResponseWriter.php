<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\ProtectedResource;

final class ProtectedResourceResponseWriter
{
    /** @var callable(string):void */
    private $headerEmitter;
    /** @var callable(string):void */
    private $bodyEmitter;
    /** @var callable():void */
    private $terminateEmitter;

    /**
     * @param callable(string):void|null $headerEmitter
     * @param callable(string):void|null $bodyEmitter
     * @param callable():void|null       $terminateEmitter
     */
    public function __construct(
        ?callable $headerEmitter = null,
        ?callable $bodyEmitter = null,
        ?callable $terminateEmitter = null
    ) {
        $this->headerEmitter = $headerEmitter ?? static function (string $header): void {
            header($header);
        };
        $this->bodyEmitter = $bodyEmitter ?? static function (string $body): void {
            echo $body;
        };
        $this->terminateEmitter = $terminateEmitter ?? static function (): void {
            exit;
        };
    }

    /**
     * @param array<string,mixed> $document
     * @return array{headers: array<int,string>, body: string}
     */
    public function write(array $document, bool $terminate = true): array
    {
        $headers = [
            'Content-Type: application/json; charset=utf-8',
        ];
        $body = (string) wp_json_encode($document);
        if ($body === '') {
            $body = '{}';
        }

        foreach ($headers as $header) {
            ($this->headerEmitter)($header);
        }

        ($this->bodyEmitter)($body);

        if ($terminate) {
            ($this->terminateEmitter)();
        }

        return [
            'headers' => $headers,
            'body' => $body,
        ];
    }
}
