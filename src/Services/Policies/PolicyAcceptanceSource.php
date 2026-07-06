<?php

namespace KostantinoAbate\Complihance\Services\Policies;

use InvalidArgumentException;

class PolicyAcceptanceSource
{
    public static function normalize(?string $source): string
    {
        $source = $source ?: 'api';

        $allowedSources = config('complihance.policy_acceptance_sources', []);

        if (! in_array($source, $allowedSources, true)) {
            throw new InvalidArgumentException("Invalid policy acceptance source [{$source}].");
        }

        return $source;
    }
}
