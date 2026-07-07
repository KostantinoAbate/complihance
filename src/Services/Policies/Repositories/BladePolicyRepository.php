<?php

namespace KostantinoAbate\Complihance\Services\Policies\Repositories;

use InvalidArgumentException;
use KostantinoAbate\Complihance\Data\Policy;
use KostantinoAbate\Complihance\Services\Policies\Repositories\Contracts\PolicyRepository;

class BladePolicyRepository implements PolicyRepository
{
    /**
     * Retrieve the current Blade-backed policy definition from the package configuration.
     */
    public function current(string $key): Policy
    {
        $config = config("complihance.policies.$key");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Policy [$key] is not configured.");
        }

        if (! isset($config['version'])) {
            throw new InvalidArgumentException("Policy [$key] is missing a version.");
        }

        return new Policy(
            key: $key,
            version: $config['version'],
            title: $config['title'] ?? ucfirst($key),
            content: null,
            view: $config['view'] ?? null,
            driver: 'blade',
        );
    }
}
