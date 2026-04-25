<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class MarkdownResponseWriter
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
     * @return array{headers: array<int,string>, body: string}
     */
    public function write(string $markdown, int $tokenCount, bool $terminate = true): array
    {
        $headers = [
            'Content-Type: text/markdown; charset=utf-8',
            'Vary: Accept',
            'x-markdown-tokens: ' . max(0, $tokenCount),
        ];

        foreach ($headers as $header) {
            ($this->headerEmitter)($header);
        }

        ($this->bodyEmitter)($markdown);

        if ($terminate) {
            ($this->terminateEmitter)();
        }

        return [
            'headers' => $headers,
            'body' => $markdown,
        ];
    }
}
