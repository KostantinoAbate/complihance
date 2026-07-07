<?php

namespace KostantinoAbate\Complihance\Services\Policies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;

class PolicyAcceptanceStatusResolver
{
    public function __construct(
        protected PolicyManager $policies,
    ) {}

    /**
     * Determine whether the current policy version has already been accepted.
     */
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

        if ($source !== null) {
            $query->where('source', PolicyAcceptanceSource::normalize($source));
        }

        if ($subject instanceof Model) {
            return $query
                ->where('subject_type', $subject->getMorphClass())
                ->where('subject_id', $subject->getKey())
                ->exists();
        }

        if (! $anonymousId && ! $sessionId) {
            return false;
        }

        $query->where(function (Builder $query) use ($anonymousId, $sessionId): void {
            if ($anonymousId) {
                $query->where('anonymous_id', $anonymousId);
            }

            if ($sessionId) {
                $anonymousId
                    ? $query->orWhere('session_id', $sessionId)
                    : $query->where('session_id', $sessionId);
            }
        });

        return $query->exists();
    }
}
