<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use KostantinoAbate\Complihance\Data\ConsentRequestContext;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\Consent;

class ConsentRecorder
{
    /**
     * Persist a consent record for the given request context.
     *
     * @param  array<int, string>  $acceptedCategories
     * @param  array<int, string>  $rejectedCategories
     * @param  array<int, string>  $acceptedVendors
     */
    public function record(
        ConsentRequestContext $context,
        array $acceptedCategories,
        array $rejectedCategories,
        array $acceptedVendors,
        string $source,
    ): Consent {
        $data = [
            'session_id' => $context->sessionId,
            'anonymous_id' => $context->anonymousId,

            'subject_type' => $context->subjectType,
            'subject_id' => $context->subjectId,

            'accepted_categories' => $acceptedCategories,
            'rejected_categories' => $rejectedCategories,

            'policy_version' => ComplihancePolicy::currentVersion('cookie'),
            'cookie_configuration_version' => config('complihance.cookie_configuration_version'),

            'ip_address' => $context->ipAddress,
            'user_agent' => $context->userAgent,

            'source' => $source,
            'accepted_at' => now(),
        ];

        if (config('complihance.granular_consent.enabled', false)) {
            $data['vendors'] = $acceptedVendors;
        }

        return Consent::query()->create($data);
    }
}
