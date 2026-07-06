<?php

namespace KostantinoAbate\Complihance\Services\Policies;

use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;

class PolicyAcceptanceStatusResolver
{
    public function __construct(
        protected PolicyManager $policies,
    ) {}

    public function hasAccepted(
        string $key,
        mixed $subject = null,
        ?string $source = null,
        ?string $anonymousId = null,
        ?string $sessionId = null,
    ): bool {
        $policy = $this->policies->get($key);

        $query = ComplihancePolicyAcceptance::query()
            ->where('policy_key', $policy->key)
            ->where('policy_version', $policy->version);

        if ($source) {
            $query->where('source', $source);
        }

        if ($subject) {
            return $query
                ->where('subject_type', $subject->getMorphClass())
                ->where('subject_id', $subject->getKey())
                ->exists();
        }

        if (! $anonymousId && ! $sessionId) {
            return false;
        }

        $query->where(function ($query) use ($anonymousId, $sessionId) {
            if ($anonymousId) {
                $query->where('anonymous_id', $anonymousId);
            }

            if ($sessionId) {
                $method = $anonymousId ? 'orWhere' : 'where';

                $query->{$method}('session_id', $sessionId);
            }
        });

        return $query->exists();
    }
}
