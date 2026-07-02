<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Models\Consent;

class CurrentConsentResolver
{
    public function resolve(Request $request): ?Consent
    {
        $cookieName = config(
            'complihance.cookie_name',
            'complihance_consent'
        );

        $cookie = $request->cookie($cookieName);

        if ($cookie) {
            $decoded = json_decode($cookie, true);

            if (
                is_array($decoded)
                && ! empty($decoded['consent_uuid'])
            ) {
                $consent = Consent::query()
                    ->where('consent_uuid', $decoded['consent_uuid'])
                    ->whereNull('revoked_at')
                    ->first();

                if ($consent) {
                    return $consent;
                }
            }
        }

        return Consent::query()
            ->whereNull('revoked_at')
            ->when(auth()->check(), function ($query) {
                $query->whereMorphedTo(
                    'subject',
                    auth()->user()
                );
            })
            ->when(! auth()->check(), function ($query) use ($request) {
                $query->where(
                    'session_id',
                    $request->hasSession()
                        ? $request->session()->getId()
                        : null
                );
            })
            ->latest('accepted_at')
            ->first();
    }

    public function resolveFromCookie(): ?Consent
    {
        return $this->resolve(request());
    }

    public function hasActiveConsent(array $decodedConsent): bool
    {
        $consentUuid = $decodedConsent['consent_uuid'] ?? null;

        if (! $consentUuid) {
            return false;
        }

        return Consent::query()
            ->where('consent_uuid', $consentUuid)
            ->whereNull('revoked_at')
            ->exists();
    }
}
