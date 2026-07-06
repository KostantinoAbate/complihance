<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\Consent;

class ConsentPayloadBuilder
{
    public function build(?Consent $consent): array
    {
        return [
            'has_consent' => $consent !== null && $consent->revoked_at === null,
            'requires_renewal' => $consent
                ? $this->requiresRenewal($consent)
                : true,
            'consent' => $consent ? $this->consentData($consent) : null,
        ];
    }

    protected function consentData(Consent $consent): array
    {
        return [
            'consent_uuid' => $consent->consent_uuid,
            'accepted_categories' => $consent->accepted_categories ?? [],
            'rejected_categories' => $consent->rejected_categories ?? [],
            'vendors' => $consent->vendors ?? [],
            'policy_version' => $consent->policy_version,
            'cookie_configuration_version' => $consent->cookie_configuration_version,
            'accepted_at' => optional($consent->accepted_at)->toISOString(),
            'revoked_at' => optional($consent->revoked_at)->toISOString(),
        ];
    }

    protected function requiresRenewal(Consent $consent): bool
    {
        return $consent->policy_version !== ComplihancePolicy::currentVersion('cookie')
            || $consent->cookie_configuration_version !== config('complihance.cookie_configuration_version');
    }
}
