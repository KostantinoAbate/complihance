<?php

namespace KostantinoAbate\Complihance\Policies;

use InvalidArgumentException;
use KostantinoAbate\Complihance\DTO\Policy;
use KostantinoAbate\Complihance\Policies\Contracts\PolicyRepository;

class BladePolicyRepository implements PolicyRepository
{
    public function current(string $key): Policy
    {
        $config = config("complihance.policies.{$key}");

        if (! $config) {
            throw new InvalidArgumentException("Policy [{$key}] is not configured.");
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
