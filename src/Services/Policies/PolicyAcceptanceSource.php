<?php

namespace KostantinoAbate\Complihance\Services\Policies;

use InvalidArgumentException;

class PolicyAcceptanceSource
{
    /**
     * Normalize and validate the source used for a policy acceptance record.
     */
    public static function normalize(?string $source): string
    {
        $source = $source ?: 'api';

        $allowedSources = config('complihance.policy_acceptance_sources', []);

        if (! is_array($allowedSources)) {
            $allowedSources = [];
        }

        if (! in_array($source, $allowedSources, true)) {
            throw new InvalidArgumentException("Invalid policy acceptance source [$source].");
        }

        return $source;
    }
}
