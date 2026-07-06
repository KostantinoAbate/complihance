<?php

namespace KostantinoAbate\Complihance\Support;

use InvalidArgumentException;

class ConsentSource
{
    public static function normalize(?string $source): string
    {
        $source = $source ?: 'api';

        $allowedSources = config('complihance.consent_sources', [
            'banner',
            'preferences',
            'api',
        ]);

        if (! in_array($source, $allowedSources, true)) {
            throw new InvalidArgumentException("Invalid consent source [{$source}].");
        }

        return $source;
    }
}
