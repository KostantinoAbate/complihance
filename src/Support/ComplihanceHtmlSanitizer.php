<?php

namespace KostantinoAbate\Complihance\Support;

use Illuminate\Support\Str;

class ComplihanceHtmlSanitizer
{
    protected array $allowedProtocols = [
        'http://',
        'https://',
        'mailto:',
        'tel:',
        '/',
        '#',
    ];

    public function sanitize(?string $html): string
    {
        $html = (string) $html;

        $html = strip_tags($html, '<strong><b><em><i><u><br><a>');

        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/\s+(style|class|id|src|srcset|target|rel)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        $html = preg_replace_callback('/<a\b([^>]*)>/i', function (array $matches) {
            preg_match('/\shref\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $matches[1], $hrefMatch);

            $href = $hrefMatch[2] ?? $hrefMatch[3] ?? $hrefMatch[4] ?? null;

            if (! $href || ! $this->isAllowedHref($href)) {
                return '<a>';
            }

            return '<a href="'.e($href).'">';
        }, $html);

        return $html;
    }

    protected function isAllowedHref(string $href): bool
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $lowerHref = Str::lower($href);

        if (str_contains($lowerHref, "\0")) {
            return false;
        }

        if (preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
            return false;
        }

        return Str::startsWith($lowerHref, $this->allowedProtocols);
    }
}
