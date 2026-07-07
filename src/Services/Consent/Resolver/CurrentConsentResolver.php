<?php

namespace KostantinoAbate\Complihance\Services\Consent\Resolver;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JsonException;
use KostantinoAbate\Complihance\Models\Consent;

class CurrentConsentResolver
{
    /**
     * Resolve the current active consent for the given request.
     */
    public function resolve(Request $request): ?Consent
    {
        $cookieName = config(
            'complihance.cookie_name',
            'complihance_consent'
        );

        $cookie = $request->cookie($cookieName);

        if ($cookie) {
            try {
                $decoded = json_decode($cookie, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $decoded = null;
            }

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
            $userConsent = Consent::query()
                ->whereNull('revoked_at')
                ->whereMorphedTo('subject', auth()->user())
                ->latest('accepted_at')
                ->first();

            if ($userConsent) {
                return $userConsent;
            }

            $anonymousId = $request->cookie(
                config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
            );

            if ($anonymousId) {
                return Consent::query()
                    ->whereNull('revoked_at')
                    ->where('anonymous_id', $anonymousId)
                    ->latest('accepted_at')
                    ->first();
            }

            return null;
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
            ->where(function (Builder $query) use ($anonymousId, $sessionId): void {
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

    /**
     * Resolve the current active consent from the current request.
     */
    public function resolveFromCookie(): ?Consent
    {
        return $this->resolve(request());
    }

    /**
     * Determine whether the decoded consent cookie references an active consent.
     *
     * @param  array<string, mixed>  $decodedConsent
     */
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
