<?php

namespace KostantinoAbate\Complihance\Services\Policies;

use InvalidArgumentException;
use KostantinoAbate\Complihance\Data\Policy;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Services\Policies\Repositories\BladePolicyRepository;
use KostantinoAbate\Complihance\Services\Policies\Repositories\Contracts\PolicyRepository;
use KostantinoAbate\Complihance\Services\Policies\Repositories\DatabasePolicyRepository;

class PolicyManager
{
    /**
     * Retrieve the current policy for the given key.
     */
    public function get(string $key): Policy
    {
        return $this->repositoryFor($key)->current($key);
    }

    /**
     * Retrieve the current privacy policy.
     *
     * @noinspection PhpUnused
     */
    public function privacy(): Policy
    {
        return $this->get('privacy');
    }

    /**
     * Retrieve the current cookie policy.
     */
    public function cookie(): Policy
    {
        return $this->get('cookie');
    }

    /**
     * Retrieve the current policy version for the given key.
     *
     * @noinspection PhpUnused
     */
    public function currentVersion(string $key): string
    {
        return $this->get($key)->version;
    }

    /**
     * Retrieve the current policy content for the given key.
     *
     * @noinspection PhpUnused
     */
    public function currentContent(string $key): ?string
    {
        return $this->get($key)->content;
    }

    /**
     * Retrieve the configured policy keys.
     *
     * @noinspection PhpUnused
     *
     * @return array<int, string>
     */
    public function configuredKeys(): array
    {
        return array_keys(config('complihance.policies', []));
    }

    /**
     * Facade-friendly shortcut for manually recording a policy acceptance.
     *
     * @param  array<string, mixed>  $metadata
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
        return app(PolicyAcceptanceStatusResolver::class)->hasAccepted(
            key: $key,
            subject: $subject,
            source: $source,
            anonymousId: $anonymousId,
            sessionId: $sessionId,
        );
    }

    /**
     * Determine whether the current policy version still requires acceptance.
     *
     * @noinspection PhpUnused
     */
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

    /**
     * Resolve the repository responsible for the given policy key.
     */
    protected function repositoryFor(string $key): PolicyRepository
    {
        $driver = config("complihance.policies.$key.driver", 'blade');

        return match ($driver) {
            'blade' => app(BladePolicyRepository::class),
            'database' => app(DatabasePolicyRepository::class),
            default => throw new InvalidArgumentException("Unsupported policy driver [$driver]."),
        };
    }
}
