<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use InvalidArgumentException;

class ConsentSource
{
    /**
     * Normalize and validate a consent source.
     */
    public static function normalize(?string $source): string
    {
        $source ??= 'api';

        $allowedSources = config('complihance.policy_acceptance_sources', [
            'banner',
            'preferences',
            'api',
            'seeder',
            'console',
        ]);

        if (! in_array($source, $allowedSources, true)) {
            throw new InvalidArgumentException("Invalid consent source [$source].");
        }

        return $source;
    }
}
