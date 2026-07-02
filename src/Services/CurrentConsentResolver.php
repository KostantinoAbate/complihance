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

        if (auth()->check()) {
            return Consent::query()
                ->whereNull('revoked_at')
                ->whereMorphedTo('subject', auth()->user())
                ->latest('accepted_at')
                ->first();
        }

        $anonymousId = $request->cookie(
            config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
        );

        $sessionId = $request->hasSession()
            ? $request->session()->getId()
            : null;

        if (! $anonymousId && ! $sessionId) {
            return null;
        }

        return Consent::query()
            ->whereNull('revoked_at')
            ->where(function ($query) use ($anonymousId, $sessionId) {
                if ($anonymousId) {
                    $query->where('anonymous_id', $anonymousId);
                }

                if ($sessionId) {
                    $method = $anonymousId ? 'orWhere' : 'where';

                    $query->{$method}('session_id', $sessionId);
                }
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
