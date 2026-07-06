<?php

namespace KostantinoAbate\Complihance\PolicyManagement;

use InvalidArgumentException;
use KostantinoAbate\Complihance\DTO\Policy;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Support\PolicyAcceptanceSource;

class PolicyManager
{
    public function get(string $key): Policy
    {
        return $this->repositoryFor($key)->current($key);
    }

    public function privacy(): Policy
    {
        return $this->get('privacy');
    }

    public function cookie(): Policy
    {
        return $this->get('cookie');
    }

    public function currentVersion(string $key): string
    {
        return $this->get($key)->version;
    }

    public function currentContent(string $key): ?string
    {
        return $this->get($key)->content;
    }

    public function configuredKeys(): array
    {
        return array_keys(config('complihance.policies', []));
    }

    public function accept(
        string $key,
        mixed $subject = null,
        ?string $source = null,
        ?int $consentId = null,
        ?string $anonymousId = null,
        ?string $sessionId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $metadata = [],
    ): ComplihancePolicyAcceptance {
        $policy = $this->get($key);
        $source = PolicyAcceptanceSource::normalize($source);

        return ComplihancePolicyAcceptance::query()->create([
            'consent_id' => $consentId,

            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),

            'session_id' => $sessionId ?? (
                request()->hasSession() ? request()->session()->getId() : null
            ),

            'anonymous_id' => $anonymousId ?? request()->cookie(
                config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
            ),

            'policy_key' => $policy->key,
            'policy_version' => $policy->version,

            'source' => $source,
            'metadata' => $metadata,

            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),

            'accepted_at' => now(),
        ]);
    }

    public function hasAccepted(
        string $key,
        mixed $subject = null,
        ?string $source = null,
    ): bool {
        $policy = $this->get($key);

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

        $anonymousId = request()->cookie(
            config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
        );

        $sessionId = request()->hasSession()
            ? request()->session()->getId()
            : null;

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

    public function requiresAcceptance(
        string $key,
        mixed $subject = null,
        ?string $source = null,
    ): bool {
        return ! $this->hasAccepted($key, $subject, $source);
    }

    protected function repositoryFor(string $key)
    {
        $driver = config("complihance.policies.{$key}.driver", 'blade');

        return match ($driver) {
            'blade' => app(BladePolicyRepository::class),
            'database' => app(DatabasePolicyRepository::class),
            default => throw new InvalidArgumentException("Unsupported policy driver [{$driver}]."),
        };
    }
}
