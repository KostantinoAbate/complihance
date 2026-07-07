<?php

namespace KostantinoAbate\Complihance\Support;

use Illuminate\Support\Str;

class ComplihanceHtmlSanitizer
{
    /**
     * Protocols and URL prefixes allowed for anchor href attributes.
     *
     * @var array<int, string>
     */
    protected array $allowedProtocols = [
        'http://',
        'https://',
        'mailto:',
        'tel:',
        '/',
        '#',
    ];

    /**
     * Sanitize package-managed HTML while preserving a small safe formatting subset.
     */
    public function sanitize(?string $html): string
    {
        $html = (string) $html;

        $html = strip_tags($html, '<strong><b><em><i><u><br><a>');

        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/\s+(style|class|id|src|srcset|target|rel)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';

        return preg_replace_callback('/<a\b([^>]*)>/i', function (array $matches): string {
            preg_match('/\shref\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $matches[1], $hrefMatch);

            $href = $hrefMatch[2] ?? $hrefMatch[3] ?? $hrefMatch[4] ?? null;

            if (! is_string($href) || ! $this->isAllowedHref($href)) {
                return '<a>';
            }

            return '<a href="'.e($href).'">';
        }, $html) ?? '';
    }

    /**
     * Determine whether a sanitized anchor href uses an allowed URL prefix.
     */
    protected function isAllowedHref(string $href): bool
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $lowerHref = Str::lower($href);

        if ($href === '' || preg_match('/[\x00-\x1F\x7F]/', $href)) {
            return false;
        }

        if (preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
            return false;
        }

        return Str::startsWith($lowerHref, $this->allowedProtocols);
    }
}
