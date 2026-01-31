<?php

use App\Helpers\MarkdownHelper;

describe('MarkdownHelper', function () {
    it('converts markdown to html', function () {
        $markdown = '# Hello World';
        $html = MarkdownHelper::toHtml($markdown);

        expect($html)->toContain('<h1>Hello World</h1>');
    });

    it('converts markdown with multiple elements', function () {
        $markdown = <<<'MD'
# Title

This is a paragraph with **bold** text.

- Item 1
- Item 2
MD;

        $html = MarkdownHelper::toHtml($markdown);

        expect($html)->toContain('<h1>Title</h1>')
            ->and($html)->toContain('<strong>bold</strong>')
            ->and($html)->toContain('<li>Item 1</li>');
    });

    it('sanitizes potentially dangerous html', function () {
        $markdown = '<script>alert("xss")</script>';
        $html = MarkdownHelper::toHtml($markdown);

        expect($html)->not->toContain('<script>');
    });

    it('escapes html tags for security', function () {
        $markdown = 'This is <em>emphasized</em> text.';
        $html = MarkdownHelper::toHtml($markdown);

        expect($html)->toContain('&lt;em&gt;');
    });

    it('handles empty string', function () {
        $html = MarkdownHelper::toHtml('');

        expect($html)->toBe('');
    });
});
