<?php

namespace KostantinoAbate\Complihance\Services;

use KostantinoAbate\Complihance\DTO\ConsentRequestContext;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\PolicyManagement\PolicyManager;
use KostantinoAbate\Complihance\Support\PolicyAcceptanceSource;

class PolicyAcceptanceRecorder
{
    public function __construct(
        protected PolicyManager $policies,
    ) {}

    public function record(
        string $key,
        ConsentRequestContext $context,
        ?Consent $consent = null,
        ?string $source = null,
        array $metadata = [],
    ): ComplihancePolicyAcceptance {
        $policy = $this->policies->get($key);
        $source = PolicyAcceptanceSource::normalize($source);

        $identityHash = hash('sha256', implode('|', [
            $context->subjectType ?? '',
            $context->subjectId ?? '',
            $context->anonymousId ?? '',
            $policy->key,
            $policy->version,
            $source ?? '',
        ]));

        return ComplihancePolicyAcceptance::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            [
                'consent_id' => $consent?->id,

                'subject_type' => $context->subjectType,
                'subject_id' => $context->subjectId,

                'session_id' => $context->sessionId,
                'anonymous_id' => $context->anonymousId,

                'policy_key' => $policy->key,
                'policy_version' => $policy->version,

                'source' => $source,
                'metadata' => $metadata,

                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,

                'accepted_at' => now(),
            ]
        );
    }

    public function recordManual(
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
        $request = request();

        $context = new ConsentRequestContext(
            sessionId: $sessionId ?? ($request->hasSession() ? $request->session()->getId() : null),
            anonymousId: $anonymousId ?? $request->cookie(config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')),
            ipAddress: $ipAddress ?? $request->ip(),
            userAgent: $userAgent ?? $request->userAgent(),
            subjectType: $subject ? $subject->getMorphClass() : null,
            subjectId: $subject?->getKey(),
            subject: $subject,
            isSecure: $request->isSecure(),
        );

        return $this->record(
            key: $key,
            context: $context,
            consent: $consentId ? Consent::query()->find($consentId) : null,
            source: $source,
            metadata: $metadata,
        );
    }
}
