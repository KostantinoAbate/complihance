<?php

namespace KostantinoAbate\Complihance\Services\Policies\Repositories;

use InvalidArgumentException;
use KostantinoAbate\Complihance\Data\Policy;
use KostantinoAbate\Complihance\Models\PolicyVersion;
use KostantinoAbate\Complihance\Services\Policies\Repositories\Contracts\PolicyRepository;

class DatabasePolicyRepository implements PolicyRepository
{
    /**
     * Retrieve the latest active policy version from the database.
     */
    public function current(string $key): Policy
    {
        /** @var PolicyVersion|null $policy */
        $policy = PolicyVersion::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->latest('published_at')
            ->latest('id')
            ->first();

        if (! $policy) {
            throw new InvalidArgumentException("No active policy found for [$key].");
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
