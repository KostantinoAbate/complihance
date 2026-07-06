<?php

namespace KostantinoAbate\Complihance\PolicyManagement;

use InvalidArgumentException;
use KostantinoAbate\Complihance\DTO\Policy;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Services\PolicyAcceptanceRecorder;
use KostantinoAbate\Complihance\Services\PolicyAcceptanceStatusResolver;

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

    /**
     * Facade-friendly shortcut for manually recording a policy acceptance.
     *
     * The main consent flow should use PolicyAcceptanceRecorder directly.
     */
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
        return app(PolicyAcceptanceRecorder::class)->recordManual(
            key: $key,
            subject: $subject,
            source: $source,
            consentId: $consentId,
            anonymousId: $anonymousId,
            sessionId: $sessionId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $metadata,
        );
    }

    public function hasAccepted(
        string $key,
        mixed $subject = null,
        ?string $source = null,
        ?string $anonymousId = null,
        ?string $sessionId = null,
    ): bool {
        return app(PolicyAcceptanceStatusResolver::class)->hasAccepted(
            key: $key,
            subject: $subject,
            source: $source,
            anonymousId: $anonymousId,
            sessionId: $sessionId,
        );
    }

    public function requiresAcceptance(
        string $key,
        mixed $subject = null,
        ?string $source = null,
        ?string $anonymousId = null,
        ?string $sessionId = null,
    ): bool {
        return ! $this->hasAccepted(
            key: $key,
            subject: $subject,
            source: $source,
            anonymousId: $anonymousId,
            sessionId: $sessionId,
        );
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
