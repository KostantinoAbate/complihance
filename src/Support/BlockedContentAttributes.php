<?php

namespace KostantinoAbate\Complihance\Support;

use Illuminate\Support\HtmlString;

class BlockedContentAttributes
{
    public function render(
        ?string $category = null,
        ?string $src = null,
        ?string $vendor = null,
        bool $inlineConsent = true,
    ): HtmlString {
        $attributes = [
            'data-complihance-blocked' => true,
            'data-complihance-inline-consent' => $inlineConsent ? 'true' : 'false',
        ];

        if ($category !== null && $category !== '') {
            $attributes['data-complihance-category'] = $category;
        } else {
            $attributes['data-complihance-requires'] = 'all-optional';
        }

        if ($src !== null && $src !== '') {
            $attributes['data-complihance-src'] = $src;
        }

        if ($vendor !== null && $vendor !== '') {
            $attributes['data-complihance-vendor'] = $vendor;
        }

        return new HtmlString(
            collect($attributes)
                ->map(fn (mixed $value, string $key): string => $value === true
                    ? $key
                    : $key.'="'.e((string) $value).'"')
                ->implode(' ')
        );
    }
}
