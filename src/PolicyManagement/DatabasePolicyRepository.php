<?php

namespace KostantinoAbate\Complihance\PolicyManagement;

use InvalidArgumentException;
use KostantinoAbate\Complihance\DTO\Policy;
use KostantinoAbate\Complihance\Models\PolicyVersion;
use KostantinoAbate\Complihance\PolicyManagement\Contracts\PolicyRepository;

class DatabasePolicyRepository implements PolicyRepository
{
    public function current(string $key): Policy
    {
        $policy = PolicyVersion::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->latest('published_at')
            ->latest('id')
            ->first();

        if (! $policy) {
            throw new InvalidArgumentException("No active policy found for [{$key}].");
        }

        return new Policy(
            key: $policy->key,
            version: $policy->version,
            title: $policy->title,
            content: $policy->content,
            view: $policy->view,
            driver: 'database',
        );
    }
}
