<?php

declare(strict_types=1);

namespace AgentReadyWP\Application\Runtime\Markdown;

final class MarkdownRenderer
{
    public function render(object $post): string
    {
        $title   = isset($post->post_title) ? trim((string) $post->post_title) : '';
        $content = isset($post->post_content) ? (string) $post->post_content : '';

        if (function_exists('apply_filters')) {
            $content = (string) apply_filters('the_content', $content);
        }

        $markdown = $this->htmlToMarkdown($content);
        $parts    = [];

        if ($title !== '') {
            $parts[] = '# ' . $title;
        }

        if ($markdown !== '') {
            $parts[] = $markdown;
        }

        if (($post->post_type ?? '') === 'product') {
            $productMeta = $this->renderProductMeta($post);
            if ($productMeta !== '') {
                $parts[] = $productMeta;
            }
        }

        return trim(implode("\n\n", $parts)) . "\n";
    }

    private function htmlToMarkdown(string $html): string
    {
        $markdown = $html;

        $markdown = preg_replace_callback('/<\s*h([1-6])[^>]*>(.*?)<\s*\/\s*h\1\s*>/is', static function (array $m): string {
            $level = max(1, min(6, (int) $m[1]));
            $text  = trim(strip_tags((string) $m[2]));

            return str_repeat('#', $level) . ' ' . $text . "\n\n";
        }, $markdown) ?? $markdown;

        $markdown = preg_replace('/<\s*(strong|b)[^>]*>(.*?)<\s*\/\s*(strong|b)\s*>/is', '**$2**', $markdown) ?? $markdown;
        $markdown = preg_replace('/<\s*(em|i)[^>]*>(.*?)<\s*\/\s*(em|i)\s*>/is', '*$2*', $markdown) ?? $markdown;

        $markdown = preg_replace_callback('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', static function (array $m): string {
            $text = trim(strip_tags((string) $m[2]));
            return '[' . $text . '](' . $m[1] . ')';
        }, $markdown) ?? $markdown;

        $markdown = preg_replace('/<img[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'][^>]*>/is', '![$2]($1)', $markdown) ?? $markdown;
        $markdown = preg_replace('/<img[^>]*alt=["\']([^"\']*)["\'][^>]*src=["\']([^"\']+)["\'][^>]*>/is', '![$1]($2)', $markdown) ?? $markdown;

        $markdown = preg_replace_callback('/<\s*li[^>]*>(.*?)<\s*\/\s*li\s*>/is', static function (array $m): string {
            $text = trim(strip_tags((string) $m[1]));
            return '- ' . $text . "\n";
        }, $markdown) ?? $markdown;

        $markdown = preg_replace('/<\s*br\s*\/?>/i', "\n", $markdown) ?? $markdown;
        $markdown = preg_replace('/<\s*\/\s*p\s*>/i', "\n\n", $markdown) ?? $markdown;
        $markdown = preg_replace('/<\s*p[^>]*>/i', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/<\s*\/\s*(div|section|article|ul|ol)\s*>/i', "\n", $markdown) ?? $markdown;
        $markdown = preg_replace('/<\s*(div|section|article|ul|ol)[^>]*>/i', '', $markdown) ?? $markdown;

        $markdown = strip_tags($markdown);
        $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $markdown = preg_replace("/\r\n|\r/", "\n", $markdown) ?? $markdown;
        $markdown = preg_replace("/\n{3,}/", "\n\n", $markdown) ?? $markdown;

        return trim((string) $markdown);
    }

    private function renderProductMeta(object $post): string
    {
        $lines = [];

        if (isset($post->price) && $post->price !== '') {
            $lines[] = '- Price: ' . (string) $post->price;
        }

        if (isset($post->sku) && $post->sku !== '') {
            $lines[] = '- SKU: ' . (string) $post->sku;
        }

        if (isset($post->stock_status) && $post->stock_status !== '') {
            $lines[] = '- Stock: ' . (string) $post->stock_status;
        }

        return implode("\n", $lines);
    }
}
