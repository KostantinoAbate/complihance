<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\Consent;

class ConsentPayloadBuilder
{
    /**
     * Build the current consent response payload.
     *
     * @return array<string, mixed>
     */
    public function build(?Consent $consent): array
    {
        return [
            'has_consent' => $consent !== null && $consent->revoked_at === null,
            'requires_renewal' => !$consent || $this->requiresRenewal($consent),
            'consent' => $consent ? $this->consentData($consent) : null,
        ];
    }

    /**
     * Build the serialized consent data.
     *
     * @return array<string, mixed>
     */
    protected function consentData(Consent $consent): array
    {
        return [
            'consent_uuid' => $consent->consent_uuid,
            'accepted_categories' => $consent->accepted_categories ?? [],
            'rejected_categories' => $consent->rejected_categories ?? [],
            'vendors' => $consent->vendors ?? [],
            'policy_version' => $consent->policy_version,
            'cookie_configuration_version' => $consent->cookie_configuration_version,
            'accepted_at' => $consent->accepted_at?->toISOString(),
            'revoked_at' => $consent->revoked_at?->toISOString(),
        ];
    }

    /**
     * Determine whether the stored consent must be renewed.
     */
    protected function requiresRenewal(Consent $consent): bool
    {
        return $consent->policy_version !== ComplihancePolicy::currentVersion('cookie')
            || $consent->cookie_configuration_version !== config('complihance.cookie_configuration_version');
    }
}
