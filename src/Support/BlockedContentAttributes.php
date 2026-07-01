<?php

namespace KostantinoAbate\Complihance\Support;

use Illuminate\Support\HtmlString;

class BlockedContentAttributes
{
    public function render(
        string $category,
        ?string $src = null,
        ?string $vendor = null,
        ?string $placeholder = null,
        bool $inlineConsent = true,
    ): HtmlString {
        $attributes = [
            'data-complihance-blocked' => true,
            'data-complihance-category' => $category,
            'data-complihance-inline-consent' => $inlineConsent ? 'true' : 'false',
        ];

        if ($src !== null && $src !== '') {
            $attributes['data-complihance-src'] = $src;
        }

        if ($vendor !== null && $vendor !== '') {
            $attributes['data-complihance-vendor'] = $vendor;
        }

        if ($placeholder !== null && $placeholder !== '') {
            $attributes['data-complihance-placeholder'] = $placeholder;
        }

        return new HtmlString(
            collect($attributes)
                ->map(function (mixed $value, string $key): string {
                    if ($value === true) {
                        return $key;
                    }

                    return $key.'="'.e((string) $value).'"';
                })
                ->implode(' ')
        );
    }
}
