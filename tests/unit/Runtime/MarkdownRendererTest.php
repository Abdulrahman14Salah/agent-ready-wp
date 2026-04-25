<?php

declare(strict_types=1);

use AgentReadyWP\Application\Runtime\Markdown\MarkdownRenderer;
use PHPUnit\Framework\TestCase;

final class MarkdownRendererTest extends TestCase
{
    public function test_renderer_transforms_basic_html_to_markdown(): void
    {
        $renderer = new MarkdownRenderer();

        $post = (object) [
            'post_title' => 'Sample Post',
            'post_type' => 'post',
            'post_content' => '<p>Intro <strong>paragraph</strong>.</p><ul><li>First item</li><li>Second item</li></ul><p><a href="https://example.com/read-more">Read more</a></p>',
        ];

        $markdown = $renderer->render($post);
        $expected = (string) file_get_contents(__DIR__ . '/../../fixtures/markdown-expected-output.md');

        $this->assertSame($expected, $markdown);
    }

    public function test_renderer_appends_product_metadata_for_product_posts(): void
    {
        $renderer = new MarkdownRenderer();

        $post = (object) [
            'post_title' => 'Product Name',
            'post_type' => 'product',
            'post_content' => '<p>Product details.</p>',
            'price' => '10.00',
            'sku' => 'SKU-1',
            'stock_status' => 'instock',
        ];

        $markdown = $renderer->render($post);

        $this->assertStringContainsString('- Price: 10.00', $markdown);
        $this->assertStringContainsString('- SKU: SKU-1', $markdown);
        $this->assertStringContainsString('- Stock: instock', $markdown);
    }

    public function test_renderer_preserves_arabic_and_utf8_content(): void
    {
        $renderer = new MarkdownRenderer();

        $post = (object) [
            'post_title' => 'مرحبا بالعالم',
            'post_type' => 'post',
            'post_content' => '<p>هذا نص عربي.</p><p><a href="https://example.com/ar">اقرأ المزيد</a></p>',
        ];

        $markdown = $renderer->render($post);

        $this->assertStringContainsString('# مرحبا بالعالم', $markdown);
        $this->assertStringContainsString('هذا نص عربي.', $markdown);
        $this->assertStringContainsString('[اقرأ المزيد](https://example.com/ar)', $markdown);
    }
}
