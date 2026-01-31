<?php

namespace App\Helpers;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownHelper
{
    /**
     * Convert markdown string to HTML string with sanitization.
     *
     * @param string $markdown
     * @return string
     */
    public static function toHtml(string $markdown): string
    {
        if ($markdown === '') {
            return '';
        }

        // Configure environment with extensions
        $config = [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'disallowed_raw_html' => [
                'disallowed_tags' => ['script', 'iframe', 'object', 'embed'],
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());

        $converter = new MarkdownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }
}
